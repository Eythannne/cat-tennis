<?php
// Gabarit commun du back-office : <head>, sidebar et fermeture de page.

function admin_header($titre, $page, $head = '') {
    global $pdo;
    $nonLus = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu=0")->fetchColumn();
    $liens = [
        'index'      => '📊 Dashboard',
        'articles'   => '📰 Articles',
        'messages'   => '✉️ Messages',
        'evenements' => '📅 Événements',
        'galerie'    => '📷 Galerie',
        'entraineurs'=> '🎾 Entraîneurs',
        'parametres' => '⚙️ Paramètres',
    ];
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre) ?> — Admin CAT</title>
    <link rel="icon" type="image/png" href="../public/favicon.png">
    <link rel="stylesheet" href="admin.css">
    <?= $head ?>
</head>
<body>
<div class="sidebar">
    <a href="index.php" class="sidebar-logo"><img src="../public/favicon.png" alt="">CAT</a>
    <nav>
        <?php foreach ($liens as $fichier => $label): ?>
        <a href="<?= $fichier ?>.php" <?= $page === $fichier ? 'class="active"' : '' ?>>
            <?= $label ?><?php if ($fichier === 'messages' && $nonLus > 0): ?> <span class="sidebar-badge"><?= $nonLus ?></span><?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/index.php" target="_blank">🌐 Voir le site</a>
        <a href="logout.php">🚪 Déconnexion</a>
    </div>
</div>
<div class="main">
<?php
}

// ── Recadrage d'image (voir crop.js) ─────────────────────────────────────────
// À mettre dans le <head> de la page
const CROP_HEAD = '<link rel="stylesheet" href="../public/assets/css/cropper.min.css">';
// À passer à admin_footer()
const CROP_SCRIPTS = '<script src="../public/assets/js/cropper.min.js"></script><script src="crop.js"></script>';

// Champ "choisir une image" + aperçu, à placer dans le formulaire
function crop_field($ratio, $width, $height) {
    ?>
    <input type="hidden" name="image_cropped" id="image_cropped">
    <input type="file" id="image_picker" accept="image/jpeg,image/png,image/webp" style="font-size:0.9rem;max-width:100%"
           data-ratio="<?= $ratio ?>" data-width="<?= $width ?>" data-height="<?= $height ?>">
    <div id="crop_preview_wrap" hidden>
        <img id="crop_preview_thumb" class="preview-img" style="max-height:80px" alt="">
        <span style="font-size:0.8rem;color:var(--vert);">✅ Image recadrée prête</span>
        <button type="button" id="crop_redo" class="link-btn">Recadrer à nouveau</button>
    </div>
    <?php
}

// Fenêtre de recadrage, à placer une fois en bas de la page (hors du formulaire)
function crop_modal() {
    ?>
<div class="crop-overlay" id="crop_overlay">
    <div class="crop-modal">
        <h3>✂️ Recadrer l'image</h3>
        <p class="crop-hint">Déplacez et zoomez pour choisir la zone à afficher (la forme du cadre est fixe).</p>
        <div class="crop-container">
            <img id="crop_img" src="" alt="Crop">
        </div>
        <div class="crop-actions">
            <button type="button" class="btn btn-secondary" id="crop_cancel">Annuler</button>
            <button type="button" class="btn btn-primary" id="crop_confirm">✅ Valider le recadrage</button>
        </div>
    </div>
</div>
    <?php
}

// Badge de statut d'un article : brouillon, programmé ou publié
function article_badge($a) {
    if (!$a['publie'])                                  return '<span class="badge badge-gray">Brouillon</span>';
    if ($a['published_at'] > date('Y-m-d H:i:s'))       return '<span class="badge badge-yellow">Programmé ' . date('d/m H:i', strtotime($a['published_at'])) . '</span>';
    return '<span class="badge badge-green">Publié</span>';
}

// Libellé lisible du sujet d'un message de contact
function sujet_label($sujet) {
    return SUJETS_CONTACT[$sujet] ?? ($sujet ?: '(sans sujet)');
}

function admin_footer($scripts = '') {
    ?>
</div>
<?= $scripts ?>
</body>
</html>
<?php
}
