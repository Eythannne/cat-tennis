<?php
require_once '../src/init.php';
$pageTitle = "Mentions légales — CAT";
$pageDesc = "Mentions légales et politique de confidentialité du Club Aulnaysien de Tennis.";
?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
<body>

<?php
$activePage = 'mentions-legales';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Mentions légales</h1>
        <p>Informations légales et politique de confidentialité</p>
    </div>
</section>

<section class="section-legal">
    <div class="container">
        <div class="legal-content">

            <h2>Éditeur du site</h2>
            <p><strong><?= htmlspecialchars(param('nom_club', 'Club Aulnaysien de Tennis')) ?> (CAT)</strong><br>
            Association loi 1901<br>
            <?= htmlspecialchars(param('adresse', ADRESSE_DEFAUT)) ?><br>
            <?php if (param('telephone')): ?>Téléphone : <?= htmlspecialchars(param('telephone')) ?><br><?php endif; ?>
            Email : <?= htmlspecialchars(param('email_contact', 'contact@cat-tennis.fr')) ?></p>

            <h2>Hébergement</h2>
            <p>Ce site est hébergé par :<br>
            <strong>InfinityFree</strong><br>
            <a href="https://www.infinityfree.com" target="_blank" rel="noopener">www.infinityfree.com</a></p>

            <h2>Responsable de publication</h2>
            <p>Le responsable de la publication est le président du Club Aulnaysien de Tennis.</p>

            <h2>Propriété intellectuelle</h2>
            <p>L'ensemble du contenu de ce site (textes, images, logos) est la propriété du Club Aulnaysien de Tennis ou de ses partenaires. Toute reproduction, même partielle, est interdite sans autorisation préalable.</p>

            <h2>Données personnelles (RGPD)</h2>
            <p>Les données collectées via le formulaire de contact (nom, email, message) sont utilisées uniquement pour répondre à votre demande. Elles ne sont ni vendues, ni transmises à des tiers.</p>
            <p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un droit d'accès, de rectification et de suppression de vos données. Pour exercer ce droit, contactez-nous à <strong><?= htmlspecialchars(param('email_contact', 'contact@cat-tennis.fr')) ?></strong>.</p>

            <h2>Cookies</h2>
            <p>Ce site n'utilise pas de cookies de traçage ou publicitaires. Seuls des cookies techniques nécessaires au bon fonctionnement du site peuvent être déposés.</p>

            <h2>Limitation de responsabilité</h2>
            <p>Le CAT s'efforce de maintenir les informations de ce site à jour. Cependant, il ne peut être tenu responsable des erreurs ou omissions, ni des dommages résultant de l'utilisation des informations publiées.</p>

        </div>
    </div>
</section>

<?php include '../templates/footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>
