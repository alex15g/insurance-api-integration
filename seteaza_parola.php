<?php
session_start();
require 'conection.php';

$mesaj = "";
$token_valid = false;
$email_user = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $data_curenta = date("Y-m-d H:i:s");

    // Verificam daca tokenul exista si daca NU a expirat
    $stmt = $conn->prepare("SELECT email FROM utilizatori WHERE reset_token = ? AND reset_expira >= ?");
    $stmt->bind_param("ss", $token, $data_curenta);
    $stmt->execute();
    $rez = $stmt->get_result();

    if ($rez->num_rows > 0) {
        $token_valid = true;
        $row = $rez->fetch_assoc();
        $email_user = $row['email'];
    } else {
        $mesaj = "<span style='color: red;'>Link-ul este invalid sau a expirat! Te rugăm să ceri altul.</span>";
    }
} else {
    die("Lipseste codul de securitate!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valid) {
    $parola_noua = $_POST['parola_noua'];
    $parola_confirma = $_POST['parola_confirma'];

    if ($parola_noua === $parola_confirma) {
        $hash_nou = password_hash($parola_noua, PASSWORD_DEFAULT);

        // Actualizam parola si STERGEM tokenul ca sa nu mai poata fi folosit
        $stmt_upd = $conn->prepare("UPDATE utilizatori SET parola = ?, reset_token = NULL, reset_expira = NULL WHERE email = ?");
        $stmt_upd->bind_param("ss", $hash_nou, $email_user);

        if ($stmt_upd->execute()) {
            $mesaj = "<span style='color: green;'>Parola a fost resetată cu succes! <a href='login.php'>Login</a></span>";
            $token_valid = false; // ascundem formularul
        }
    } else {
        $mesaj = "<span style='color: red;'>Parolele nu coincid!</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Setați Parola Nouă</title>
    <style>
        body { background-color: #f3f4f6; font-family: sans-serif; display: flex; justify-content: center; padding-top: 100px; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; color: white; background: #10b981; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
<div class="card">
    <h2>Parolă Nouă</h2>
    <div style="margin-bottom: 15px; font-weight: bold;"><?php echo $mesaj; ?></div>

    <?php if ($token_valid): ?>
        <form method="POST">
            <input type="password" name="parola_noua" placeholder="Introdu noua parolă" required>
            <input type="password" name="parola_confirma" placeholder="Confirmă noua parolă" required>
            <button type="submit">Salvează Parola</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>