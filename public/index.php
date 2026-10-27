<?php
require_once '../src/init.php';
$articles = $pdo->query("SELECT * FROM articles WHERE " . sql_article_visible() . " ORDER BY published_at DESC LIMIT 3")->fetchAll();

// 3 prochains événements : pas encore terminés (ou du jour s'ils n'ont pas de fin)
$stmt = $pdo->prepare(
    "SELECT * FROM evenements
     WHERE (date_fin IS NOT NULL AND date_fin >= ?) OR (date_fin IS NULL AND date_debut >= ?)
     ORDER BY date_debut LIMIT 3"
);
$stmt->execute([date('Y-m-d H:i:s'), date('Y-m-d 00:00:00')]);
$evenements = $stmt->fetchAll();

$pageTitle = param('nom_club', 'Club Aulnaysien de Tennis');
$pageDesc = param('description', "Bienvenue sur le site officiel du Club Aulnaysien de Tennis à Aulnay-sous-Bois.");
?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
<body>

    <!-- NAVBAR -->
    <?php
    $activePage = 'accueil';
    include '../templates/navbar.php';
    ?>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-content">
            <h1>Club Aulnaysien de Tennis</h1>
            <p>Rejoignez-nous à Aulnay-sous-Bois</p>
            <a href="<?= BASE_URL ?>/club.php#tarifs" class="btn-primary">Nous rejoindre</a>
        </div>
    </section>

    <!-- Section : Présentation rapide -->
    <section class="section-about">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <span class="section-tag">Le club</span>
                    <h2>Bienvenue au CAT</h2>
                    <p>Fondé à Aulnay-sous-Bois, le Club Aulnaysien de Tennis accueille joueurs de tous niveaux, des débutants aux compétiteurs. Cours collectifs, tournois internes, animations jeunes — il y en a pour tout le monde.</p>
                    <a href="club.php" class="btn-secondary">En savoir plus</a>
                </div>
                <?php include '../templates/stats.php'; ?>
            </div>
        </div>
    </section>

    <!-- Section : Dernières actualités -->
    <section class="section-news">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Actualités</span>
                <h2>Les dernières nouvelles du club</h2>
                <a href="actualites.php" class="btn-link">Voir tout →</a>
            </div>
            <div class="news-grid">
                <?php if (empty($articles)): ?>
                    <p class="etat-vide" data-icone="📰">Aucun article publié pour l'instant.</p>
                <?php else: ?>
                    <?php foreach ($articles as $a): ?>
                    <article class="news-card">
                        <?php if (!empty($a['image'])): ?>
                            <div class="news-img" style="background:url('assets/uploads/articles/<?= htmlspecialchars($a['image']) ?>') center/cover no-repeat"></div>
                        <?php else: ?>
                            <div class="news-img" style="background:url('assets/images/placeholder.png') center/cover no-repeat"></div>
                        <?php endif; ?>
                        <div class="news-body">
                            <span class="news-date"><?= date('d F Y', strtotime($a['published_at'])) ?></span>
                            <h3><?= htmlspecialchars($a['titre']) ?></h3>
                            <p><?= htmlspecialchars(texte_brut($a['contenu'], 120)) ?></p>
                            <a href="article.php?id=<?= $a['id'] ?>" class="news-link">Lire la suite →</a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section : Prochains événements (masquée s'il n'y en a aucun) -->
    <?php if ($evenements): ?>
    <section class="section-agenda">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Agenda</span>
                <h2>Prochains événements</h2>
                <a href="calendrier.php" class="btn-link">Tout le calendrier →</a>
            </div>
            <div class="agenda-grid">
                <?php foreach ($evenements as $e):
                    $type    = EVENT_TYPES[$e['type']] ?? EVENT_TYPES['autre'];
                    $debut   = strtotime($e['date_debut']);
                    $enCours = $debut <= time();
                ?>
                <a href="calendrier.php?evenement=<?= $e['id'] ?>" class="agenda-card" style="--type: <?= $type['couleur'] ?>">
                    <div class="agenda-date">
                        <span class="agenda-jour"><?= date('j', $debut) ?></span>
                        <span class="agenda-mois"><?= MOIS_FR[date('m', $debut)] ?></span>
                    </div>
                    <div class="agenda-infos">
                        <span class="agenda-type"><?= $type['label'] ?><?php if ($enCours): ?> <span class="agenda-encours">En cours</span><?php endif; ?></span>
                        <h3><?= htmlspecialchars($e['titre']) ?></h3>
                        <span class="agenda-quand">🕐 <?= quand_evenement($e) ?></span>
                        <?php if ($e['description']): ?>
                        <p><?= htmlspecialchars(mb_strimwidth($e['description'], 0, 90, '…')) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Section : Réserver un court -->
    <section class="section-reservation">
        <div class="container">
            <div class="reservation-content">
                <div>
                    <h2>Réservez votre court</h2>
                    <p>La réservation des courts se fait via la plateforme Ten'up, le service officiel de la Fédération Française de Tennis.</p>
                </div>
                <a href="https://tenup.fft.fr/" target="_blank" rel="noopener" class="btn-primary">Réserver sur Ten'up</a>
            </div>
        </div>
    </section>

<?php include '../templates/footer.php'; ?>

<!-- ══ WIDGET MÉTÉO FIXE ══ -->
<!-- Pill et panneau sont des éléments fixed indépendants pour éviter tout conflit de stacking -->
<div class="meteo-pill" id="meteo-pill">
    <span class="meteo-pill-icon" id="m-icon">⏳</span>
    <span class="meteo-pill-temp" id="m-temp">--°</span>
</div>
<div class="meteo-panel" id="meteo-panel">
    <div class="meteo-today">
        <div class="meteo-today-icon" id="mp-icon">⏳</div>
        <div class="meteo-today-info">
            <div class="meteo-today-temp" id="mp-temp">--°C</div>
            <div class="meteo-today-desc" id="mp-desc">Chargement…</div>
            <div class="meteo-today-loc">📍 Aulnay-sous-Bois</div>
        </div>
    </div>
    <div class="meteo-week" id="mp-week"></div>
</div>

    <script src="assets/js/main.js"></script>
    <script>
    /* ══ WIDGET MÉTÉO FIXE — Open-Meteo, Aulnay-sous-Bois ══ */
    const METEO_URL = 'https://api.open-meteo.com/v1/forecast'
        + '?latitude=48.938&longitude=2.497'
        + '&current=temperature_2m,weather_code'
        + '&daily=weather_code,temperature_2m_max,temperature_2m_min'
        + '&timezone=Europe%2FParis'
        + '&forecast_days=7';

    const WC = {
        0:  ['☀️','Ensoleillé'],   1:  ['🌤️','Peu nuageux'],    2:  ['⛅','Part. nuageux'],
        3:  ['☁️','Couvert'],      45: ['🌫️','Brouillard'],      48: ['🌫️','Brouillard givrant'],
        51: ['🌦️','Bruine légère'],53: ['🌦️','Bruine'],          55: ['🌧️','Bruine forte'],
        61: ['🌧️','Pluie légère'], 63: ['🌧️','Pluie'],           65: ['🌧️','Pluie forte'],
        71: ['🌨️','Neige légère'], 73: ['🌨️','Neige'],           75: ['❄️','Neige forte'],
        80: ['🌦️','Averses'],      81: ['🌧️','Averses mod.'],    82: ['⛈️','Averses fortes'],
        95: ['⛈️','Orage'],        96: ['⛈️','Orage+grêle'],     99: ['⛈️','Orage violent'],
    };
    const JOURS = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'];

    fetch(METEO_URL)
      .then(r => r.json())
      .then(data => {
        const cur = data.current;
        const temp = Math.round(cur.temperature_2m);
        const code = cur.weather_code ?? cur.weathercode ?? -1;
        const [icon, desc] = WC[code] ?? ['🌡️','Conditions inconnues'];

        // Pill
        document.getElementById('m-icon').textContent = icon;
        document.getElementById('m-temp').textContent = temp + '°';

        // Panneau — aujourd'hui
        document.getElementById('mp-icon').textContent = icon;
        document.getElementById('mp-temp').textContent = temp + '°C';
        document.getElementById('mp-desc').textContent = desc;

        // Panneau — semaine
        const { weather_code: wcs, temperature_2m_max: maxs, temperature_2m_min: mins, time: times } = data.daily;
        const weekEl = document.getElementById('mp-week');
        weekEl.innerHTML = '';
        times.forEach((t, i) => {
            const d   = new Date(t);
            const lbl = i === 0 ? "Auj." : JOURS[d.getDay()];
            const [ic] = WC[wcs[i]] ?? ['🌡️'];
            const mx  = Math.round(maxs[i]);
            const mn  = Math.round(mins[i]);
            weekEl.innerHTML += `
              <div class="meteo-day">
                <span class="md-label">${lbl}</span>
                <span class="md-icon">${ic}</span>
                <span class="md-temps"><strong>${mx}°</strong><span>${mn}°</span></span>
              </div>`;
        });
      })
      .catch(err => {
        console.warn('Météo indisponible:', err);
        document.getElementById('m-icon').textContent = '🌡️';
        document.getElementById('m-temp').textContent = '--°';
        document.getElementById('mp-desc').textContent = 'Météo indisponible';
      });

    /* Survol (ordinateur) ou appui (téléphone) sur la pill → ouvre/ferme le panneau */
    (function() {
        const pill  = document.getElementById('meteo-pill');
        const panel = document.getElementById('meteo-panel');
        let timer;

        function open()  { clearTimeout(timer); panel.classList.add('open'); }
        function close() { timer = setTimeout(() => panel.classList.remove('open'), 120); }

        pill.addEventListener('mouseenter', open);
        pill.addEventListener('mouseleave', close);
        panel.addEventListener('mouseenter', open);
        panel.addEventListener('mouseleave', close);

        pill.addEventListener('click', (e) => {
            e.stopPropagation();
            clearTimeout(timer);
            panel.classList.toggle('open');
        });
        document.addEventListener('click', (e) => {
            if (!panel.contains(e.target)) panel.classList.remove('open');
        });
    })();
    </script>
</body>
</html>