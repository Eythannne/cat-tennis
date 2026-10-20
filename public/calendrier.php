<?php
$pageTitle = "Calendrier — CAT";
$pageDesc = "Calendrier des événements, animations et cours du Club Aulnaysien de Tennis.";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $pageDesc ?>">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
</head>
<body>

<?php
$activePage = 'calendrier';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="page-hero">

<section class="page-hero">
    <div class="container">
        <h1>Calendrier</h1>
        <p>Animations, cours, tournois et événements du club</p>
    </div>
</section>

<section class="section-calendar">
    <div class="container">

        <!-- Légende -->
        <div class="cal-legend">
            <span class="legend-item"><span class="legend-dot" style="background:#1A5276"></span>Tournoi</span>
            <span class="legend-item"><span class="legend-dot" style="background:#1E8449"></span>Animation</span>
            <span class="legend-item"><span class="legend-dot" style="background:#D4AC0D"></span>Cours / Stage</span>
            <span class="legend-item"><span class="legend-dot" style="background:#884EA0"></span>Vacances scolaires</span>
        </div>

        <div id="calendar"></div>

    </div>
</section>

<?php include '../templates/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        buttonText: {
            today: "Aujourd'hui",
            month: 'Mois',
            list: 'Liste'
        },
        events: [
            // Données statiques pour l'instant — viendront de la BDD
            { title: 'Tournoi interne automne', start: '2026-10-24', end: '2026-10-25', color: '#1A5276' },
            { title: 'Vacances Toussaint', start: '2026-10-18', end: '2026-11-03', color: '#884EA0', display: 'background' },
            { title: 'Stage vacances Toussaint', start: '2026-10-26', end: '2026-10-30', color: '#D4AC0D' },
            { title: 'Reprise cours collectifs', start: '2026-11-04', color: '#D4AC0D' },
            { title: 'Animation parents-enfants', start: '2026-11-15', color: '#1E8449' },
            { title: 'Tournoi Noël', start: '2026-12-13', end: '2026-12-14', color: '#1A5276' },
            { title: 'Vacances Noël', start: '2026-12-20', end: '2027-01-05', color: '#884EA0', display: 'background' },
        ],
        eventClick: function(info) {
            alert(info.event.title + '\nDu ' + info.event.startStr + (info.event.endStr ? ' au ' + info.event.endStr : ''));
        },
        height: 'auto'
    });
    calendar.render();
});
</script>
</body>
</html>