<?php
require_once '../../src/config/database.php';
header('Content-Type: application/json');

$evenements = $pdo->query("SELECT * FROM evenements")->fetchAll();
$events = [];

foreach ($evenements as $e) {
    $events[] = [
        'title' => $e['titre'],
        'start' => $e['date_debut'],
        'end' => $e['date_fin'],
        'color' => $e['couleur'],
        'description' => $e['description'],
    ];
}

echo json_encode($events);