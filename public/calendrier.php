<?php
require_once '../src/init.php';
$pageTitle = "Calendrier — CAT";
$pageDesc = "Calendrier des événements, animations et cours du Club Aulnaysien de Tennis.";
?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
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
            <?php foreach (EVENT_TYPES as $type): ?>
            <span class="legend-item"><span class="legend-dot" style="background:<?= $type['couleur'] ?>"></span><?= $type['label'] ?></span>
            <?php endforeach; ?>
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
        locale: 'fr',
        headerToolbar: window.innerWidth < 700
            ? { left: 'prev,next', center: 'title', right: 'today' }
            : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
        // Sur téléphone, la vue liste est plus lisible que la grille du mois
        initialView: window.innerWidth < 700 ? 'listMonth' : 'dayGridMonth',
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
