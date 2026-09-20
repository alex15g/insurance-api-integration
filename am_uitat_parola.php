<?php
session_start();
require 'conection.php';

// Includem PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM utilizatori WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $rez = $stmt->get_result();

    if ($rez->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $stmt_update = $conn->prepare("UPDATE utilizatori SET reset_token = ?, reset_expira = ? WHERE email = ?");
        $stmt_update->bind_param("sss", $token, $expira, $email);
        $stmt_update->execute();

        // Link-ul de resetare catre XAMPP-ul tau
        $link_resetare = "http://localhost/proiect_rca/seteaza_parola.php?token=" . $token;

        // Trimiterea efectiva a email-ului
        $mail = new PHPMailer(true);

        try {
            // Setari Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;

            $mail->Username   = 'alexandrugavris1610@gmail.com';  // <--- MODIFICĂ AICI: Adresa de Gmail neimportanta
            $mail->Password   = 'irbpinrjpjjtcrbr';               // <--- MODIFICĂ AICI: Parola de 16 litere de la Pasul 1

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Expeditor si Destinatar
            $mail->setFrom('emailul_tau_de_test@gmail.com', 'Platforma RCA'); // <--- MODIFICĂ AICI: Aceeasi adresa de mai sus
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Resetare Parolă - Platforma RCA';
            $mail->Body    = "Salut,<br><br>Am primit o cerere pentru resetarea parolei contului tău.<br><br>
                              Apasă pe link-ul de mai jos pentru a seta o parolă nouă:<br>
                              <a href='{$link_resetare}'>{$link_resetare}</a><br><br>
                              Acest link este valabil o oră.";

            $mail->send();
            $mesaj = "<span style='color: green;'>Am trimis un link de resetare pe adresa ta de email! Verifică și folderul Spam.</span>";
        } catch (Exception $e) {
            $mesaj = "<span style='color: red;'>Eroare tehnica la trimitere: {$mail->ErrorInfo}</span>";
        }
    } else {
        $mesaj = "<span style='color: green;'>Am trimis un link de resetare pe adresa ta de email! Verifică și folderul Spam.</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Recuperare Parolă</title>
    <style>
        body { background-color: #f3f4f6; font-family: sans-serif; display: flex; justify-content: center; padding-top: 100px; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; color: white; background: #f59e0b; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
<div class="card">
    <h2>Ai uitat parola?</h2>
    <p style="font-size: 0.9rem; color: #555;">Introdu email-ul contului și îți vom trimite un link securizat.</p>
    <div style="margin-bottom: 15px; font-weight: bold; font-size: 0.9rem;"><?php echo $mesaj; ?></div>
    <form method="POST">
        <input type="email" name="email" placeholder="Ex: adresa.mea@firma.ro" required>
        <button type="submit">Trimite Email de Resetare</button>
    </form>
    <a href="login.php" style="display: block; margin-top: 15px; font-size: 0.9rem; color: #3b82f6; text-decoration: none;">&larr; Înapoi la Login</a>
</div>
</body>
</html>