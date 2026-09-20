<?php
session_start();
require 'config_api.php'; // Tragem datele de logare la API-ul Life Is Hard

// Includem PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_GET['policyId']) || !isset($_GET['email_client'])) {
    die("Eroare: Lipsește ID-ul poliței sau adresa de email a clientului.");
}

$policyId = $_GET['policyId'];
$email_client = trim($_GET['email_client']);
$baseUrl = rtrim(API_URL, '/');

// ==========================================
// 1. LOGIN LA API PENTRU A OBȚINE TOKEN-UL
// ==========================================
$authPayload = json_encode(['account' => API_USER, 'password' => API_PASS]);
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
    die("Eroare la autentificarea în API-ul asigurătorului.");
}
$token = $authData['data']['token'];

// ==========================================
// 2. CEREM PDF-UL POLIȚEI DE LA ASIGURĂTOR
// ==========================================
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
$contentType = curl_getinfo($chPdf, CURLINFO_CONTENT_TYPE);
curl_close($chPdf);

// ==========================================
// 3. EXTRAGEM BINARUL (Decodăm Base64)
// ==========================================
$pdfBinar = '';
if (strpos($contentType, 'application/json') !== false) {
    $data = json_decode($apiResponse, true);
    if (isset($data['error']) && $data['error'] === false && !empty($data['data']['files'])) {
        $fisier = $data['data']['files'][0];

        $base64String = '';
        if (isset($fisier['content'])) $base64String = $fisier['content'];
        elseif (isset($fisier['base64'])) $base64String = $fisier['base64'];
        elseif (isset($fisier['data'])) $base64String = $fisier['data'];

        if (!empty($base64String)) {
            $pdfBinar = base64_decode($base64String);
        }
    }
} else {
    $pdfBinar = $apiResponse;
}

if (empty($pdfBinar)) {
    die("Eroare: Nu am putut genera fișierul PDF de la asigurător.");
}

// ==========================================
// 4. TRIMITEM EMAIL-UL CĂTRE CLIENT
// ==========================================
$mail = new PHPMailer(true);

try {
    // Setari SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'alexandrugavris1610@gmail.com'; // <--- MODIFICĂ AICI
    $mail->Password   = 'irbpinrjpjjtcrbr';              // <--- MODIFICĂ AICI cu parola de 16 caractere
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('emailul_tau_de_test@gmail.com', 'Platforma RCA'); // <--- MODIFICĂ AICI
    $mail->addAddress($email_client);

    // ATAȘĂM PDF-UL DIRECT DIN MEMORIE (Magia PHPMailer)
    $mail->addStringAttachment($pdfBinar, 'Polita_RCA_' . $policyId . '.pdf', 'base64', 'application/pdf');

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Polița ta RCA este pregătită!';

    // Mesajul din corpul email-ului
    $mail->Body = "
        <h3>Salut,</h3>
        <p>Îți mulțumim că ai ales serviciile noastre.</p>
        <p>Găsești atașată acestui email polița ta RCA. O poți printa sau o poți păstra direct pe telefon (este perfect legală în format digital).</p>
        <p>Drumuri bune și sigure!</p>
        <br><br>
        <p><i>Echipa Broker RCA</i></p>
    ";

    $mail->send();
    echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>";
    echo "<h2 style='color: green;'>✅ Polița PDF a fost trimisă cu succes pe adresa: <b>{$email_client}</b>!</h2>";
    echo "<br><a href='index.php' style='padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Înapoi la platformă</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<h2 style='color: red; text-align: center;'>Eroare la trimiterea email-ului: {$mail->ErrorInfo}</h2>";
}
?>