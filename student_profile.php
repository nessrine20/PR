<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "revision");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$user = null;

// Récupérer les infos de l'étudiant
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
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f8f5ff 0%, #f0ebff 100%);
            min-height: 100vh;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
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
            margin: 0 0 20px 0;
        }
        
        .back-link {
            color: #7c3aed;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .back-link:hover {
            color: #6d28d9;
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
    </style>
</head>
<body>

<div class="profile-container">
    <a href="dashboard.php" class="back-link">← Retour au dashboard</a>
    
    <div class="profile-header">
        <div class="profile-avatar">🎓</div>
        <h1 class="profile-name"><?= e($user['nom'] ?? 'Étudiant') ?></h1>
        <p class="profile-role">Étudiant</p>
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
                <span class="info-value">Étudiant</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
