<?php
include "config.php";

$id = $_GET["id"] ?? 0;
$role = $_GET["role"] ?? "students";

// récupérer l’utilisateur
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Utilisateur introuvable !");
}

// Lors de la modification
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = $_POST["nom"];
    $email = $_POST["email"];

    $sql = "UPDATE users SET nom=?, email=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nom, $email, $id);
    $stmt->execute();

    // redirection vers la bonne liste
    header("Location: index.php?view=" . ($role === "students" ? "students" : "teachers"));
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier utilisateur</title>
</head>
<body>

<h2>Modifier l’utilisateur</h2>

<form method="POST">

    <label>Nom :</label><br>
    <input type="text" name="nom" value="<?= $user['nom'] ?>" required><br><br>

    <label>Email :</label><br>
    <input type="email" name="email" value="<?= $user['email'] ?>" required><br><br>

    <button type="submit">Mettre à jour</button>

</form>

</body>
</html>

