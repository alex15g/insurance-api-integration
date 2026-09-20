<?php
// Fără spații înainte de php!
session_start();
require 'config_api.php';

if (!isset($_GET['policyId'])) {
    die("Lipsește ID-ul poliței.");
}

$policyId = $_GET['policyId'];
$baseUrl = rtrim(API_URL, '/');

// 1. LOGIN PENTRU A OBȚINE TOKEN-UL
$authPayload = json_encode([
    'account' => API_USER,
    'password' => API_PASS
]);

$chAuth = curl_init($baseUrl . '/auth');
curl_setopt($chAuth, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chAuth, CURLOPT_POST, true);
curl_setopt($chAuth, CURLOPT_POSTFIELDS, $authPayload);
curl_setopt($chAuth, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($chAuth, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($chAuth, CURLOPT_SSL_VERIFYHOST, false);

$authResponse = curl_exec($chAuth);
curl_close($chAuth);

$authData = json_decode($authResponse, true);
if (!isset($authData['data']['token'])) {
    die("Eroare la autentificarea pentru PDF.");
}
$token = $authData['data']['token'];

// 2. CEREM DOCUMENTELE
$chPdf = curl_init($baseUrl . '/policy/' . $policyId);
curl_setopt($chPdf, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chPdf, CURLOPT_HTTPGET, true);
curl_setopt($chPdf, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . trim($token),
    'Token: ' . trim($token),
    'Accept: application/json, application/pdf'
]);
curl_setopt($chPdf, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($chPdf, CURLOPT_SSL_VERIFYHOST, false);

$apiResponse = curl_exec($chPdf);
$httpcode = curl_getinfo($chPdf, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($chPdf, CURLINFO_CONTENT_TYPE);
curl_close($chPdf);

// 3. PROCESĂM RĂSPUNSUL (Dacă e JSON, extragem Base64. Dacă e binar, îl dăm direct)
if (strpos($contentType, 'application/json') !== false) {
    $data = json_decode($apiResponse, true);

    // Verificăm dacă avem succes și dacă există array-ul de fișiere
    if (isset($data['error']) && $data['error'] === false && !empty($data['data']['files'])) {

        $fisier = $data['data']['files'][0]; // Luăm primul document din array

        // Extragem conținutul (API-urile pot folosi 'content', 'base64' sau 'data' ca nume de cheie)
        $base64String = $fisier['content'] ?? $fisier['base64'] ?? $fisier['data'] ?? '';

        if (!empty($base64String)) {
            $pdfBinar = base64_decode($base64String); // Transformăm textul în PDF

            if (ob_get_length()) ob_clean();
            header("Content-type: application/pdf");
            header("Content-Disposition: inline; filename=polita_rca_" . $policyId . ".pdf");
            echo $pdfBinar;
            exit;
        } else {
            die("Am găsit fișierul, dar nu are conținut base64! Structura e: " . json_encode($fisier));
        }
    } else {
        die("Eroare în JSON-ul returnat: " . $apiResponse);
    }
} else {
    // API-ul ne-a dat direct fișierul binar
    if (ob_get_length()) ob_clean();
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=polita_rca_" . $policyId . ".pdf");
    echo $apiResponse;
    exit;
}
?>