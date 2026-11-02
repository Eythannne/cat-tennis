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

// Jours fériés des années affichées (FullCalendar envoie la période visible en start / end)
$anneeDebut = (int)substr($_GET['start'] ?? '', 0, 4) ?: (int)date('Y');
$anneeFin   = (int)substr($_GET['end'] ?? '', 0, 4) ?: $anneeDebut;
$anneeFin   = min(max($anneeFin, $anneeDebut), $anneeDebut + 2);
for ($annee = $anneeDebut; $annee <= $anneeFin; $annee++) {
    foreach (jours_feries($annee) as $date => $nom) {
        // Fond teinté sur la case du jour + étiquette « Férié » (visible aussi en vue liste)
        $events[] = ['start' => $date, 'display' => 'background', 'classNames' => ['fc-ferie-fond']];
        $events[] = [
            'id' => "ferie-$date",
            'title' => $nom,
            'start' => $date,
            'allDay' => true,
            'color' => FERIE_COULEUR,
            'type' => 'Jour férié',
            'classNames' => ['fc-ferie'],
        ];
    }
}

echo json_encode($events);
