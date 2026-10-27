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
    // Par défaut : liste paginée, du plus récent au plus ancien
    $parPage   = 5;
    $total     = (int)$pdo->query("SELECT COUNT(*) FROM articles WHERE " . sql_article_visible())->fetchColumn();
    $nbPages   = max(1, (int)ceil($total / $parPage));
    $page      = min(max(1, (int)($_GET['page'] ?? 1)), $nbPages);
    $articles  = $pdo->query(
        "SELECT * FROM articles WHERE " . sql_article_visible() . " ORDER BY published_at DESC
         LIMIT $parPage OFFSET " . (($page - 1) * $parPage)
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
foreach ($archivesRaw as $r) {
    [$annee, $numMois] = explode('-', $r['mois']);
    $archives[$annee][] = [
        'slug'  => $r['mois'],
        'label' => MOIS_FR[$numMois] ?? $numMois,
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
    $titreFiltreActif = (MOIS_FR[$m] ?? $m) . ' ' . $y;
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

<section class="page-hero page-hero--actualites">
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
                        <a href="actualites.php" class="actu-filter-reset">✕ Voir tous les articles</a>
                    </div>
                    <?php endif; ?>

                    <div class="news-grid news-grid--list">
                        <?php if (empty($articles)): ?>
                            <p class="etat-vide" data-icone="📰">Aucun article pour cette période.</p>
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
                                    <p><?= htmlspecialchars(texte_brut($a['contenu'], 120)) ?></p>
                                    <a href="article.php?id=<?= $a['id'] ?>" class="news-link">Lire la suite →</a>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!$filtreMois && $nbPages > 1): ?>
                    <nav class="pagination" aria-label="Pages d'articles">
                        <?php if ($page > 1): ?>
                            <a href="actualites.php?page=<?= $page - 1 ?>" class="pagination-btn">← Plus récents</a>
                        <?php endif; ?>
                        <span class="pagination-pages">
                            <?php for ($p = 1; $p <= $nbPages; $p++): ?>
                                <?php if ($p === $page): ?>
                                    <span class="pagination-num active" aria-current="page"><?= $p ?></span>
                                <?php else: ?>
                                    <a href="actualites.php?page=<?= $p ?>" class="pagination-num"><?= $p ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </span>
                        <?php if ($page < $nbPages): ?>
                            <a href="actualites.php?page=<?= $page + 1 ?>" class="pagination-btn">Plus anciens →</a>
                        <?php endif; ?>
                    </nav>
                    <?php endif; ?>
                </div>

                <!-- Sidebar archives -->
                <aside class="actu-sidebar">
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">📁 Archives</h3>
                        <?php if (empty($archives)): ?>
                            <p class="etat-vide" data-icone="📁">Aucun article.</p>
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
                    <p class="etat-vide" data-icone="📷">Aucune photo pour l'instant.</p>
                <?php else: ?>
                    <?php foreach ($photos as $p): ?>
                    <div class="galerie-item" data-cat="<?= htmlspecialchars($p['categorie'] ?? '') ?>"
                         data-full="assets/uploads/galerie/<?= htmlspecialchars($p['fichier']) ?>"
                         tabindex="0" role="button" aria-label="Agrandir : <?= htmlspecialchars($p['titre']) ?>">
                        <div class="galerie-img">
                            <img src="assets/uploads/galerie/<?= htmlspecialchars(miniature_galerie($p['fichier'])) ?>"
                                 alt="<?= htmlspecialchars($p['titre']) ?>" loading="lazy">
                            <span class="galerie-zoom" aria-hidden="true">🔍</span>
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

<!-- Visionneuse plein écran de la galerie -->
<div class="lightbox" id="lightbox" hidden role="dialog" aria-modal="true" aria-label="Visionneuse de photos">
    <div class="lightbox-top">
        <span class="lightbox-compteur" id="lb-compteur"></span>
        <button type="button" class="lightbox-btn lightbox-fermer" id="lb-fermer" aria-label="Fermer">✕</button>
    </div>
    <button type="button" class="lightbox-btn lightbox-nav lightbox-prev" id="lb-prev" aria-label="Photo précédente">‹</button>
    <figure class="lightbox-figure">
        <img id="lb-img" src="" alt="">
        <figcaption>
            <span class="lightbox-cat" id="lb-cat"></span>
            <span class="lightbox-titre" id="lb-titre"></span>
        </figcaption>
    </figure>
    <button type="button" class="lightbox-btn lightbox-nav lightbox-next" id="lb-next" aria-label="Photo suivante">›</button>
    <div class="lightbox-miniatures" id="lb-miniatures"></div>
</div>

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

// Lien direct vers l'onglet galerie (actualites.php#galerie)
if (location.hash === '#galerie') {
    document.querySelector('.tab-btn[data-tab="galerie"]')?.click();
}

// ── Visionneuse plein écran ─────────────────────────────────────────────────
(function() {
    const lb        = document.getElementById('lightbox');
    const img       = document.getElementById('lb-img');
    const titre     = document.getElementById('lb-titre');
    const cat       = document.getElementById('lb-cat');
    const compteur  = document.getElementById('lb-compteur');
    const miniatures= document.getElementById('lb-miniatures');
    let photos = [];   // photos visibles (respecte le filtre de catégorie)
    let index  = 0;

    function afficher(i) {
        index = (i + photos.length) % photos.length;   // boucle au début / à la fin
        const photo = photos[index].querySelector('img');
        img.classList.remove('lightbox-anim');
        void img.offsetWidth;                          // relance l'animation
        img.classList.add('lightbox-anim');
        img.src = photos[index].dataset.full;   // grande photo (la grille n'affiche que la vignette)
        img.alt = photo.alt;
        titre.textContent = photo.alt;
        cat.textContent = photos[index].dataset.cat;
        compteur.textContent = (index + 1) + ' / ' + photos.length;
        miniatures.querySelectorAll('img').forEach((m, j) => m.classList.toggle('active', j === index));
        miniatures.children[index]?.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
        // Précharge les voisines pour une navigation instantanée
        [index - 1, index + 1].forEach(j => { const p = photos[(j + photos.length) % photos.length]; if (p) new Image().src = p.dataset.full; });
    }

    function ouvrir(item) {
        photos = [...document.querySelectorAll('.galerie-grid .galerie-item:not([hidden])')];
        miniatures.innerHTML = '';
        photos.forEach((p, j) => {
            const m = document.createElement('img');
            m.src = p.querySelector('img').src;
            m.alt = '';
            m.addEventListener('click', () => afficher(j));
            miniatures.appendChild(m);
        });
        lb.classList.toggle('lightbox-seule', photos.length < 2);
        lb.hidden = false;
        document.body.style.overflow = 'hidden';
        afficher(photos.indexOf(item));
        document.getElementById('lb-fermer').focus();
    }

    function fermer() {
        lb.hidden = true;
        document.body.style.overflow = '';
        photos[index]?.focus();
    }

    document.querySelectorAll('.galerie-grid .galerie-item').forEach(item => {
        item.addEventListener('click', () => ouvrir(item));
        item.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); ouvrir(item); } });
    });
    document.getElementById('lb-prev').addEventListener('click', () => afficher(index - 1));
    document.getElementById('lb-next').addEventListener('click', () => afficher(index + 1));
    document.getElementById('lb-fermer').addEventListener('click', fermer);
    lb.addEventListener('click', e => { if (e.target === lb || e.target.classList.contains('lightbox-figure')) fermer(); });

    document.addEventListener('keydown', e => {
        if (lb.hidden) return;
        if (e.key === 'Escape')     fermer();
        if (e.key === 'ArrowLeft')  afficher(index - 1);
        if (e.key === 'ArrowRight') afficher(index + 1);
    });

    // Glisser le doigt sur téléphone
    let departX = null;
    lb.addEventListener('touchstart', e => { departX = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', e => {
        if (departX === null) return;
        const dx = e.changedTouches[0].clientX - departX;
        if (Math.abs(dx) > 50) afficher(index + (dx < 0 ? 1 : -1));
        departX = null;
    });
})();
</script>
</body>
</html>
