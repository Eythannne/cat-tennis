<?php
require_once '../src/init.php';
$pageTitle = "Calendrier — CAT";
$pageDesc = "Calendrier des événements, animations et cours du Club Aulnaysien de Tennis.";

// Lien direct vers un événement (ex : depuis l'accueil) : on ouvre le bon mois puis sa pop-up
$evenementCible = null;
if (isset($_GET['evenement'])) {
    $stmt = $pdo->prepare("SELECT id, date_debut FROM evenements WHERE id = ?");
    $stmt->execute([(int)$_GET['evenement']]);
    $evenementCible = $stmt->fetch() ?: null;
}
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

<!-- Pop-up de détail d'un événement -->
<div class="event-modal" id="event-modal" hidden>
    <div class="event-modal-box" role="dialog" aria-modal="true" aria-labelledby="em-titre">
        <button type="button" class="event-modal-close" id="em-close" aria-label="Fermer">✕</button>
        <span class="event-modal-type" id="em-type"></span>
        <h3 id="em-titre"></h3>
        <p class="event-modal-date" id="em-date"></p>
        <p class="event-modal-desc" id="em-desc"></p>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

<script src="assets/js/fullcalendar.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'fr',
        <?php if ($evenementCible): ?>
        initialDate: '<?= date('Y-m-d', strtotime($evenementCible['date_debut'])) ?>',
        <?php endif; ?>
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
            info.jsEvent.preventDefault();
            ouvrirEvenement(info.event);
        },
        height: 'auto'
    });
    calendar.render();

    <?php if ($evenementCible): ?>
    // Ouvre la pop-up de l'événement demandé dès que les événements sont chargés
    let dejaOuvert = false;
    calendar.on('eventsSet', function() {
        const ev = calendar.getEventById('<?= (int)$evenementCible['id'] ?>');
        if (ev && !dejaOuvert) { dejaOuvert = true; ouvrirEvenement(ev); }
    });
    <?php endif; ?>
});

// ── Pop-up de détail ─────────────────────────────────────────────────────────
const modal = document.getElementById('event-modal');

function jour(d)  { return d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }
function heure(d) { return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }).replace(':', 'h'); }
function aUneHeure(d) { return d.getHours() !== 0 || d.getMinutes() !== 0; }

// "samedi 14 novembre 2026 · 14h00 – 18h00" ou "Du ... au ..." sur plusieurs jours
function texteDate(debut, fin) {
    if (!fin || debut.toDateString() === fin.toDateString()) {
        let texte = jour(debut);
        if (aUneHeure(debut)) texte += ' · ' + heure(debut) + (fin && aUneHeure(fin) ? ' – ' + heure(fin) : '');
        return texte.charAt(0).toUpperCase() + texte.slice(1);
    }
    return 'Du ' + jour(debut) + (aUneHeure(debut) ? ' (' + heure(debut) + ')' : '')
         + ' au ' + jour(fin) + (aUneHeure(fin) ? ' (' + heure(fin) + ')' : '');
}

function ouvrirEvenement(ev) {
    const type = document.getElementById('em-type');
    type.textContent = ev.extendedProps.type || '';
    type.style.background = ev.backgroundColor;
    document.getElementById('em-titre').textContent = ev.title;
    document.getElementById('em-date').textContent = texteDate(ev.start, ev.end);
    const desc = document.getElementById('em-desc');
    desc.textContent = ev.extendedProps.description || '';
    desc.hidden = !ev.extendedProps.description;
    modal.hidden = false;
    document.getElementById('em-close').focus();
}

function fermerEvenement() { modal.hidden = true; }
document.getElementById('em-close').addEventListener('click', fermerEvenement);
modal.addEventListener('click', (e) => { if (e.target === modal) fermerEvenement(); });
document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.hidden) fermerEvenement(); });
</script>
</body>
</html>
