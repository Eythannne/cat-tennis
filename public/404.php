<?php
require_once '../src/config/database.php';
$pageTitle = "Page introuvable — CAT";
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php
$activePage = 'actualites';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="section-404">
    <div class="container">
        <div class="not-found">
            <span class="not-found-icon">🎾</span>
            <h1>404</h1>
            <h2>Cette page est hors-jeu</h2>
            <p>La page que vous cherchez n'existe pas ou a été déplacée.</p>
            <a href="<?= BASE_URL ?>/index.php" class="btn-primary">Retour à l'accueil</a>
        </div>
    </div>
</section>

<script src="assets/js/main.js"></script>
</body>
</html>
