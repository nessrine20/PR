<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'prof') {
    header("Location: login.html");
    exit();
}

$profId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$coursId = isset($_GET['cours_id']) ? (int) $_GET['cours_id'] : 0;

// Connexion DB
$conn = new mysqli("localhost", "root", "", "revision");
if ($conn->connect_error) {
    die("Erreur connexion DB : " . $conn->connect_error);
}

// Vérifier que le cours appartient au prof
$coursName = "Cours inconnu";
if ($coursId > 0) {
    $stmt = $conn->prepare("SELECT nom_cours FROM cours WHERE id = ? AND prof_id = ?");
    $stmt->bind_param("ii", $coursId, $profId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $coursName = $res->fetch_assoc()['nom_cours'];
    } else {
        // Rediriger si le cours n'est pas trouvé ou n'appartient pas au prof
        header("Location: mes_cours.php");
        exit();
    }
    $stmt->close();
} else {
    // Si pas de cours_id, rediriger vers mes_cours
    header("Location: mes_cours.php");
    exit();
}

// Récupérer les contenus du cours
$contenus = [];
$stmt = $conn->prepare("SELECT * FROM contenus WHERE cours_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $coursId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $contenus[] = $row;
}
$stmt->close();

$status = $_GET['status'] ?? null;
$message = $_GET['message'] ?? '';

function e($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Contenus - <?= e($coursName) ?></title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .prof-main {
            padding: 40px;
            margin-left: 250px;
            max-width: 1200px;
            margin-right: auto;
        }
        .management-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin: 0 auto 30px;
            max-width: 900px;
        }
        .form-panel {
            background: linear-gradient(135deg, #ede3ff, #e4d6ff);
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            border: 1px solid #d4c5f9;
            box-shadow: 0 4px 15px rgba(106, 52, 204, 0.08);
        }
        .form-panel h3 {
            color: #5b21b6;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            gap: 15px;
        }
        .form-grid label {
            font-weight: 600;
            color: #4a2db6;
            display: block;
            margin-bottom: 5px;
        }
        .form-grid input, .form-grid select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e4d6ff;
            border-radius: 8px;
            font-size: 15px;
            transition: border 0.3s;
        }
        .form-grid input:focus, .form-grid select:focus {
            outline: none;
            border-color: #6a34cc;
        }
        .primary-btn {
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
            width: 100%;
            text-transform: none;
        }
        .primary-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
            background: linear-gradient(135deg, #6d28d9, #8b5cf6);
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .content-table th, .content-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .content-table th {
            background: linear-gradient(135deg, #f8f4ff, #ede3ff);
            font-weight: 600;
            color: #4a2db6;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        .content-table tr:hover {
            background-color: #faf8ff;
        }
        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .badge-tp { 
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
        }
        .badge-td { 
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #166534;
        }
        .badge-resume { 
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
        }
        
        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            margin-right: 8px;
            display: inline-block;
            font-weight: 500;
            transition: transform 0.2s;
        }
        .btn-action:hover {
            transform: translateY(-1px);
        }
        .btn-edit { 
            background: linear-gradient(135deg, #fef3c7, #fde047);
            color: #713f12;
        }
        .btn-delete { 
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border: none;
            cursor: pointer;
        }
        
        .status-banner {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .status-banner.success { 
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .status-banner.error { 
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #7f1d1d;
            border-left: 4px solid #ef4444;
        }
        .dashboard-title {
            color: #5b21b6;
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 700;
            text-align: center;
        }
        .back-link {
            color: #7c3aed;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 25px;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #6d28d9;
        }
    </style>
</head>
<body>

<div class="hamburger" id="hamburger">☰</div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Espace Prof</h3>
    </div>
    <ul class="menu-list">
        <li class="menu-item"><a href="prof_dashboard.php" class="sidebar-btn">🏠 Dashboard</a></li>
        <li class="menu-item"><a href="mes_cours.php" class="sidebar-btn active">📘 Mes cours</a></li>
        <li class="menu-item"><a href="liste_etudiants.php" class="sidebar-btn">🎓 Liste étudiants</a></li>
        <li class="menu-item"><a href="login.html" class="sidebar-btn">🚪 Déconnexion</a></li>
    </ul>
</div>

<div class="navbar">
    <div class="logo">📂 Gestion : <?= e($coursName) ?></div>
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
    <div class="back-link">
        <a href="mes_cours.php" class="back-link">← Retour à mes cours</a>
    </div>

    <h1 class="dashboard-title">Contenus pour <?= e($coursName) ?></h1>
    
    <?php if ($message): ?>
        <div class="status-banner <?= $status === 'success' ? 'success' : 'error' ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <section class="management-section">
        <div class="form-panel">
            <h3>Ajouter un nouveau contenu</h3>
            <form action="prof_actions.php" method="POST" enctype="multipart/form-data" class="form-grid">
                <input type="hidden" name="action" value="add_content">
                <input type="hidden" name="cours_id" value="<?= $coursId ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label for="titre">Titre du contenu</label>
                        <input type="text" id="titre" name="titre" required placeholder="Ex: TP1 - Introduction">
                    </div>
                    <div>
                        <label for="type_contenu">Type</label>
                        <select id="type_contenu" name="type_contenu" required>
                            <option value="Cours">Résumé / Cours</option>
                            <option value="TP">Travaux Pratiques (TP)</option>
                            <option value="TD">Travaux Dirigés (TD)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="fichier">Fichier (PDF, Word, ZIP)</label>
                    <input type="file" id="fichier" name="fichier" required>
                </div>

                <button type="submit" class="primary-btn">➕ Ajouter le contenu</button>
            </form>
        </div>

        <h3>Liste des contenus existants</h3>
        <?php if (empty($contenus)): ?>
            <p style="color: #777; font-style: italic;">Aucun contenu n'a été ajouté pour ce cours.</p>
        <?php else: ?>
            <table class="content-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Titre</th>
                        <th>Fichier</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contenus as $item): ?>
                        <tr>
                            <td>
                                <?php 
                                    $badgeClass = 'badge-resume';
                                    if ($item['type_contenu'] === 'TP') $badgeClass = 'badge-tp';
                                    if ($item['type_contenu'] === 'TD') $badgeClass = 'badge-td';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= e($item['type_contenu']) ?></span>
                            </td>
                            <td><?= e($item['titre']) ?></td>
                            <td>
                                <a href="<?= e($item['fichier']) ?>" target="_blank" style="color: #6a34cc; text-decoration: underline;">Télécharger</a>
                            </td>
                            <td>
                                <a href="edit_content.php?id=<?= $item['id'] ?>" class="btn-action btn-edit" style="border:none; cursor:pointer;">Modifier</a>
                                <form action="prof_actions.php" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce contenu ?');">
                                    <input type="hidden" name="action" value="delete_content">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="cours_id" value="<?= $coursId ?>">
                                    <button type="submit" class="btn-action btn-delete" style="border:none; cursor:pointer;">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

</main>

<script src="dashboard.js?v=4" defer></script>

</body>
</html>


