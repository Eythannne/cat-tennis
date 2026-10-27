<?php
require_once '../../src/init.php';
header('Content-Type: application/json');

$evenements = $pdo->query("SELECT * FROM evenements")->fetchAll();
$events = [];

foreach ($evenements as $e) {
    // La couleur vient du type, pour correspondre à la légende du calendrier
    $type = EVENT_TYPES[$e['type']] ?? EVENT_TYPES['autre'];
    $events[] = [
        'id' => $e['id'],
        'title' => $e['titre'],
        'start' => $e['date_debut'],
        'end' => $e['date_fin'],
        'color' => $type['couleur'],
        'type' => $type['label'],
        'description' => $e['description'],
    ];
}

echo json_encode($events);
