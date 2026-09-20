<?php
session_start();
require 'conection.php';
require 'config_api.php'; // Parolele ascunse

header('Content-Type: application/json');

// 1. Daca userul nu e logat
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => true, 'message' => 'Acces neautorizat. Te rugam sa te loghezi.']);
    exit();
}

$inputJSON = file_get_contents('php://input');

$baseUrl = rtrim(API_URL, '/');

// ============================================
// PASUL 1: AUTENTIFICAREA PENTRU TOKEN
// ============================================
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
$authError = curl_error($chAuth);
curl_close($chAuth);

if ($authResponse === false) {
    echo json_encode(['error' => true, 'message' => 'Eroare Conexiune XAMPP (Auth): ' . $authError]);
    exit();
}

$authData = json_decode($authResponse, true);
if (!isset($authData['data']['token'])) {
    echo json_encode(['error' => true, 'message' => 'Eroare Login LIH: ' . $authResponse]);
    exit();
}
$token = $authData['data']['token'];

// ============================================
// PASUL 2: CEREM EMITEREA POLIȚEI
// ============================================
// Aici folosim endpoint-ul /policy in loc de /offer
$chPolicy = curl_init($baseUrl . '/policy');
curl_setopt($chPolicy, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chPolicy, CURLOPT_POST, true);
curl_setopt($chPolicy, CURLOPT_POSTFIELDS, $inputJSON);
curl_setopt($chPolicy, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . trim($token),
    'Token: ' . trim($token),
    'Content-Language: ro'
]);
curl_setopt($chPolicy, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($chPolicy, CURLOPT_SSL_VERIFYHOST, false);

$policyResponse = curl_exec($chPolicy);
$policyError = curl_error($chPolicy);
curl_close($chPolicy);

if ($policyResponse === false) {
    echo json_encode(['error' => true, 'message' => 'Eroare Conexiune XAMPP (Policy): ' . $policyError]);
    exit();
}

echo $policyResponse;
?>