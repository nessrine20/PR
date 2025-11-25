<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}
?>
<h1>Bienvenue Admin <?= $_SESSION['nom']; ?></h1>
