<?php
session_start();
require 'conection.php';
require 'config_api.php'; // Aici avem parolele ascunse

header('Content-Type: application/json');

// 1. Daca userul nu e logat
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => true, 'message' => 'Acces neautorizat. Te rugam sa te loghezi.']);
    exit();
}

$inputJSON = file_get_contents('php://input');
$requestData = json_decode($inputJSON, true);

// Curatam URL-ul in caz ca are vreun slash in plus la final in .env
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
// --- MAGIA ANTI-XAMPP PENTRU HTTPS ---
curl_setopt($chAuth, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($chAuth, CURLOPT_SSL_VERIFYHOST, false);

$authResponse = curl_exec($chAuth);
$authError = curl_error($chAuth); // Prindem orice eroare interna XAMPP
curl_close($chAuth);

// Daca XAMPP blocheaza conexiunea
if ($authResponse === false) {
    echo json_encode(['error' => true, 'message' => 'Eroare Conexiune XAMPP (Auth): ' . $authError]);
    exit();
}

$authData = json_decode($authResponse, true);

// Daca LIH ne respinge user-ul sau parola
if (!isset($authData['data']['token'])) {
    echo json_encode([
        'error' => true,
        'message' => 'Eroare Login LIH: ' . $authResponse
    ]);
    exit();
}
$token = $authData['data']['token']; // Avem Token-ul!

// ============================================
// PASUL 2: CEREM OFERTELE REALE
// ============================================
$chOffer = curl_init($baseUrl . '/offer');
curl_setopt($chOffer, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chOffer, CURLOPT_POST, true);
curl_setopt($chOffer, CURLOPT_POSTFIELDS, $inputJSON);
curl_setopt($chOffer, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . trim($token),
    'Token: ' . trim($token), // <--- Adaugam si headerul dedicat cerut de unii asiguratori in docs
    'Content-Language: ro'
]);
// --- MAGIA ANTI-XAMPP PENTRU HTTPS ---
curl_setopt($chOffer, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($chOffer, CURLOPT_SSL_VERIFYHOST, false);

$offerResponse = curl_exec($chOffer);
$offerError = curl_error($chOffer);
curl_close($chOffer);

// Daca XAMPP blocheaza a doua conexiune
if ($offerResponse === false) {
    echo json_encode(['error' => true, 'message' => 'Eroare Conexiune XAMPP (Offer): ' . $offerError]);
    exit();
}

// ============================================
// PASUL 3: TRASABILITATE (Salvare in Baza de Date)
// ============================================
$id_utilizator = $_SESSION['user_id'];
$nr_inmatriculare = $requestData['product']['vehicle']['licensePlate'] ?? 'NECUNOSCUT';
$marca = $requestData['product']['vehicle']['brand'] ?? 'NECUNOSCUT';

$json_cerere_safe = $conn->real_escape_string($inputJSON);
$json_raspuns_safe = $conn->real_escape_string($offerResponse);

$sql = "INSERT INTO cereri_rca (id_utilizator, numar_inmatriculare, marca_auto, json_cerere, preturi_obtinute, status) 
        VALUES ('$id_utilizator', '$nr_inmatriculare', '$marca', '$json_cerere_safe', '$json_raspuns_safe', 'calculat')";
$conn->query($sql);

// Returnam raspunsul catre frontend
echo $offerResponse;
?>