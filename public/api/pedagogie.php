<?php
// Calendrier interne de la page Pédagogie (FullCalendar) : animations planifiées.
// Couleur verte si l'animation est aussi publiée sur le calendrier public, violette sinon.
require_once '../../src/init.php';
header('Content-Type: application/json; charset=utf-8');

$planifs = $pdo->query("SELECT c.id, c.titre, c.date_debut, c.date_fin, c.notes, e.id AS publie
                        FROM peda_calendrier c LEFT JOIN evenements e ON e.id = c.evenement_id")->fetchAll();
$events = [];
foreach ($planifs as $p) {
    $events[] = [
        'id'    => $p['id'],
        'title' => ($p['publie'] ? '🌐 ' : '') . $p['titre'],
        'start' => $p['date_debut'],
        'end'   => $p['date_fin'],
        'color' => $p['publie'] ? '#1B6B2F' : '#8E44AD',
    ];
}
echo json_encode($events, JSON_UNESCAPED_UNICODE);
