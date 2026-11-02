<?php
require_once '../src/init.php';
$pageTitle = "Le club — CAT";
$pageDesc = "Découvrez le Club Aulnaysien de Tennis : son histoire, ses terrains, ses entraîneurs, son équipe et ses tarifs.";
$entraineurs = $pdo->query("SELECT * FROM entraineurs ORDER BY ordre, id")->fetchAll();
$bureau      = $pdo->query("SELECT * FROM bureau ORDER BY ordre, id")->fetchAll();
$tarifs      = $pdo->query("SELECT * FROM tarifs ORDER BY ordre, id")->fetchAll();
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
        <h1><?= t('club_hero_titre') ?></h1>
        <p><?= t('club_hero_sous_titre') ?></p>
    </div>
</section>

<!-- Histoire -->
<section class="section-about">
    <div class="container">
        <div class="about-grid">
            <div class="about-text">
                <span class="section-tag"><?= t('club_histoire_etiquette') ?></span>
                <h2><?= t('club_histoire_titre') ?></h2>
                <?= format_article(param('presentation_club', presentation_club_defaut())) ?>
            </div>
            <?php include '../templates/stats.php'; ?>
        </div>
    </div>
</section>

<!-- Terrains -->
<section class="section-courts">
    <div class="container">
        <div class="section-center">
            <span class="section-tag"><?= t('club_terrains_etiquette') ?></span>
            <h2><?= t('club_terrains_titre') ?></h2>
        </div>
        <div class="courts-grid">
            <?php foreach ([1, 2, 3] as $n): ?>
            <div class="court-card">
                <div class="court-icon"><?= t("club_terrain{$n}_icone") ?></div>
                <h3><?= t("club_terrain{$n}_titre") ?></h3>
                <p><?= t("club_terrain{$n}_texte") ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Équipe administrative (gérée dans l'admin, page Bureau) -->
<?php if ($bureau || param('groupe_bureau_photo')): ?>
<section class="section-bureau" id="bureau">
    <div class="container">
        <div class="section-center">
            <span class="section-tag"><?= t('club_bureau_etiquette') ?></span>
            <h2><?= t('club_bureau_titre') ?></h2>
        </div>
        <?php $personnes = $bureau; $dossierPhotos = 'bureau'; include '../templates/cartes-personnes.php'; ?>
    </div>
</section>
<?php endif; ?>

<!-- Entraîneurs (gérés dans l'admin) -->
<?php if ($entraineurs || param('groupe_entraineurs_photo')): ?>
<section class="section-coaches">
    <div class="container">
        <div class="section-center">
            <span class="section-tag"><?= t('club_coachs_etiquette') ?></span>
            <h2><?= t('club_coachs_titre') ?></h2>
        </div>
        <?php $personnes = $entraineurs; $dossierPhotos = 'entraineurs'; include '../templates/cartes-personnes.php'; ?>
    </div>
</section>
<?php endif; ?>

<!-- Tarifs (gérés dans l'admin) -->
<?php if ($tarifs): ?>
<section class="section-tarifs" id="tarifs">
    <div class="container">
        <div class="section-center">
            <span class="section-tag"><?= t('club_tarifs_etiquette') ?></span>
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
                <?php if (!empty($t['pdf'])): ?>
                <button type="button" class="tarif-btn"
                        data-fiche="assets/uploads/documents/<?= rawurlencode($t['pdf']) ?>"
                        data-formule="<?= htmlspecialchars($t['nom']) ?>">📄 Voir la fiche d'inscription</button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="tarifs-note"><?= t('club_tarifs_note') ?><?= param('telephone') ? ' : ' . htmlspecialchars(param('telephone')) : '' ?>.</p>
    </div>
</section>
<?php endif; ?>

<?php if (array_filter(array_column($tarifs, 'pdf'))): ?>
<!-- Pop-up fiche d'inscription : le PDF affiché est celui de la formule cliquée -->
<div class="fiche-modal" id="fiche-modal" hidden role="dialog" aria-modal="true" aria-labelledby="fiche-titre">
    <div class="fiche-modal-box">
        <div class="fiche-modal-header">
            <h3 id="fiche-titre">📄 Fiche d'inscription</h3>
            <button type="button" class="event-modal-close" id="fiche-fermer" aria-label="Fermer">✕</button>
        </div>
        <p class="fiche-modal-aide">Imprimez la fiche, remplissez-la et rapportez-la au secrétariat du club avec votre règlement.</p>
        <div id="fiche-apercu"></div>
        <p class="fiche-modal-mobile">📱 Sur téléphone, ouvrez la fiche en plein écran ou téléchargez-la pour l'imprimer.</p>
        <div class="fiche-modal-actions">
            <a href="#" id="fiche-telecharger" download class="btn-primary">⬇️ Télécharger</a>
            <a href="#" id="fiche-ouvrir" target="_blank" rel="noopener" class="btn-secondary">↗ Ouvrir en plein écran</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Footer -->
<?php include '../templates/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
// Pop-up de la fiche d'inscription : chaque carte de tarif ouvre le PDF de sa formule
(function() {
    const modal = document.getElementById('fiche-modal');
    if (!modal) return;
    const apercu = document.getElementById('fiche-apercu');

    function ouvrir(btn) {
        const url = btn.dataset.fiche;
        const formule = btn.dataset.formule;
        document.getElementById('fiche-titre').textContent = '📄 Fiche d\'inscription — ' + formule;
        // Nouvel aperçu à chaque ouverture (un <object> ne recharge pas toujours un nouveau PDF)
        const objet = document.createElement('object');
        objet.className = 'fiche-modal-pdf';
        objet.type = 'application/pdf';
        objet.data = url;
        objet.innerHTML = '<p class="fiche-modal-fallback">L\'aperçu du PDF n\'est pas disponible sur cet appareil. Utilisez les boutons ci-dessous pour l\'ouvrir ou le télécharger.</p>';
        apercu.replaceChildren(objet);
        const telecharger = document.getElementById('fiche-telecharger');
        telecharger.href = url;
        telecharger.download = 'fiche-inscription-' + formule.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-') + '.pdf';
        document.getElementById('fiche-ouvrir').href = url;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        document.getElementById('fiche-fermer').focus();
    }
    function fermer() {
        modal.hidden = true;
        document.body.style.overflow = '';
        apercu.replaceChildren();
    }

    document.querySelectorAll('[data-fiche]').forEach(btn => btn.addEventListener('click', () => ouvrir(btn)));
    document.getElementById('fiche-fermer').addEventListener('click', fermer);
    modal.addEventListener('click', e => { if (e.target === modal) fermer(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.hidden) fermer(); });
})();
</script>
</body>
</html>
