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

$userId = $_SESSION['user_id'];
$user = null;

// Récupérer les infos du prof
$stmt = $conn->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}
$stmt->close();

function e($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .prof-main {
            padding: 40px 60px;
            margin-left: 250px;
            margin-right: 40px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f5ff 0%, #f0ebff 100%);
        }
        
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 50px;
            color: white;
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.3);
        }
        
        .profile-name {
            font-size: 32px;
            color: #5b21b6;
            margin: 0 0 8px 0;
            font-weight: 700;
        }
        
        .profile-role {
            color: #6b7280;
            font-size: 16px;
            margin: 0;
        }
        
        .profile-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(91, 33, 182, 0.08);
        }
        
        .profile-section {
            margin-bottom: 30px;
        }
        
        .profile-section:last-child {
            margin-bottom: 0;
        }
        
        .section-title {
            font-size: 18px;
            color: #5b21b6;
            font-weight: 700;
            margin: 0 0 20px 0;
            border-bottom: 2px solid #e4d6ff;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #6b7280;
            width: 150px;
            font-size: 14px;
        }
        
        .info-value {
            color: #374151;
            font-size: 15px;
            flex: 1;
        }
        
        .btn-edit-profile {
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
            border: none;
            cursor: pointer;
            font-size: 15px;
        }
        
        .btn-edit-profile:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);
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
        <li class="menu-item"><a href="mes_cours.php" class="sidebar-btn">📘 Mes cours</a></li>
        <li class="menu-item"><a href="liste_etudiants.php" class="sidebar-btn">🎓 Liste étudiants</a></li>
        <li class="menu-item"><a href="prof_profile.php" class="sidebar-btn active">👤 Mon Profil</a></li>
        <li class="menu-item"><a href="login.html" class="sidebar-btn">🚪 Déconnexion</a></li>
    </ul>
</div>

<div class="navbar">
    <div class="logo">👤 Mon Profil</div>
    <div class="nav-actions">
        <span class="profile">👤 <?= e($_SESSION['nom'] ?? 'Professeur') ?></span>
    </div>
</div>

<main class="prof-main">
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">👨‍🏫</div>
            <h1 class="profile-name"><?= e($user['nom'] ?? 'Professeur') ?></h1>
            <p class="profile-role">Professeur</p>
        </div>

        <div class="profile-card">
            <div class="profile-section">
                <h2 class="section-title">Informations personnelles</h2>
                <div class="info-row">
                    <span class="info-label">Nom complet</span>
                    <span class="info-value"><?= e($user['nom'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?= e($user['email'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Rôle</span>
                    <span class="info-value">Professeur</span>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("active");
}
</script>

</body>
</html>
