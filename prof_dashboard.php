<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'prof') {
    header("Location: login.html");
    exit();
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "revision");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$profId = $_SESSION['user_id'];

// Compter le nombre total d'étudiants
$sqlEtudiants = "SELECT COUNT(*) as total FROM users WHERE role = 'etudiant'";
$resultEtudiants = $conn->query($sqlEtudiants);
$nbEtudiants = $resultEtudiants->fetch_assoc()['total'];

// Compter le nombre de cours assignés à ce professeur
$sqlCours = "SELECT COUNT(*) as total FROM cours WHERE prof_id = ?";
$stmtCours = $conn->prepare($sqlCours);
$stmtCours->bind_param("i", $profId);
$stmtCours->execute();
$nbCours = $stmtCours->get_result()->fetch_assoc()['total'];

// Compter le nombre de contenus publiés par ce professeur
$sqlContenus = "SELECT COUNT(*) as total FROM contenus WHERE cours_id IN (SELECT id FROM cours WHERE prof_id = ?)";
$stmtContenus = $conn->prepare($sqlContenus);
$stmtContenus->bind_param("i", $profId);
$stmtContenus->execute();
$nbContenus = $stmtContenus->get_result()->fetch_assoc()['total'];

$conn->close();

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Professeur</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="hamburger" id="hamburger">☰</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Espace Prof</h3>
    </div>
    <ul class="menu-list">
        <li class="menu-item"><a href="prof_dashboard.php" class="sidebar-btn active">🏠 Dashboard</a></li>
        <li class="menu-item"><a href="mes_cours.php" class="sidebar-btn">📘 Mes cours</a></li>
        <li class="menu-item"><a href="liste_etudiants.php" class="sidebar-btn">🎓 Liste étudiants</a></li>
        <li class="menu-item"><a href="attribuer_matieres.php" class="sidebar-btn">📚 Attribuer matières</a></li>
    </ul>
</div>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">🎓 BOOSTSR51</div>
    <div class="nav-actions">
        <button id="darkModeBtn" class="btn">🌙</button>
        <div class="profile-dropdown">
            <button class="profile-btn">
                👤 <?= e($_SESSION['nom'] ?? 'Professeur') ?>
                <span class="dropdown-arrow">▼</span>
            </button>
            <div class="profile-menu" id="profileMenu">
                <div class="profile-menu-header">
                    <div class="profile-avatar-small">👨‍🏫</div>
                    <div>
                        <div class="profile-menu-name"><?= e($_SESSION['nom'] ?? 'Professeur') ?></div>
                        <div class="profile-menu-role">Professeur</div>
                    </div>
                </div>
                <div class="profile-menu-divider"></div>
                <div class="profile-menu-item">
                    <span class="menu-icon">📧</span>
                    <span><?= e($_SESSION['email'] ?? 'Email non disponible') ?></span>
                </div>
                <div class="profile-menu-divider"></div>
                <a href="login.html" class="profile-menu-item logout-item">
                    <span class="menu-icon">🚪</span>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- MAIN -->
<main class="prof-main">
    <div class="dashboard-header">
        <h1>Bienvenue, <?= e($_SESSION['nom'] ?? 'Professeur') ?> ! 👋</h1>
        <p class="subtitle">The influence of a good teacher can never be erased</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <h3>Mes Cours</h3>
                <p class="stat-number"><?= $nbCours ?></p>
                <p class="stat-label">cours assignés</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🎓</div>
            <div class="stat-info">
                <h3>Étudiants</h3>
                <p class="stat-number"><?= $nbEtudiants ?></p>
                <p class="stat-label">étudiants inscrits</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-info">
                <h3>Contenus</h3>
                <p class="stat-number"><?= $nbContenus ?></p>
                <p class="stat-label">ressources publiées</p>
            </div>
        </div>
    </div>

    <div class="actions-section">
        <h2>Actions Rapides</h2>
        <div class="actions-grid">
            <a href="mes_cours.php" class="action-btn">
                <span class="action-icon">📘</span>
                <span class="action-text">Gérer mes cours</span>
            </a>
            <a href="liste_etudiants.php" class="action-btn">
                <span class="action-icon">👥</span>
                <span class="action-text">Liste des étudiants</span>
            </a>
            <a href="attribuer_matieres.php" class="action-btn">
                <span class="action-icon">📚</span>
                <span class="action-text">Attribuer matières</span>
            </a>
        </div>
    </div>
</main>

<style>
.prof-main {
    padding: 40px 60px;
    margin-top: 100px; /* Espace pour la navbar */
    margin-left: 250px;
    margin-right: 40px;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8f5ff 0%, #f0ebff 100%);
    max-width: 1400px;
}

.dashboard-header {
    margin-bottom: 40px;
    text-align: center;
}

.dashboard-header h1 {
    font-size: 32px;
    color: #5b21b6;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.subtitle {
    color: #6b7280;
    font-size: 16px;
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 15px rgba(91, 33, 182, 0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(91, 33, 182, 0.15);
    border-color: #a78bfa;
}

.stat-icon {
    font-size: 48px;
    line-height: 1;
}

.stat-info h3 {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 8px 0;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 36px;
    font-weight: 700;
    color: #5b21b6;
    margin: 0 0 5px 0;
}

.stat-label {
    font-size: 13px;
    color: #9ca3af;
    margin: 0;
}

.actions-section {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(91, 33, 182, 0.08);
}

.actions-section h2 {
    font-size: 20px;
    color: #1f2937;
    margin: 0 0 25px 0;
    font-weight: 700;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.action-btn {
    background: linear-gradient(135deg, #7c3aed, #a78bfa);
    color: white;
    padding: 25px 30px;
    border-radius: 12px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
}

.action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);
}

.action-icon {
    font-size: 32px;
}

.action-text {
    font-size: 16px;
    font-weight: 600;
}

/* Profile Dropdown */
.profile-dropdown {
    position: relative;
}

.profile-btn {
    background: linear-gradient(135deg, #7c3aed, #a78bfa);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.profile-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
}

.dropdown-arrow {
    font-size: 10px;
}

.profile-menu {
    position: absolute;
    top: 60px;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    min-width: 280px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
}

.profile-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.profile-menu-header {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.profile-avatar-small {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed, #a78bfa);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.profile-menu-name {
    font-weight: 700;
    color: #1f2937;
    font-size: 16px;
}

.profile-menu-role {
    color: #6b7280;
    font-size: 13px;
}

.profile-menu-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0 10px;
}

.profile-menu-item {
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #374151;
    text-decoration: none;
    transition: background 0.2s;
    cursor: pointer;
}

.profile-menu-item:hover {
    background: #f9fafb;
}

.logout-item {
    color: #dc2626;
    border-radius: 0 0 12px 12px;
}

.logout-item:hover {
    background: #fee2e2;
}

.menu-icon {
    font-size: 18px;
}
</style>

<script src="assets/js/dashboard.js?v=4" defer></script>



</body>
</html>
