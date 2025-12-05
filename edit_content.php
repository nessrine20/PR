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
$content = null;

if ($id > 0) {
    // On vérifie aussi que le cours appartient au prof pour la sécurité (via une jointure ou en deux temps)
    // Ici on fait simple : on récupère le contenu et on vérifie si le cours lié est au prof
    $stmt = $conn->prepare("
        SELECT c.*, co.nom_cours 
        FROM contenus c 
        JOIN cours co ON c.cours_id = co.id 
        WHERE c.id = ? AND co.prof_id = ?
    ");
    $profId = $_SESSION['user_id'];
    $stmt->bind_param("ii", $id, $profId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $content = $result->fetch_assoc();
    }
}

if (!$content) {
    die("Contenu introuvable ou accès refusé.");
}

function e($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier contenu</title>
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
        .form-group input, .form-group select {
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
        .current-file {
            margin-top: 5px;
            font-size: 0.9rem;
            color: #666;
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
        <li class="menu-item"><a href="mes_cours.php" class="sidebar-btn active">📘 Mes cours</a></li>
        <li class="menu-item"><a href="login.html" class="sidebar-btn">🚪 Déconnexion</a></li>
    </ul>
</div>

<div class="navbar">
    <div class="logo">✏️ Modifier contenu</div>
    <div class="nav-actions">
        <span class="profile">👤 <?= e($_SESSION['nom'] ?? 'Professeur') ?></span>
    </div>
</div>

<main class="prof-main">
    <div class="form-panel">
        <h2>Modifier : <?= e($content['titre']) ?></h2>
        <p style="margin-bottom: 20px; color: #666;">Cours : <?= e($content['nom_cours']) ?></p>

        <form action="prof_actions.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_content">
            <input type="hidden" name="id" value="<?= $content['id'] ?>">
            <input type="hidden" name="cours_id" value="<?= $content['cours_id'] ?>">
            
            <div class="form-group">
                <label for="titre">Titre</label>
                <input type="text" id="titre" name="titre" value="<?= e($content['titre']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="type_contenu">Type</label>
                <select id="type_contenu" name="type_contenu" required>
                    <option value="Cours" <?= $content['type_contenu'] === 'Cours' ? 'selected' : '' ?>>Résumé / Cours</option>
                    <option value="TP" <?= $content['type_contenu'] === 'TP' ? 'selected' : '' ?>>Travaux Pratiques (TP)</option>
                    <option value="TD" <?= $content['type_contenu'] === 'TD' ? 'selected' : '' ?>>Travaux Dirigés (TD)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="fichier">Remplacer le fichier (optionnel)</label>
                <input type="file" id="fichier" name="fichier">
                <div class="current-file">Fichier actuel : <a href="<?= e($content['fichier']) ?>" target="_blank">Voir</a></div>
            </div>

            <button type="submit" class="btn-save">Enregistrer les modifications</button>
            <a href="prof_contenus.php?cours_id=<?= $content['cours_id'] ?>" class="btn-cancel">Annuler</a>
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
