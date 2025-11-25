<?php
session_start();

// 1. Connexion BD (à modifier selon ton serveur)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "revision";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// 2. Récupération des données du formulaire
$email = $_POST['email'];
$password = $_POST['password'];

// 3. Vérifier si l'utilisateur existe
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    // 4. Vérifier mot de passe
    if (password_verify($password, $user['password'])) {

        // Stocker les infos en session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nom'] = $user['nom'];

        // 5. Redirection selon rôle
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
            exit();
        }

        if ($user['role'] == 'prof') {
            header("Location: prof_dashboard.php");
            exit();
        }

        if ($user['role'] == 'etudiant') {
            header("Location: etudiant_dashboard.php");
            exit();
        }

    } else {
        echo "❌ Mot de passe incorrect !";
    }

} else {
    echo "❌ Aucun utilisateur trouvé avec cet email.";
}

$stmt->close();
$conn->close();
?>
