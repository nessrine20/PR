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

$profId = $_SESSION['user_id'];

// Récupérer tous les événements
$sql = "SELECT * FROM evenements ORDER BY date_debut DESC";
$result = $conn->query($sql);

function e($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Événements</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .prof-main {
            padding: 40px 60px;
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
        
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(91, 33, 182, 0.08);
        }
        
        .form-section h2 {
            color: #5b21b6;
            font-size: 20px;
            margin: 0 0 20px 0;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="date"],
        input[type="color"],
        select,
        textarea {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #7c3aed;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);
        }
        
        .events-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .events-table thead {
            background: linear-gradient(135deg, #f8f4ff, #ede3ff);
        }
        
        .events-table th {
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #5b21b6;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .events-table td {
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
        }
        
        .events-table tr:hover {
            background: #faf8ff;
        }
        
        .type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .type-formation { background: #dbeafe; color: #1e40af; }
        .type-rattrapage { background: #fed7aa; color: #c2410c; }
        .type-competition { background: #e9d5ff; color: #6b21a8; }
        .type-hackathon { background: #d1fae5; color: #065f46; }
        .type-vacances { background: #fce7f3; color: #9f1239; }
        
        .color-box {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 2px solid #e5e7eb;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }
        
        .btn-delete:hover {
            transform: scale(1.05);
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
        <li class="menu-item"><a href="prof_evenements.php" class="sidebar-btn active">📅 Événements</a></li>
    </ul>
</div>

<div class="navbar">
    <div class="logo">📅 Gestion des Événements</div>
    <div class="nav-actions">
        <div class="profile-dropdown">
            <button class="profile-btn" onclick="toggleProfileMenu()">
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
        <h1 class="page-title">📅 Gestion des Événements</h1>
        <p style="color: #6b7280; font-size: 15px;">Créez des notifications d'événements pour les étudiants (formations, rattrapages, compétitions)</p>
    </div>

    <div class="form-section">
        <h2>➕ Ajouter un nouvel événement</h2>
        <form action="prof_actions.php" method="POST">
            <input type="hidden" name="action" value="add_event">
            
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="titre">Titre de l'événement *</label>
                    <input type="text" id="titre" name="titre" required placeholder="Ex: Formation React.js">
                </div>
                
                <div class="form-group">
                    <label for="type">Type d'événement *</label>
                    <select id="type" name="type" required>
                        <option value="formation">🎓 Formation</option>
                        <option value="rattrapage">📝 Rattrapage</option>
                        <option value="competition">🏆 Compétition</option>
                        <option value="hackathon">💻 Hackathon</option>
                        <option value="vacances">🏖️ Vacances</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="couleur">Couleur</label>
                    <input type="color" id="couleur" name="couleur" value="#7c3aed">
                </div>
                
                <div class="form-group">
                    <label for="date_debut">Date de début *</label>
                    <input type="date" id="date_debut" name="date_debut" required>
                </div>
                
                <div class="form-group">
                    <label for="date_fin">Date de fin *</label>
                    <input type="date" id="date_fin" name="date_fin" required>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">✓ Créer l'événement</button>
        </form>
    </div>

    <div class="form-section">
        <h2>📋 Liste des événements</h2>
        <?php if ($result->num_rows > 0): ?>
            <table class="events-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Couleur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($event = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= e($event['titre']) ?></strong></td>
                            <td>
                                <span class="type-badge type-<?= e($event['type']) ?>">
                                    <?= ucfirst(e($event['type'])) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($event['date_debut'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($event['date_fin'])) ?></td>
                            <td><span class="color-box" style="background-color: <?= e($event['couleur']) ?>"></span></td>
                            <td>
                                <form action="prof_actions.php" method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Supprimer cet événement ?');">
                                    <input type="hidden" name="action" value="delete_event">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button type="submit" class="btn-delete">🗑️ Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #9ca3af; padding: 30px;">Aucun événement créé pour le moment.</p>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("active");
}

function toggleProfileMenu() {
    document.getElementById("profileMenu").classList.toggle("show");
}

document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.profile-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
        document.getElementById("profileMenu").classList.remove("show");
    }
});
</script>

</body>
</html>
