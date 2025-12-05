<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'prof') {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "revision");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'etudiant'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    }
}

if (!$student) {
    die("Étudiant introuvable.");
}

function e($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier étudiant</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .prof-main {
            padding: 40px;
            margin-left: 250px;
        }
        .form-panel {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .btn-save {
            background-color: #6a34cc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-cancel {
            background-color: #ccc;
            color: #333;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<div class="hamburger" onclick="toggleSidebar()">☰</div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Espace Prof</h3>
    </div>
    <ul class="menu-list">
        <li class="menu-item"><a href="prof_dashboard.php" class="sidebar-btn">🏠 Dashboard</a></li>
        <li class="menu-item"><a href="liste_etudiants.php" class="sidebar-btn active">🎓 Liste étudiants</a></li>
        <li class="menu-item"><a href="mes_cours.php" class="sidebar-btn">📘 Mes cours</a></li>
        <li class="menu-item"><a href="login.html" class="sidebar-btn">🚪 Déconnexion</a></li>
    </ul>
</div>

<div class="navbar">
    <div class="logo">✏️ Modifier étudiant</div>
    <div class="nav-actions">
        <span class="profile">👤 <?= e($_SESSION['nom'] ?? 'Professeur') ?></span>
    </div>
</div>

<main class="prof-main">
    <div class="form-panel">
        <h2>Modifier les informations</h2>
        <form action="prof_actions.php" method="POST">
            <input type="hidden" name="action" value="update_student">
            <input type="hidden" name="id" value="<?= $student['id'] ?>">
            
            <div class="form-group">
                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom" value="<?= e($student['nom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($student['email']) ?>" required>
            </div>

            <button type="submit" class="btn-save">Enregistrer</button>
            <a href="liste_etudiants.php" class="btn-cancel">Annuler</a>
        </form>
    </div>
</main>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("active");
}
</script>

</body>
</html>
