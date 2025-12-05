<?php
session_start();

// Si on accède directement à login.php sans formulaire, rediriger vers login.html
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit();
}

// 1. Connexion BD
$host = "localhost";
$user = "root";
$pass = "";
$db   = "revision";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// 2. Récupération des données du formulaire
$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$role = isset($_POST['expected_role']) ? $_POST['expected_role'] : '';

// Vérifier que tous les champs sont remplis
if (empty($email) || empty($password) || empty($role)) {
    echo "❌ Veuillez remplir tous les champs.";
    exit();
}

// 3. Vérifier si l'utilisateur existe déjà
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // === UTILISATEUR EXISTE → CONNEXION ===
    $user = $result->fetch_assoc();

    // Vérifier mot de passe
    if ($password === $user['password']) {

        // Vérifier si le rôle correspond
        if ($user['role'] !== $role) {
            echo "❌ Vous n'avez pas accès à cet espace. Votre rôle est : " . $user['role'];
            exit();
        }

        // Stocker les infos en session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['email'] = $user['email'];

        // Redirection selon rôle
        redirectByRole($user['role']);

    } else {
        echo "❌ Mot de passe incorrect !";
        exit();
    }

} else {
    // === UTILISATEUR N'EXISTE PAS → INSCRIPTION ===
    // Extraire le nom depuis l'email (partie avant @)
    $nom = explode('@', $email)[0];
    
    $sql_insert = "INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssss", $nom, $email, $password, $role);

    if ($stmt_insert->execute()) {
        // Inscription réussie → Stocker en session
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['role'] = $role;
        $_SESSION['nom'] = $nom;
        $_SESSION['email'] = $email;

        // Redirection selon rôle
        redirectByRole($role);

    } else {
        echo "❌ Erreur lors de l'inscription : " . $conn->error;
        exit();
    }

    $stmt_insert->close();
}

// Fonction de redirection selon le rôle
function redirectByRole($role) {
    switch ($role) {
        case 'admin':
            header("Location: index.php");
            exit();
        case 'prof':
            header("Location: prof_dashboard.php");
            exit();
        case 'etudiant':
            header("Location: dashboard.php");
            exit();
        default:
            echo "❌ Rôle inconnu.";
            exit();
    }
}
$now = date("Y-m-d H:i:s");
$conn->query("UPDATE users SET last_login='$now' WHERE id='$user_id'");


$stmt->close();
$conn->close();
