<?php
// <head> commun des pages publiques.
// Variables attendues : $pageTitle, $pageDesc (optionnel), $extraHead (optionnel, HTML brut)
// Partage sur les réseaux : $pageImage (chemin depuis public/, optionnel), $pageType ('website' ou 'article')
$ogImage = url_site($pageImage ?? 'assets/images/fond-web-cat.jpg');
$ogUrl   = url_site(basename($_SERVER['SCRIPT_NAME']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($pageDesc)): ?>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <?php endif; ?>
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="theme-color" content="#124A20">

    <!-- Aperçu lors d'un partage (WhatsApp, Facebook, Messenger, X...) -->
    <meta property="og:site_name" content="<?= htmlspecialchars(param('nom_club', 'Club Aulnaysien de Tennis')) ?>">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:type" content="<?= $pageType ?? 'website' ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <?php if (!empty($pageDesc)): ?>
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?= htmlspecialchars($ogUrl) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/favicon.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <?= $extraHead ?? '' ?>
</head>
