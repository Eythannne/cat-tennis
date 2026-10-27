<?php
require_once '../src/init.php';
$pageTitle = "Le club — CAT";
$pageDesc = "Découvrez le Club Aulnaysien de Tennis : son histoire, ses terrains, ses entraîneurs et ses tarifs.";
$entraineurs = $pdo->query("SELECT * FROM entraineurs ORDER BY ordre, id")->fetchAll();
$tarifs      = $pdo->query("SELECT * FROM tarifs ORDER BY ordre, id")->fetchAll();
$fiche       = param('fiche_inscription');
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

<!-- Tarifs (gérés dans l'admin) -->
<?php if ($tarifs): ?>
<section class="section-tarifs" id="tarifs">
    <div class="container">
        <div class="section-center">
            <span class="section-tag">Adhésion</span>
            <h2>Tarifs<?= param('saison') ? ' saison ' . htmlspecialchars(param('saison')) : '' ?></h2>
        </div>
        <div class="tarifs-grid">
            <?php foreach ($tarifs as $t): ?>
            <div class="tarif-card <?= $t['mis_en_avant'] ? 'tarif-featured' : '' ?>">
                <div class="tarif-header">
                    <h3><?= htmlspecialchars($t['nom']) ?></h3>
                    <?php if ($t['public_cible']): ?><span class="tarif-age"><?= htmlspecialchars($t['public_cible']) ?></span><?php endif; ?>
                </div>
                <div class="tarif-price"><?= htmlspecialchars($t['prix']) ?><span><?= htmlspecialchars($t['periode'] ?? '') ?></span></div>
                <ul class="tarif-features">
                    <?php foreach (array_filter(array_map('trim', explode("\n", $t['avantages'] ?? ''))) as $avantage): ?>
                    <li>✓ <?= htmlspecialchars($avantage) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($fiche): ?>
                <button type="button" class="tarif-btn" data-ouvrir-fiche>S'inscrire</button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($fiche): ?>
        <div class="tarifs-cta">
            <button type="button" class="btn-primary" data-ouvrir-fiche>📄 Voir la fiche d'inscription</button>
        </div>
        <?php endif; ?>
        <p class="tarifs-note">Renseignements au secrétariat du club<?= param('telephone') ? ' : ' . htmlspecialchars(param('telephone')) : '' ?>.</p>
    </div>
</section>
<?php endif; ?>

<?php if ($fiche): $urlFiche = 'assets/uploads/documents/' . rawurlencode($fiche); ?>
<!-- Pop-up fiche d'inscription -->
<div class="fiche-modal" id="fiche-modal" hidden role="dialog" aria-modal="true" aria-labelledby="fiche-titre">
    <div class="fiche-modal-box">
        <div class="fiche-modal-header">
            <h3 id="fiche-titre">📄 Fiche d'inscription<?= param('saison') ? ' — saison ' . htmlspecialchars(param('saison')) : '' ?></h3>
            <button type="button" class="event-modal-close" id="fiche-fermer" aria-label="Fermer">✕</button>
        </div>
        <p class="fiche-modal-aide">Imprimez la fiche, remplissez-la et rapportez-la au secrétariat du club avec votre règlement.</p>
        <object class="fiche-modal-pdf" data="<?= $urlFiche ?>" type="application/pdf">
            <p class="fiche-modal-fallback">L'aperçu du PDF n'est pas disponible sur cet appareil. Utilisez les boutons ci-dessous pour l'ouvrir ou le télécharger.</p>
        </object>
        <p class="fiche-modal-mobile">📱 Sur téléphone, ouvrez la fiche en plein écran ou téléchargez-la pour l'imprimer.</p>
        <div class="fiche-modal-actions">
            <a href="<?= $urlFiche ?>" download="fiche-inscription-CAT.pdf" class="btn-primary">⬇️ Télécharger</a>
            <a href="<?= $urlFiche ?>" target="_blank" rel="noopener" class="btn-secondary">↗ Ouvrir en plein écran</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Footer -->
<?php include '../templates/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
// Pop-up de la fiche d'inscription
(function() {
    const modal = document.getElementById('fiche-modal');
    if (!modal) return;
    const ouvrir = () => { modal.hidden = false; document.body.style.overflow = 'hidden'; document.getElementById('fiche-fermer').focus(); };
    const fermer = () => { modal.hidden = true; document.body.style.overflow = ''; };
    document.querySelectorAll('[data-ouvrir-fiche]').forEach(btn => btn.addEventListener('click', ouvrir));
    document.getElementById('fiche-fermer').addEventListener('click', fermer);
    modal.addEventListener('click', e => { if (e.target === modal) fermer(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.hidden) fermer(); });
    // Lien direct : club.php#inscription ouvre la fiche
    if (location.hash === '#inscription') ouvrir();
})();
</script>
</body>
</html>
