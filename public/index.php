<?php
$pageTitle = "Club Aulnaysien de Tennis";
$pageDesc = "Bienvenue sur le site officiel du Club Aulnaysien de Tennis à Aulnay-sous-Bois.";
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
            <a href="/cat-tennis/club.php" class="btn-primary">Nous rejoindre</a>
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
            <div class="about-stats">
                <div class="stat-card">
                    <span class="stat-number">6</span>
                    <span class="stat-label">Courts couverts</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">300+</span>
                    <span class="stat-label">Adhérents</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">1975</span>
                    <span class="stat-label">Année de création</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">5</span>
                    <span class="stat-label">Entraîneurs</span>
                </div>
            </div>
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
            <!-- Ces cartes seront dynamiques depuis la BDD, pour l'instant statiques -->
            <article class="news-card">
                <div class="news-img" style="background:#D6EAF8"></div>
                <div class="news-body">
                    <span class="news-date">15 octobre 2026</span>
                    <h3>Tournoi interne automne 2026</h3>
                    <p>Inscriptions ouvertes pour le tournoi interne de la saison automne. Tous niveaux bienvenus !</p>
                    <a href="#" class="news-link">Lire la suite →</a>
                </div>
            </article>
            <article class="news-card">
                <div class="news-img" style="background:#D5F5E3"></div>
                <div class="news-body">
                    <span class="news-date">8 octobre 2026</span>
                    <h3>Reprise des cours collectifs</h3>
                    <p>Les cours reprennent pour la nouvelle saison. Consultez les créneaux disponibles et inscrivez-vous.</p>
                    <a href="#" class="news-link">Lire la suite →</a>
                </div>
            </article>
            <article class="news-card">
                <div class="news-img" style="background:#FDEBD0"></div>
                <div class="news-body">
                    <span class="news-date">1 octobre 2026</span>
                    <h3>Portes ouvertes du club</h3>
                    <p>Venez découvrir le club, rencontrer les entraîneurs et essayer le tennis gratuitement.</p>
                    <a href="#" class="news-link">Lire la suite →</a>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- Section : Réserver un court -->
<section class="section-reservation">
    <div class="container">
        <div class="reservation-content">
            <div>
                <h2>Réservez votre court</h2>
                <p>La réservation des courts se fait via la plateforme Ten'up, le service officiel de la Fédération Française de Tennis.</p>
            </div>
            <a href="https://www.ten-up.com" target="_blank" rel="noopener" class="btn-primary">Réserver sur Ten'up</a>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include '../templates/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>