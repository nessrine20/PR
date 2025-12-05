<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'prof') {
    header('Location: login.html');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'revision');
if ($conn->connect_error) {
    die('Erreur de connexion Ã  la base de donnÃ©es.');
}

$action = $_POST['action'] ?? '';
$coursId = isset($_POST['cours_id']) ? (int) $_POST['cours_id'] : 0;

function redirect($coursId, $status, $message) {
    header("Location: prof_contenus.php?cours_id=$coursId&status=$status&message=" . urlencode($message));
    exit();
}

if ($action === 'add_content') {
    $titre = trim($_POST['titre']);
    $type = $_POST['type_contenu'];
    
    // Upload fichier
    $filePath = '';
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName = time() . '_' . basename($_FILES['fichier']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $targetPath)) {
            $filePath = $targetPath;
        } else {
            redirect($coursId, 'error', "Erreur lors de l'upload du fichier.");
        }
    } else {
        redirect($coursId, 'error', "Veuillez sÃ©lectionner un fichier valide.");
    }

    $stmt = $conn->prepare("INSERT INTO contenus (cours_id, titre, type_contenu, fichier) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $coursId, $titre, $type, $filePath);
    
    if ($stmt->execute()) {
        redirect($coursId, 'success', "Contenu ajoutÃ© avec succÃ¨s !");
    } else {
        redirect($coursId, 'error', "Erreur lors de l'ajout en base de donnÃ©es.");
    }
    $stmt->close();

    $stmt->close();

} elseif ($action === 'update_content') {
    $id = (int) $_POST['id'];
    $titre = trim($_POST['titre']);
    $type = $_POST['type_contenu'];
    
    // Gestion du fichier optionnel
    $filePath = null;
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName = time() . '_' . basename($_FILES['fichier']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $targetPath)) {
            $filePath = $targetPath;
            
            // Supprimer l'ancien fichier
            $stmt = $conn->prepare("SELECT fichier FROM contenus WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                if (file_exists($row['fichier'])) {
                    unlink($row['fichier']);
                }
            }
            $stmt->close();
        }
    }

    // Mise Ã  jour SQL
    if ($filePath) {
        $stmt = $conn->prepare("UPDATE contenus SET titre = ?, type_contenu = ?, fichier = ? WHERE id = ?");
        $stmt->bind_param("sssi", $titre, $type, $filePath, $id);
    } else {
        $stmt = $conn->prepare("UPDATE contenus SET titre = ?, type_contenu = ? WHERE id = ?");
        $stmt->bind_param("ssi", $titre, $type, $id);
    }

    if ($stmt->execute()) {
        redirect($coursId, 'success', "Contenu modifiÃ© avec succÃ¨s.");
    } else {
        redirect($coursId, 'error', "Erreur lors de la modification.");
    }
    $stmt->close();

} elseif ($action === 'delete_content') {
    $id = (int) $_POST['id'];
    
    // RÃ©cupÃ©rer le fichier pour le supprimer du serveur
    $stmt = $conn->prepare("SELECT fichier FROM contenus WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (file_exists($row['fichier'])) {
            unlink($row['fichier']);
        }
    }
    $stmt->close();

    // Supprimer de la BDD
    $stmt = $conn->prepare("DELETE FROM contenus WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        redirect($coursId, 'success', "Contenu supprimÃ© avec succÃ¨s.");
    } else {
        redirect($coursId, 'error', "Erreur lors de la suppression.");
    }
    $stmt->close();
} else {
    header("Location: mes_cours.php");
    exit();
}
?>

// ===== GESTION DES ÉVÉNEMENTS =====
if ($action === 'add_event') {
    $titre = trim($_POST['titre']);
    $type = $_POST['type'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $couleur = $_POST['couleur'] ?? '#7c3aed';
    
    $stmt = $conn->prepare('INSERT INTO evenements (titre, type, date_debut, date_fin, couleur) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $titre, $type, $date_debut, $date_fin, $couleur);
    
    if ($stmt->execute()) {
        header('Location: prof_evenements.php?status=success&message=' . urlencode('Événement créé !'));
    } else {
        header('Location: prof_evenements.php?status=error&message=' . urlencode('Erreur.'));
    }
    $stmt->close();
    exit();
}

if ($action === 'delete_event') {
    $eventId = (int) $_POST['event_id'];
    
    $stmt = $conn->prepare('DELETE FROM evenements WHERE id = ?');
    $stmt->bind_param('i', $eventId);
    
    if ($stmt->execute()) {
        header('Location: prof_evenements.php?status=success&message=' . urlencode('Événement supprimé !'));
    } else {
        header('Location: prof_evenements.php?status=error&message=' . urlencode('Erreur.'));
    }
    $stmt->close();
    exit();
}
