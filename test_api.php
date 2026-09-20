<?php
require 'config_api.php';

echo "<h2>Testare Conexiune API Life Is Hard</h2>";
echo "Datele citite din .env:<br>";
echo "User: " . API_USER . "<br>";
echo "Pass: " . API_PASS . "<br>";
echo "URL: " . API_URL . "<br><hr>";

$authPayload = json_encode([
    'account' => API_USER,
    'password' => API_PASS
]);

$ch = curl_init(API_URL . '/auth');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $authPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo "<h3 style='color:red;'>Eroare XAMPP/Rețea: $error</h3>";
} else {
    echo "<h3>Răspunsul oficial de la serverul Life Is Hard:</h3>";
    echo "<pre style='background: #f4f4f4; padding: 15px; border: 1px solid #ddd;'>" . print_r(json_decode($response, true), true) . "</pre>";
    echo "<b>Text brut (Raw):</b> " . htmlspecialchars($response);
}
?>