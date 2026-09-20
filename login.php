<?php
session_start();
require 'conection.php'; // Conexiunea la baza de date

$mesaj = "";

// Verificam daca userul a apasat un buton in formular
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $parola = trim($_POST['parola']);
    $actiune = $_POST['actiune']; // login sau register

    if ($actiune == 'register') {
        // 1. Verificam INTAI daca emailul exista deja (Folosind Prepared Statements pt securitate maxima)
        $stmt_check = $conn->prepare("SELECT id FROM utilizatori WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $verificare = $stmt_check->get_result();

        if ($verificare->num_rows > 0) {
            $mesaj = "<span style='color: red;'>Eroare: Acest email este deja folosit!</span>";
        } else {
            // Daca e liber, criptam si salvam
            $parola_criptata = password_hash($parola, PASSWORD_DEFAULT);

            // Insert securizat
            $stmt_insert = $conn->prepare("INSERT INTO utilizatori (email, parola) VALUES (?, ?)");
            $stmt_insert->bind_param("ss", $email, $parola_criptata);

            if ($stmt_insert->execute()) {
                $mesaj = "<span style='color: green;'>Cont creat cu succes! Acum te poți loga.</span>";
            } else {
                $mesaj = "<span style='color: red;'>Eroare la crearea contului. Încearcă din nou!</span>";
            }
        }
    } elseif ($actiune == 'login') {
        // Cautare securizata (Fara SQL Injection)
        $stmt_login = $conn->prepare("SELECT * FROM utilizatori WHERE email = ?");
        $stmt_login->bind_param("s", $email);
        $stmt_login->execute();
        $rezultat = $stmt_login->get_result();

        if ($rezultat->num_rows > 0) {
            $user = $rezultat->fetch_assoc();

            // Verificam daca parola introdusa se potriveste cu hash-ul
            if (password_verify($parola, $user['parola'])) {
                // Succes! Ii deschidem sesiunea
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];

                // Il redirectionam catre aplicatia RCA
                header("Location: index.php");
                exit();
            } else {
                $mesaj = "<span style='color: red;'>Parolă incorectă!</span>";
            }
        } else {
            $mesaj = "<span style='color: red;'>Nu există niciun cont cu acest email!</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login - RCA Insurtech</title>
    <style>
        body { background-color: #f3f4f6; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; padding-top: 100px; }
        .login-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; color: white; border: none; border-radius: 6px; cursor: pointer; margin-top: 10px; font-weight: bold; }
        .btn-login { background: #3b82f6; }
        .btn-register { background: #10b981; }
        .mesaj { margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Access Platformă</h2>
    <div class="mesaj"><?php echo $mesaj; ?></div>

    <form method="POST" action="login.php">
        <input type="email" name="email" placeholder="Email Broker" required>
        <input type="password" name="parola" placeholder="Parolă" required>

        <div style="text-align: right; margin-bottom: 15px;">
            <a href="am_uitat_parola.php" style="font-size: 0.9rem; color: #3b82f6; text-decoration: none;">Ai uitat parola?</a>
        </div>

        <button type="submit" name="actiune" value="login" class="btn-login">Intră în cont (Login)</button>
        <button type="submit" name="actiune" value="register" class="btn-register">Creează cont nou</button>
    </form>
</div>

</body>
</html>