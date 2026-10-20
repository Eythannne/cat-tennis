<?php
require_once '../src/config/database.php';
$articles = $pdo->query("SELECT * FROM articles WHERE publie=1 ORDER BY created_at DESC")->fetchAll();

$pageTitle = "Actualités — CAT";
$pageDesc = "Toutes les actualités et la galerie photos du Club Aulnaysien de Tennis.";
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

<!-- Onglets -->
<section class="section-actu">
    <div class="container">
        <div class="tabs">
            <button class="tab-btn active" data-tab="articles">📰 Articles</button>
            <button class="tab-btn" data-tab="galerie">📷 Galerie photos</button>
        </div>

        <!-- Onglet Articles -->
        <div class="tab-content active" id="tab-articles">
            <div class="news-grid">
                <?php if (empty($articles)): ?>
                    <p style="color:#999;grid-column:1/-1">Aucun article publié pour l'instant.</p>
                <?php else: ?>
                    <?php foreach ($articles as $a): ?>
                    <article class="news-card">
                        <div class="news-img" style="background:#D6EAF8"></div>
                        <div class="news-body">
                            <span class="news-date"><?= date('d F Y', strtotime($a['created_at'])) ?></span>
                            <h3><?= htmlspecialchars($a['titre']) ?></h3>
                            <p><?= htmlspecialchars(mb_substr($a['contenu'], 0, 120)) ?>...</p>
                            <a href="#" class="news-link">Lire la suite →</a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Onglet Galerie -->
        <div class="tab-content" id="tab-galerie">
            <div class="galerie-grid">
                <div class="galerie-item">
                    <div class="galerie-img" style="background:#D6EAF8"><span>📸</span></div>
                    <p>Tournoi interne 2026</p>
                </div>
                <div class="galerie-item">
                    <div class="galerie-img" style="background:#D5F5E3"><span>📸</span></div>
                    <p>Cours enfants</p>
                </div>
                <div class="galerie-item">
                    <div class="galerie-img" style="background:#FDEBD0"><span>📸</span></div>
                    <p>Portes ouvertes</p>
                </div>
                <div class="galerie-item">
                    <div class="galerie-img" style="background:#E8DAEF"><span>📸</span></div>
                    <p>Championnat par équipes</p>
                </div>
                <div class="galerie-item">
                    <div class="galerie-img" style="background:#FADBD8"><span>📸</span></div>
                    <p>Stage vacances</p>
                </div>
                <div class="galerie-item">
                    <div class="galerie-img" style="background:#D6EAF8"><span>📸</span></div>
                    <p>Animations jeunes</p>
                </div>
                <div class="galerie-item">
                    <div class="galerie-img" style="background:#D5F5E3"><span>📸</span></div>
                    <p>Soirée club</p>
                </div>
                <div class="galerie-item">
                    <div class="galerie-img" style="background:#FDEBD0"><span>📸</span></div>
                    <p>Terrains couverts</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../templates/footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>