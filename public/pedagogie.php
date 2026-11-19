<?php
// Page Pédagogie (entraîneurs) : onglets Animations, Calendrier et Exercices.
// Lien discret dans le pied de page. Accès ouvert pour l'instant (voir src/pedagogie.php).
require_once '../src/init.php';
require_once '../src/pedagogie.php';

$onglet  = isset(PEDA_ONGLETS[$_GET['onglet'] ?? '']) ? $_GET['onglet'] : 'animations';
$section = isset(PEDA_SECTIONS_EXERCICES[$_GET['section'] ?? '']) ? $_GET['section'] : 'bibliotheque';

// Adresse de la page dans l'onglet (et la sous-section) en cours
function peda_url($parametres = []) {
    global $onglet, $section;
    $base = ['onglet' => $onglet] + ($onglet === 'exercices' ? ['section' => $section] : []);
    return 'pedagogie.php?' . http_build_query(array_merge($base, $parametres));
}

// Formulaires : enregistrement puis retour sur la page (sans renvoi du formulaire en cas d'actualisation)
$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = peda_traiter($erreurs);
    if ($code) {
        header('Location: ' . peda_url(['ok' => $code]));
        exit;
    }
}
$message = PEDA_MESSAGES[$_GET['ok'] ?? ''] ?? '';

// Valeur d'un champ : celle saisie (si le formulaire a été refusé), sinon celle de l'élément modifié
function peda_valeur($champ, $ligne, $defaut = '') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return $_POST[$champ] ?? $defaut;
    return $ligne[$champ] ?? $defaut;
}

$pageTitle = "Pédagogie — CAT";
$extraHead = '<meta name="robots" content="noindex, nofollow">'
           . '<link rel="stylesheet" href="' . ressource('css/pedagogie.css') . '">';
?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
<body>

<?php
$activePage = 'pedagogie';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="page-hero page-hero--pedagogie">
    <div class="container">
        <h1>🧑‍🏫 Espace pédagogique</h1>
        <p>Animations, calendrier et exercices des entraîneurs du club</p>
    </div>
</section>

<section class="section-peda">
    <div class="container">
        <nav class="peda-onglets" aria-label="Onglets de la page Pédagogie">
            <?php foreach (PEDA_ONGLETS as $cle => $libelle): ?>
            <a href="pedagogie.php?onglet=<?= $cle ?>" class="<?= $onglet === $cle ? 'actif' : '' ?>"><?= $libelle ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ($message): ?><div class="peda-alerte peda-alerte--ok" role="status">✅ <?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($erreurs): ?>
            <div class="peda-alerte peda-alerte--erreur" role="alert">
                ⚠️ <?= count($erreurs) > 1 ? 'Le formulaire n\'a pas été enregistré :' : '' ?>
                <?php if (count($erreurs) > 1): ?><ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                <?php else: ?><?= htmlspecialchars($erreurs[0]) ?><?php endif; ?>
            </div>
        <?php endif; ?>

        <?php include "../templates/pedagogie/$onglet.php"; ?>
    </div>
</section>

<?php include '../templates/footer.php'; ?>

<script src="<?= ressource('js/main.js') ?>"></script>
<?php if ($onglet === 'calendrier'): ?><script src="<?= ressource('js/fullcalendar.min.js') ?>"></script><?php endif; ?>
<script src="<?= ressource('js/pedagogie.js') ?>"></script>
</body>
</html>
