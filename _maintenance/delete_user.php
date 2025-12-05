<?php
include "config.php";

// Sécurisation
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$role = $_GET['role'] ?? 'students';

if ($id <= 0) {
    die("❌ ID invalide");
}

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Redirection vers index.php
header("Location: index.php?view={$role}");
exit();
