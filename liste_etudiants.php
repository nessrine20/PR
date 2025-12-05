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

$etudiants = [];
$sql = "SELECT id, nom, email FROM users WHERE role = 'etudiant' ";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $etudiants[] = $row;
    }
}

$conn->close();

function e($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des étudiants</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .prof-main {
            padding: 40px 60px;
            margin-top: 100px; /* Espace pour la navbar */
            margin-left: 250px;
            margin-right: 40px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f5ff 0%, #f0ebff 100%);
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 28px;
            color: #5b21b6;
            margin: 0 0 8px 0;
            font-weight: 700;
        }
        
        .page-subtitle {
            color: #6b7280;
            font-size: 15px;
            margin: 0;
        }
        
        .table-container {
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(91, 33, 182, 0.08);
        }
        
        .modern-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        
        .modern-table thead {
            background: linear-gradient(135deg, #f8f4ff, #ede3ff);
        }
        
        .modern-table th {
            padding: 18px 20px;
            text-align: left;
            border-bottom: 2px solid #e4d6ff;
            font-weight: 700;
            color: #5b21b6;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        .modern-table td {
            padding: 18px 20px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            font-size: 15px;
        }
        
        .modern-table tr:hover {
            background-color: #faf8ff;
        }
        
        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .btn-action {
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            margin-right: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-block;
        }
        
        .btn-edit { 
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }
        
        .btn-delete { 
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
    </style>
</head>
<body>

<div class="hamburger" id="hamburger">☰</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Espace Prof</h3>
    </div>
    <ul class="menu-list">
        <li class="menu-item"><a href="prof_dashboard.php" class="sidebar-btn">🏠 Dashboard</a></li>
        <li class="menu-item"><a href="mes_cours.php" class="sidebar-btn">📘 Mes cours</a></li>
        <li class="menu-item"><a href="liste_etudiants.php" class="sidebar-btn active">🎓 Liste étudiants</a></li>
    </ul>
</div>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">🎓 Liste des étudiants</div>
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

<main class="prof-main">
    <div class="page-header">
        <h1 class="page-title">📚 Étudiants inscrits</h1>
        <p class="page-subtitle">Liste complète des étudiants enregistrés sur la plateforme</p>
    </div>

    <div class="table-container">
        <?php if (empty($etudiants)): ?>
            <p style="color: #9ca3af; text-align: center; padding: 40px;">Aucun étudiant trouvé.</p>
        <?php else: ?>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etudiants as $et) : ?>
                        <tr>
                            <td><?= e($et["nom"]) ?></td>
                            <td><?= e($et["email"]) ?></td>
                            <td>
                                <a href="edit_student.php?id=<?= $et['id'] ?>" class="btn-action btn-edit">Modifier</a>
                                <form action="prof_actions.php" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet étudiant ?');">
                                    <input type="hidden" name="action" value="delete_student">
                                    <input type="hidden" name="id" value="<?= $et['id'] ?>">
                                    <button type="submit" class="btn-action btn-delete">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<script src="assets/js/dashboard.js?v=4" defer></script>

</body>
</html>
