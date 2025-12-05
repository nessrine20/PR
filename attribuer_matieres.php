<?php
session_start();

// Pour l'instant, on permet l'accès aux profs (plus tard on pourrait ajouter un rôle admin)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'prof') {
    header("Location: login.html");
    exit();
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "revision");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$message = "";
$messageType = "";

// Traitement de l'attribution de matière
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'attribuer_matiere') {
        $profId = intval($_POST['prof_id']);
        $nomCours = trim($_POST['nom_cours']);
        $descriptionCours = trim($_POST['description_cours']);
        
        if (!empty($nomCours) && $profId > 0) {
            $sql = "INSERT INTO cours (nom_cours, description_cours, prof_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nomCours, $descriptionCours, $profId);
            
            if ($stmt->execute()) {
                $message = "✅ Matière attribuée avec succès!";
                $messageType = "success";
            } else {
                $message = "❌ Erreur lors de l'attribution: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        } else {
            $message = "❌ Veuillez remplir tous les champs requis.";
            $messageType = "error";
        }
    }
}

// Récupérer la liste des professeurs
$sqlProfs = "SELECT id, nom, email FROM users WHERE role = 'prof' ORDER BY nom ASC";
$resultProfs = $conn->query($sqlProfs);

// Récupérer toutes les attributions de cours
$sqlAttributions = "SELECT c.id, c.nom_cours, c.description_cours, u.nom as prof_nom, u.email as prof_email, c.prof_id
                    FROM cours c
                    LEFT JOIN users u ON c.prof_id = u.id
                    ORDER BY u.nom ASC, c.nom_cours ASC";
$resultAttributions = $conn->query($sqlAttributions);

function e($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attribution des Matières</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .admin-main {
            padding: 40px 60px;
            margin-top: 100px;
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
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(91, 33, 182, 0.08);
        }
        
        .form-card h2 {
            font-size: 20px;
            color: #5b21b6;
            margin: 0 0 20px 0;
            font-weight: 700;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-group select,
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.2s;
        }
        
        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }
        
        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #ef4444;
        }
        
        .attributions-table {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(91, 33, 182, 0.08);
        }
        
        .attributions-table h2 {
            font-size: 20px;
            color: #5b21b6;
            margin: 0 0 20px 0;
            font-weight: 700;
        }
        
        .modern-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .modern-table thead {
            background: linear-gradient(135deg, #f8f4ff, #ede3ff);
        }
        
        .modern-table th {
            padding: 15px 18px;
            text-align: left;
            border-bottom: 2px solid #e4d6ff;
            font-weight: 700;
            color: #5b21b6;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .modern-table td {
            padding: 15px 18px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            font-size: 14px;
        }
        
        .modern-table tr:hover {
            background-color: #faf8ff;
        }
        
        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            font-style: italic;
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
        <li class="menu-item"><a href="liste_etudiants.php" class="sidebar-btn">🎓 Liste étudiants</a></li>
        <li class="menu-item"><a href="attribuer_matieres.php" class="sidebar-btn active">📚 Attribuer matières</a></li>
    </ul>
</div>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">📚 Attribution des Matières</div>
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

<main class="admin-main">
    <div class="page-header">
        <h1 class="page-title">📚 Attribution des Matières</h1>
        <p class="page-subtitle">Attribuez des matières aux professeurs de la plateforme</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="message <?= $messageType ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <div class="content-grid">
        <!-- Formulaire d'attribution -->
        <div class="form-card">
            <h2>➕ Nouvelle Attribution</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="attribuer_matiere">
                
                <div class="form-group">
                    <label for="prof_id">Professeur *</label>
                    <select name="prof_id" id="prof_id" required>
                        <option value="">-- Sélectionner un professeur --</option>
                        <?php while ($prof = $resultProfs->fetch_assoc()): ?>
                            <option value="<?= $prof['id'] ?>">
                                <?= e($prof['nom']) ?> (<?= e($prof['email']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="nom_cours">Nom de la matière *</label>
                    <input type="text" name="nom_cours" id="nom_cours" 
                           placeholder="Ex: Développement Web" required>
                </div>
                
                <div class="form-group">
                    <label for="description_cours">Description</label>
                    <textarea name="description_cours" id="description_cours" 
                              placeholder="Description de la matière..."></textarea>
                </div>
                
                <button type="submit" class="btn-submit">✅ Attribuer la matière</button>
            </form>
        </div>
        
        <!-- Statistiques rapides -->
        <div class="form-card">
            <h2>📊 Statistiques</h2>
            <div style="display: grid; gap: 15px;">
                <div style="padding: 20px; background: linear-gradient(135deg, #ede9fe, #ddd6fe); border-radius: 12px;">
                    <div style="font-size: 32px; font-weight: 700; color: #5b21b6;">
                        <?php
                        $resultProfs->data_seek(0);
                        echo $resultProfs->num_rows;
                        ?>
                    </div>
                    <div style="color: #6b7280; font-size: 14px; margin-top: 5px;">Professeurs</div>
                </div>
                <div style="padding: 20px; background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-radius: 12px;">
                    <div style="font-size: 32px; font-weight: 700; color: #1e40af;">
                        <?= $resultAttributions->num_rows ?>
                    </div>
                    <div style="color: #6b7280; font-size: 14px; margin-top: 5px;">Matières attribuées</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des attributions -->
    <div class="attributions-table">
        <h2>📋 Attributions Actuelles</h2>
        
        <?php if ($resultAttributions->num_rows === 0): ?>
            <div class="empty-state">
                <p>Aucune matière n'a encore été attribuée.</p>
            </div>
        <?php else: ?>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Professeur</th>
                        <th>Email</th>
                        <th>Matière</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($attr = $resultAttributions->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= e($attr['prof_nom'] ?? 'Non assigné') ?></strong></td>
                            <td><?= e($attr['prof_email'] ?? '-') ?></td>
                            <td><strong><?= e($attr['nom_cours']) ?></strong></td>
                            <td><?= e($attr['description_cours'] ?? '-') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<script src="assets/js/dashboard.js?v=4" defer></script>

</body>
</html>
<?php $conn->close(); ?>
