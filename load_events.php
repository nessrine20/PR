<?php
header('Content-Type: application/json');

$pdo = new PDO("mysql:host=localhost;dbname=revision;charset=utf8", "root", "");

// Récupérer événements
$stmt = $pdo->query("SELECT * FROM evenements");
$events = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $events[] = [
        "title" => $row["titre"],
        "start" => $row["date_debut"],
        "end"   => $row["date_fin"],
        "color" => $row["couleur"] ?? '#7c3aed',
        "type"  => $row["type"] ?? 'autre'
    ];
}

echo json_encode($events);
