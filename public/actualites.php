<?php
require_once '../src/init.php';

// ── Filtre par mois (ex: ?mois=2026-10) ──────────────────────────────────────
$filtreMois = isset($_GET['mois']) && preg_match('/^\d{4}-\d{2}$/', $_GET['mois'])
    ? $_GET['mois'] : null;

// ── Articles à afficher ───────────────────────────────────────────────────────
if ($filtreMois) {
    $stmt = $pdo->prepare(
        "SELECT * FROM articles WHERE " . sql_article_visible() . "
         AND DATE_FORMAT(published_at, '%Y-%m') = ?
         ORDER BY published_at DESC"
    );
    $stmt->execute([$filtreMois]);
    $articles = $stmt->fetchAll();
} else {
    // Par défaut : 5 derniers
    $articles = $pdo->query(
        "SELECT * FROM articles WHERE " . sql_article_visible() . " ORDER BY published_at DESC LIMIT 5"
    )->fetchAll();
}

// ── Archives : tous les mois ayant au moins 1 article ─────────────────────────
$archivesRaw = $pdo->query(
    "SELECT DATE_FORMAT(published_at, '%Y-%m') AS mois, COUNT(*) AS nb
     FROM articles WHERE " . sql_article_visible() . "
     GROUP BY DATE_FORMAT(published_at, '%Y-%m')
     ORDER BY mois DESC"
)->fetchAll();

// Organisé par année → [mois => nb], découpage en PHP
$archives = [];
$MOIS_FR  = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril',
             '05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août',
             '09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
foreach ($archivesRaw as $r) {
    [$annee, $numMois] = explode('-', $r['mois']);
    $archives[$annee][] = [
        'slug'  => $r['mois'],
        'label' => $MOIS_FR[$numMois] ?? $numMois,
        'nb'    => $r['nb'],
    ];
}

// ── Galerie ───────────────────────────────────────────────────────────────────
$photos = $pdo->query("SELECT * FROM galerie ORDER BY ordre ASC, created_at DESC")->fetchAll();
$categories = array_values(array_unique(array_filter(array_column($photos, 'categorie'))));
sort($categories);

$pageTitle = "Actualités — CAT";
$pageDesc  = "Toutes les actualités et la galerie photos du Club Aulnaysien de Tennis.";

// Label du filtre actif
$titreFiltreActif = '';
if ($filtreMois) {
    [$y, $m] = explode('-', $filtreMois);
    $titreFiltreActif = ($MOIS_FR[$m] ?? $m) . ' ' . $y;
}
?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
<body>

<?php
$activePage = 'actualites';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Actualités</h1>
        <p>Articles, événements et galerie photos du club</p>
    </div>
</section>

<section class="section-actu">
    <div class="container">
        <div class="tabs">
            <button class="tab-btn active" data-tab="articles">📰 Articles</button>
            <button class="tab-btn" data-tab="galerie">📷 Galerie photos</button>
        </div>

        <!-- ── Onglet Articles ─────────────────────────────────────────── -->
        <div class="tab-content active" id="tab-articles">
            <div class="actu-layout">

                <!-- Liste articles -->
                <div class="actu-main">
                    <?php if ($filtreMois): ?>
                    <div class="actu-filter-bar">
                        <span>📅 <?= htmlspecialchars($titreFiltreActif) ?> — <?= count($articles) ?> article<?= count($articles) > 1 ? 's' : '' ?></span>
                        <a href="actualites.php" class="actu-filter-reset">✕ Voir les derniers</a>
                    </div>
                    <?php endif; ?>

                    <div class="news-grid news-grid--list">
                        <?php if (empty($articles)): ?>
                            <p class="actu-empty">Aucun article pour cette période.</p>
                        <?php else: ?>
                            <?php foreach ($articles as $a): ?>
                            <article class="news-card">
                                <?php if (!empty($a['image'])): ?>
                                    <div class="news-img" style="background:url('assets/uploads/articles/<?= htmlspecialchars($a['image']) ?>') center/cover no-repeat; min-height:120px;"></div>
                                <?php else: ?>
                                    <div class="news-img" style="background:url('assets/images/placeholder.png') center/cover no-repeat; min-height:120px;"></div>
                                <?php endif; ?>
                                <div class="news-body">
                                    <span class="news-date"><?= date('d F Y', strtotime($a['published_at'])) ?></span>
                                    <h3><?= htmlspecialchars($a['titre']) ?></h3>
                                    <p><?= htmlspecialchars(mb_substr($a['contenu'], 0, 120)) ?>...</p>
                                    <a href="article.php?id=<?= $a['id'] ?>" class="news-link">Lire la suite →</a>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar archives -->
                <aside class="actu-sidebar">
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">📁 Archives</h3>
                        <?php if (empty($archives)): ?>
                            <p style="color:#999;font-size:0.85rem">Aucun article.</p>
                        <?php else: ?>
                            <?php foreach ($archives as $annee => $moisList): ?>
                            <div class="archive-year">
                                <button class="archive-year-btn" data-year="<?= $annee ?>">
                                    <span><?= $annee ?></span>
                                    <span class="archive-chevron">▾</span>
                                </button>
                                <ul class="archive-months" id="year-<?= $annee ?>">
                                    <?php foreach ($moisList as $m): ?>
                                    <li>
                                        <a href="actualites.php?mois=<?= $m['slug'] ?>"
                                           class="archive-link <?= $filtreMois === $m['slug'] ? 'active' : '' ?>">
                                            <span><?= $m['label'] ?></span>
                                            <span class="archive-count"><?= $m['nb'] ?></span>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </aside>

            </div><!-- /actu-layout -->
        </div>

        <!-- ── Onglet Galerie ──────────────────────────────────────────── -->
        <div class="tab-content" id="tab-galerie">
            <?php if (count($categories) > 0): ?>
            <div class="galerie-filtres">
                <button class="galerie-filtre active" data-cat="">Toutes</button>
                <?php foreach ($categories as $c): ?>
                <button class="galerie-filtre" data-cat="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="galerie-grid">
                <?php if (empty($photos)): ?>
                    <p style="color:#999;grid-column:1/-1">Aucune photo pour l'instant.</p>
                <?php else: ?>
                    <?php foreach ($photos as $p): ?>
                    <div class="galerie-item" data-cat="<?= htmlspecialchars($p['categorie'] ?? '') ?>">
                        <div class="galerie-img">
                            <img src="assets/uploads/galerie/<?= htmlspecialchars($p['fichier']) ?>"
                                 alt="<?= htmlspecialchars($p['titre']) ?>" loading="lazy">
                        </div>
                        <p><?= htmlspecialchars($p['titre']) ?></p>
                        <?php if (!empty($p['categorie'])): ?><span class="galerie-cat"><?= htmlspecialchars($p['categorie']) ?></span><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php include '../templates/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
// Accordéon années dans la sidebar
document.querySelectorAll('.archive-year-btn').forEach(btn => {
    const year = btn.dataset.year;
    const ul   = document.getElementById('year-' + year);
    btn.addEventListener('click', () => {
        const collapsed = ul.classList.toggle('collapsed');
        btn.querySelector('.archive-chevron').textContent = collapsed ? '▸' : '▾';
    });
});

// Ouvre l'année qui contient le mois actif, ferme les autres
const activeLink = document.querySelector('.archive-link.active');
if (activeLink) {
    document.querySelectorAll('.archive-months').forEach(ul => {
        if (!ul.contains(activeLink)) {
            ul.classList.add('collapsed');
            ul.closest('.archive-year')
              .querySelector('.archive-chevron').textContent = '▸';
        }
    });
} else {
    // Pas de filtre actif : ferme toutes sauf la première
    document.querySelectorAll('.archive-months').forEach((ul, i) => {
        if (i > 0) {
            ul.classList.add('collapsed');
            ul.closest('.archive-year')
              .querySelector('.archive-chevron').textContent = '▸';
        }
    });
}

// Filtre de la galerie par catégorie
document.querySelectorAll('.galerie-filtre').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.galerie-filtre').forEach(b => b.classList.toggle('active', b === btn));
        document.querySelectorAll('.galerie-grid .galerie-item').forEach(item => {
            item.hidden = btn.dataset.cat !== '' && item.dataset.cat !== btn.dataset.cat;
        });
    });
});
</script>
</body>
</html>
