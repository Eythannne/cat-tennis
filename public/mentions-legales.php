<?php
require_once '../src/init.php';
$pageTitle = "Mentions légales — CAT";
$pageDesc = "Mentions légales et politique de confidentialité du Club Aulnaysien de Tennis.";
$nomClub   = param('nom_club', 'Club Aulnaysien de Tennis');
$emailClub = param('email_contact', 'contact@cat-tennis.fr');
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

<section class="page-hero page-hero--mentions">
    <div class="container">
        <h1>Mentions légales</h1>
        <p>Informations légales et politique de confidentialité</p>
    </div>
</section>

<section class="section-legal">
    <div class="container">
        <div class="legal-content">

            <h2>Éditeur du site</h2>
            <p><strong><?= htmlspecialchars($nomClub) ?> (CAT)</strong><br>
            Association régie par la loi du 1<sup>er</sup> juillet 1901<br>
            <?php if (param('rna')): ?>Numéro RNA : <?= htmlspecialchars(param('rna')) ?><br><?php endif; ?>
            <?php if (param('siret')): ?>SIRET : <?= htmlspecialchars(param('siret')) ?><br><?php endif; ?>
            Siège : <?= htmlspecialchars(param('adresse', ADRESSE_DEFAUT)) ?><br>
            <?php if (param('telephone')): ?>Téléphone : <?= htmlspecialchars(param('telephone')) ?><br><?php endif; ?>
            Email : <?= htmlspecialchars($emailClub) ?></p>

            <h2>Responsable de la publication</h2>
            <p><?= param('responsable_publication')
                ? htmlspecialchars(param('responsable_publication'))
                : 'Le président ou la présidente du ' . htmlspecialchars($nomClub) . '.' ?></p>

            <h2>Hébergement</h2>
            <p>Ce site est hébergé par <strong><?= HEBERGEUR['nom'] ?></strong><br>
            <?= HEBERGEUR['adresse'] ?><br>
            <a href="<?= HEBERGEUR['site'] ?>" target="_blank" rel="noopener"><?= preg_replace('#^https?://#', '', HEBERGEUR['site']) ?></a><br>
            Les serveurs sont exploités par <?= HEBERGEUR['serveurs'] ?>.</p>

            <h2>Propriété intellectuelle</h2>
            <p>L'ensemble du contenu de ce site (textes, photos, logo) appartient au <?= htmlspecialchars($nomClub) ?> ou à ses partenaires. Toute reproduction, même partielle, est interdite sans autorisation préalable.</p>
            <p>Les personnes photographiées lors des activités du club peuvent demander le retrait d'une photo à tout moment en écrivant à <?= htmlspecialchars($emailClub) ?>.</p>

            <h2 id="donnees">Données personnelles (RGPD)</h2>
            <p><strong>Responsable du traitement :</strong> le <?= htmlspecialchars($nomClub) ?>, joignable à <?= htmlspecialchars($emailClub) ?>.</p>
            <p><strong>Données collectées :</strong> uniquement celles que vous saisissez dans le formulaire de contact (nom, adresse email, sujet et message). Aucun compte visiteur n'est créé et aucune donnée n'est collectée à votre insu.</p>
            <p><strong>Finalité et base légale :</strong> ces données servent uniquement à répondre à votre demande (intérêt légitime du club à traiter les demandes qui lui sont adressées).</p>
            <p><strong>Destinataires :</strong> les seuls membres du bureau du club chargés de répondre. Elles ne sont ni vendues, ni cédées, ni transmises à des tiers.</p>
            <p><strong>Durée de conservation :</strong> <?= CONSERVATION_MESSAGES_ANS ?> ans à compter de l'envoi du message ; elles sont ensuite supprimées automatiquement.</p>
            <p><strong>Vos droits :</strong> vous pouvez demander l'accès, la rectification, l'effacement ou la limitation du traitement de vos données, et vous opposer à ce traitement, en écrivant à <strong><?= htmlspecialchars($emailClub) ?></strong>. Si vous estimez que vos droits ne sont pas respectés, vous pouvez adresser une réclamation à la CNIL (<a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>).</p>

            <h2>Cookies</h2>
            <p>Ce site ne dépose <strong>aucun cookie publicitaire ni de mesure d'audience</strong> : aucun bandeau de consentement n'est donc nécessaire. Seul un cookie technique de session est utilisé dans l'espace d'administration, réservé aux membres du bureau.</p>

            <h2>Services tiers</h2>
            <p>Pour afficher certains contenus, votre navigateur contacte directement des services extérieurs, qui reçoivent alors votre adresse IP (comme pour toute visite d'un site web) :</p>
            <ul>
                <li><strong>Carte</strong> (page Contact) : fonds de carte fournis par OpenStreetMap (<a href="https://osmfoundation.org/wiki/Privacy_Policy" target="_blank" rel="noopener">politique de confidentialité</a>).</li>
                <li><strong>Météo</strong> (page d'accueil) : prévisions fournies par Open-Meteo, service sans cookie ni compte (<a href="https://open-meteo.com/en/terms" target="_blank" rel="noopener">conditions</a>).</li>
            </ul>
            <p>La police d'écriture et toutes les autres ressources du site sont hébergées directement sur nos serveurs. Les liens vers Ten'up (réservation des courts) mènent au site de la Fédération Française de Tennis, qui applique sa propre politique de confidentialité.</p>

            <h2>Limitation de responsabilité</h2>
            <p>Le club s'efforce de maintenir les informations de ce site exactes et à jour, mais ne peut garantir l'absence d'erreurs ou d'omissions, ni être tenu responsable des dommages résultant de l'utilisation des informations publiées.</p>

            <p class="legal-maj">Dernière mise à jour : <?= jour_mois(filemtime(__FILE__)) . ' ' . date('Y', filemtime(__FILE__)) ?></p>

        </div>
    </div>
</section>

<?php include '../templates/footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>
