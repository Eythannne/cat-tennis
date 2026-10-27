<?php
require_once '../src/init.php';
$pageTitle = "Le club — CAT";
$pageDesc = "Découvrez le Club Aulnaysien de Tennis : son histoire, ses terrains, ses entraîneurs et ses tarifs.";
$entraineurs = $pdo->query("SELECT * FROM entraineurs ORDER BY ordre, id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
<body>

<?php
$activePage = 'club';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<!-- Hero page intérieure -->
<section class="page-hero page-hero--club">
    <div class="container">
        <h1>Le club</h1>
        <p>Notre histoire, nos terrains, nos entraîneurs</p>
    </div>
</section>

<!-- Histoire -->
<section class="section-about">
    <div class="container">
        <div class="about-grid">
            <div class="about-text">
                <span class="section-tag">Notre histoire</span>
                <h2>Un club ancré dans Aulnay-sous-Bois</h2>
                <p>Fondé en 1975, le Club Aulnaysien de Tennis est l'un des clubs de référence de Seine-Saint-Denis. Depuis plus de 50 ans, il accueille des joueurs de tous âges et de tous niveaux, dans un esprit convivial et de progression.</p>
                <p>Affilié à la Fédération Française de Tennis, le CAT participe aux compétitions par équipes et propose tout au long de l'année des animations, tournois internes et stages pour les jeunes comme pour les adultes.</p>
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
                    <span class="stat-number"><?= count($entraineurs) ?></span>
                    <span class="stat-label">Entraîneurs</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Terrains -->
<section class="section-courts">
    <div class="container">
        <div class="section-center">
            <span class="section-tag">Installations</span>
            <h2>Nos terrains</h2>
        </div>
        <div class="courts-grid">
            <div class="court-card">
                <div class="court-icon">🏟️</div>
                <h3>Courts couverts</h3>
                <p>6 courts en dur couverts, disponibles toute l'année quelle que soit la météo.</p>
            </div>
            <div class="court-card">
                <div class="court-icon">☀️</div>
                <h3>Courts extérieurs</h3>
                <p>Courts en accès libre selon disponibilité. Réservation via Ten'up recommandée.</p>
            </div>
            <div class="court-card">
                <div class="court-icon">🏃</div>
                <h3>Espace échauffement</h3>
                <p>Zone dédiée à l'échauffement et aux exercices spécifiques tennis.</p>
            </div>
        </div>
    </div>
</section>

<!-- Entraîneurs (gérés dans l'admin) -->
<?php if ($entraineurs): ?>
<section class="section-coaches">
    <div class="container">
        <div class="section-center">
            <span class="section-tag">L'équipe</span>
            <h2>Nos entraîneurs</h2>
        </div>
        <div class="coaches-grid">
            <?php foreach ($entraineurs as $e): ?>
            <div class="coach-card">
                <div class="coach-avatar">
                    <?php if ($e['photo']): ?>
                        <img src="assets/uploads/entraineurs/<?= htmlspecialchars($e['photo']) ?>" alt="<?= htmlspecialchars($e['prenom']) ?>" loading="lazy">
                    <?php else: ?>
                        <?= htmlspecialchars(initiales($e)) ?>
                    <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars(trim($e['prenom'] . ' ' . $e['nom'])) ?></h3>
                <?php if ($e['role']): ?><span class="coach-role"><?= htmlspecialchars($e['role']) ?></span><?php endif; ?>
                <?php if ($e['description']): ?><p><?= nl2br(htmlspecialchars($e['description'])) ?></p><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Tarifs -->
<section class="section-tarifs">
    <div class="container">
        <div class="section-center">
            <span class="section-tag">Adhésion</span>
            <h2>Tarifs saison 2026-2027</h2>
        </div>
        <div class="tarifs-grid">
            <div class="tarif-card">
                <div class="tarif-header">
                    <h3>Enfant</h3>
                    <span class="tarif-age">Moins de 18 ans</span>
                </div>
                <div class="tarif-price">À définir<span>/an</span></div>
                <ul class="tarif-features">
                    <li>✓ Cotisation FFT incluse</li>
                    <li>✓ Accès aux cours collectifs</li>
                    <li>✓ Tournois internes</li>
                    <li>✓ Animations vacances</li>
                </ul>
            </div>
            <div class="tarif-card tarif-featured">
                <div class="tarif-header">
                    <h3>Adulte</h3>
                    <span class="tarif-age">18 ans et plus</span>
                </div>
                <div class="tarif-price">À définir<span>/an</span></div>
                <ul class="tarif-features">
                    <li>✓ Cotisation FFT incluse</li>
                    <li>✓ Accès aux cours collectifs</li>
                    <li>✓ Tournois internes</li>
                    <li>✓ Compétitions par équipes</li>
                </ul>
            </div>
            <div class="tarif-card">
                <div class="tarif-header">
                    <h3>Famille</h3>
                    <span class="tarif-age">2 adultes + enfants</span>
                </div>
                <div class="tarif-price">À définir<span>/an</span></div>
                <ul class="tarif-features">
                    <li>✓ Cotisations FFT incluses</li>
                    <li>✓ Accès cours collectifs</li>
                    <li>✓ Tournois internes</li>
                    <li>✓ Tarif préférentiel</li>
                </ul>
            </div>
        </div>
        <p class="tarifs-note">Les tarifs exacts seront renseignés depuis le back-office. Renseignements au secrétariat du club.</p>
    </div>
</section>

<!-- Footer -->
<?php include '../templates/footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>
