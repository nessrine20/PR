<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'prof') {
    header("Location: login.html");
    exit();
}

// Connexion DB
$conn = new mysqli("localhost", "root", "", "revision");
if ($conn->connect_error) {
    die("Erreur connexion DB : " . $conn->connect_error);
}

$profId = $_SESSION['user_id'];
$message = "";
$messageType = "";

// TRAITEMENT DES ACTIONS (Ajouter, Modifier, Supprimer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // AJOUTER une nouvelle matière
    if (isset($_POST['action']) && $_POST['action'] === 'ajouter') {
        $nomCours = trim($_POST['nom_cours']);
        $descCours = trim($_POST['description_cours']);
        
        if (!empty($nomCours)) {
            $sql = "INSERT INTO cours (nom_cours, description_cours, prof_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nomCours, $descCours, $profId);
            
            if ($stmt->execute()) {
                $message = "✅ Matière ajoutée avec succès!";
                $messageType = "success";
            } else {
                $message = "❌ Erreur lors de l'ajout: " . $conn->error;
                $messageType = "error";
            }
        } else {
            $message = "❌ Le nom de la matière est obligatoire!";
            $messageType = "error";
        }
    }
    
    // MODIFIER une matière existante
    if (isset($_POST['action']) && $_POST['action'] === 'modifier') {
        $coursId = intval($_POST['cours_id']);
        $nomCours = trim($_POST['nom_cours']);
        $descCours = trim($_POST['description_cours']);
        
        if (!empty($nomCours)) {
            $sql = "UPDATE cours SET nom_cours = ?, description_cours = ? WHERE id = ? AND prof_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $nomCours, $descCours, $coursId, $profId);
            
            if ($stmt->execute()) {
                $message = "✅ Matière modifiée avec succès!";
                $messageType = "success";
            } else {
                $message = "❌ Erreur lors de la modification: " . $conn->error;
                $messageType = "error";
            }
        }
    }
    
    // SUPPRIMER une matière
    if (isset($_POST['action']) && $_POST['action'] === 'supprimer') {
        $coursId = intval($_POST['cours_id']);
        
        $sql = "DELETE FROM cours WHERE id = ? AND prof_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $coursId, $profId);
        
        if ($stmt->execute()) {
            $message = "✅ Matière supprimée avec succès!";
            $messageType = "success";
        } else {
            $message = "❌ Erreur lors de la suppression: " . $conn->error;
            $messageType = "error";
        }
    }
}

// Récupérer les matières du professeur
$sql = "SELECT id, nom_cours, description_cours 
        FROM cours 
        WHERE prof_id = ?
        ORDER BY nom_cours ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profId);
$stmt->execute();
$cours_result = $stmt->get_result();

function e($val) {
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes cours</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            animation: slideIn 0.3s ease;
        }
        
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }
        
        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #dc2626;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .add-course-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(91, 33, 182, 0.1);
        }
        
        .add-course-section h2 {
            color: #5b21b6;
            margin: 0 0 20px 0;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #7c3aed;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }
        
        .courses-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(91, 33, 182, 0.1);
        }
        
        .courses-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .courses-table th {
            background: #5b21b6;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .courses-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .courses-table tr:hover {
            background: #f9fafb;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit,
        .btn-delete {
            padding: 6px 15px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        
        .btn-edit:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        
        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        
        .empty-state {
            background: white;
            padding: 60px 20px;
            border-radius: 12px;
            text-align: center;
            color: #6b7280;
        }
        
        .empty-state p {
            font-size: 16px;
            margin: 0;
        }
        
        /* Modal pour édition */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            color: #5b21b6;
            margin: 0;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
        }
        
        .close-modal:hover {
            color: #374151;
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
        <li class="menu-item"><a href="mes_cours.php" class="sidebar-btn active">📘 Mes cours</a></li>
        <li class="menu-item"><a href="liste_etudiants.php" class="sidebar-btn">🎓 Liste étudiants</a></li>
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

<main class="prof-main">
    <h1 class="dashboard-title">Mes Matières</h1>
    <p class="dashboard-subtitle">Gérez vos matières: ajouter, modifier ou supprimer</p>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout -->
    <div class="add-course-section">
        <h2>➕ Ajouter une nouvelle matière</h2>
        <form method="POST">
            <input type="hidden" name="action" value="ajouter">
            <div class="form-group">
                <label for="nom_cours">Nom de la matière *</label>
                <input type="text" id="nom_cours" name="nom_cours" placeholder="Ex: Mathématiques" required>
            </div>
            <div class="form-group">
                <label for="description_cours">Description</label>
                <textarea id="description_cours" name="description_cours" placeholder="Description de la matière..."></textarea>
            </div>
            <button type="submit" class="btn-submit">Ajouter la matière</button>
        </form>
    </div>

    <!-- Liste des matières -->
    <?php if ($cours_result->num_rows === 0): ?>
        <div class="empty-state">
            <p>📚 Vous n'avez pas encore créé de matières. Utilisez le formulaire ci-dessus pour en ajouter une!</p>
        </div>
    <?php else: ?>
        <div class="courses-table">
            <table>
                <thead>
                    <tr>
                        <th>Nom de la matière</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cours = $cours_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= e($cours['nom_cours']) ?></strong></td>
                            <td><?= e($cours['description_cours']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-edit" onclick="openEditModal(<?= $cours['id'] ?>, '<?= e($cours['nom_cours']) ?>', '<?= e($cours['description_cours']) ?>')">
                                        ✏️ Modifier
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette matière?');">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="cours_id" value="<?= $cours['id'] ?>">
                                        <button type="submit" class="btn-delete">🗑️ Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<!-- Modal de modification -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✏️ Modifier la matière</h3>
            <button class="close-modal" onclick="closeEditModal()">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="cours_id" id="edit_cours_id">
            <div class="form-group">
                <label for="edit_nom_cours">Nom de la matière *</label>
                <input type="text" id="edit_nom_cours" name="nom_cours" required>
            </div>
            <div class="form-group">
                <label for="edit_description_cours">Description</label>
                <textarea id="edit_description_cours" name="description_cours"></textarea>
            </div>
            <button type="submit" class="btn-submit">Enregistrer les modifications</button>
        </form>
    </div>
</div>

<script src="assets/js/dashboard.js?v=4" defer></script>

<script>
function openEditModal(id, nom, description) {
    document.getElementById('edit_cours_id').value = id;
    document.getElementById('edit_nom_cours').value = nom;
    document.getElementById('edit_description_cours').value = description;
    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

// Fermer le modal en cliquant en dehors
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

</body>
</html>
<?php $conn->close(); ?>
