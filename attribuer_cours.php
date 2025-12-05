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

$message = "";
$messageType = "";

// Traitement de l'attribution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cours_id']) && isset($_POST['prof_id'])) {
    $coursId = intval($_POST['cours_id']);
    $profId = intval($_POST['prof_id']);
    
    $sqlUpdate = "UPDATE cours SET prof_id = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ii", $profId, $coursId);
    
    if ($stmtUpdate->execute()) {
        $message = "✅ Cours assigné avec succès!";
        $messageType = "success";
    } else {
        $message = "❌ Erreur lors de l'attribution: " . $conn->error;
        $messageType = "error";
    }
}

// Récupérer tous les cours
$sqlCours = "SELECT c.id, c.nom_cours, c.description_cours, c.prof_id, u.nom as prof_nom
             FROM cours c
             LEFT JOIN users u ON c.prof_id = u.id
             ORDER BY c.nom_cours";
$resultCours = $conn->query($sqlCours);

// Récupérer tous les professeurs
$sqlProfs = "SELECT id, nom, email FROM users WHERE role = 'prof' ORDER BY nom";
$resultProfs = $conn->query($sqlProfs);
$professeurs = [];
while ($prof = $resultProfs->fetch_assoc()) {
    $professeurs[] = $prof;
}

function e($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attribuer les cours</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .attribution-container {
            max-width: 1200px;
            margin: 120px auto 40px;
            padding: 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(91, 33, 182, 0.08);
        }
        
        .page-title {
            font-size: 28px;
            color: #5b21b6;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .page-subtitle {
            color: #6b7280;
            margin-bottom: 30px;
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        
        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #dc2626;
        }
        
        .cours-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .cours-table th {
            background: #5b21b6;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .cours-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .cours-table tr:hover {
            background: #f9fafb;
        }
        
        .select-prof {
            padding: 8px 12px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            min-width: 200px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .select-prof:focus {
            outline: none;
            border-color: #7c3aed;
        }
        
        .btn-assign {
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-assign:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }
        
        .assigned-badge {
            background: #d1fae5;
            color: #065f46;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .unassigned-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .back-btn {
            display: inline-block;
            background: #6b7280;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">🎓 BOOSTSR51</div>
    <div class="nav-actions">
        <a href="prof_dashboard.php" class="back-btn">← Retour au dashboard</a>
    </div>
</div>

<div class="attribution-container">
    <h1 class="page-title">Attribution des cours aux professeurs</h1>
    <p class="page-subtitle">Assignez chaque cours à un professeur pour qu'il apparaisse dans "Mes cours"</p>
    
    <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>
    
    <table class="cours-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du cours</th>
                <th>Description</th>
                <th>Statut</th>
                <th>Professeur assigné</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($cours = $resultCours->fetch_assoc()): ?>
                <tr>
                    <td><?= $cours['id'] ?></td>
                    <td><strong><?= e($cours['nom_cours']) ?></strong></td>
                    <td><?= e($cours['description_cours'] ?? 'Aucune description') ?></td>
                    <td>
                        <?php if ($cours['prof_id']): ?>
                            <span class="assigned-badge">✓ Assigné</span>
                        <?php else: ?>
                            <span class="unassigned-badge">⚠ Non assigné</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                            <input type="hidden" name="cours_id" value="<?= $cours['id'] ?>">
                            <select name="prof_id" class="select-prof">
                                <option value="">-- Sélectionner un professeur --</option>
                                <?php foreach ($professeurs as $prof): ?>
                                    <option value="<?= $prof['id'] ?>" 
                                            <?= ($cours['prof_id'] == $prof['id']) ? 'selected' : '' ?>>
                                        <?= e($prof['nom']) ?> (<?= e($prof['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-assign">Assigner</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php $conn->close(); ?>
