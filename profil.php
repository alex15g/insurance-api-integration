<?php
session_start();
require 'conection.php'; // Conexiunea ta la baza de date

// Daca nu e logat, il dam afara
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $parola_veche = trim($_POST['parola_veche']);
    $parola_noua = trim($_POST['parola_noua']);
    $confirma_parola = trim($_POST['confirma_parola']);
    $user_id = $_SESSION['user_id'];

    // 1. Verificăm dacă parolele noi coincid
    if ($parola_noua !== $confirma_parola) {
        $mesaj = "<span style='color: red;'>Parolele noi nu coincid! Încearcă din nou.</span>";
    } else {
        // 2. Extragem hash-ul parolei actuale din baza de date
        $stmt = $conn->prepare("SELECT parola FROM utilizatori WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $rezultat = $stmt->get_result();
        $user = $rezultat->fetch_assoc();

        // 3. Verificăm dacă parola veche introdusă e corectă
        if (password_verify($parola_veche, $user['parola'])) {

            // 4. Parola veche e corectă -> Generăm hash-ul pentru parola NOUĂ
            $hash_nou = password_hash($parola_noua, PASSWORD_DEFAULT);

            // 5. Facem UPDATE în baza de date
            $stmt_update = $conn->prepare("UPDATE utilizatori SET parola = ? WHERE id = ?");
            $stmt_update->bind_param("si", $hash_nou, $user_id);

            if ($stmt_update->execute()) {
                $mesaj = "<span style='color: green;'>Parola a fost actualizată cu succes!</span>";
            } else {
                $mesaj = "<span style='color: red;'>A apărut o eroare la salvarea parolei.</span>";
            }
        } else {
            $mesaj = "<span style='color: red;'>Parola veche este incorectă!</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Profil - Schimbare Parolă</title>
    <style>
        body { background-color: #f3f4f6; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; padding-top: 100px; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; color: white; background: #3b82f6; border: none; border-radius: 6px; cursor: pointer; margin-top: 10px; font-weight: bold; }
        .mesaj { margin-bottom: 15px; font-weight: bold; }
        .back-link { display: block; margin-top: 20px; color: #6b7280; text-decoration: none; }
    </style>
</head>
<body>

<div class="card">
    <h2>Schimbare Parolă</h2>
    <p>Cont logat: <strong><?php echo $_SESSION['email']; ?></strong></p>

    <div class="mesaj"><?php echo $mesaj; ?></div>

    <form method="POST" action="profil.php">
        <input type="password" name="parola_veche" placeholder="Parola actuală" required>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
        <input type="password" name="parola_noua" placeholder="Noua parolă" required>
        <input type="password" name="confirma_parola" placeholder="Confirmă noua parolă" required>

        <button type="submit">Actualizează Parola</button>
    </form>

    <a href="index.php" class="back-link">&larr; Înapoi la Calculator RCA</a>
</div>

</body>
</html>