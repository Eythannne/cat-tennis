<?php
require_once '../src/config/database.php';
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
</head>
<body>

<?php
$activePage = 'calendrier';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Calendrier</h1>
        <p>Animations, cours, tournois et événements du club</p>
    </div>
</section>

<section class="section-calendar">
    <div class="container">
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

<script src="assets/js/fullcalendar.min.js"></script>
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
        events: '<?= BASE_URL ?>/api/evenements.php',
        eventClick: function(info) {
            alert(info.event.title + (info.event.extendedProps.description ? '\n' + info.event.extendedProps.description : ''));
        },
        height: 'auto'
    });
    calendar.render();
});
</script>
</body>
</html>
