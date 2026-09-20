<?php
session_start();
session_destroy(); // Distrugem sesiunea (rupem bratara de acces)
header("Location: login.php"); // Il trimitem inapoi la usa
exit();
?>