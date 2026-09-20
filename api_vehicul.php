<?php
session_start();
require 'config_api.php';

header('Content-Type: application/json');

if (!isset($_GET['nr_auto']) || empty($_GET['nr_auto'])) {
    echo json_encode(['error' => true, 'message' => 'Numărul de înmatriculare lipsește.']);
    exit;
}

$nr_auto = strtoupper(trim($_GET['nr_auto']));
$baseUrl = rtrim(API_URL, '/');

// 1. LOGIN PENTRU TOKEN
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
    echo json_encode(['error' => true, 'message' => 'Eroare la autentificarea în API.']);
    exit;
}
$token = $authData['data']['token'];

// 2. CEREM DATELE MAȘINII
$chVeh = curl_init($baseUrl . '/vehicle?licensePlate=' . urlencode($nr_auto));
curl_setopt($chVeh, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chVeh, CURLOPT_HTTPGET, true);
curl_setopt($chVeh, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . trim($token),
    'Token: ' . trim($token),
    'Accept: application/json'
]);
curl_setopt($chVeh, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($chVeh, CURLOPT_SSL_VERIFYHOST, false);

$apiResponse = curl_exec($chVeh);
curl_close($chVeh);

$vehData = json_decode($apiResponse, true);

// Verificăm dacă am găsit mașina
if (isset($vehData['error']) && $vehData['error'] === false && !empty($vehData['data'])) {
    echo json_encode(['error' => false, 'date_masina' => $vehData['data']]);
} else {
    echo json_encode(['error' => true, 'message' => 'Nu am găsit date pentru acest număr auto.']);
}
?>