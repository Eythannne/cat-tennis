<?php
// <head> commun des pages publiques.
// Variables attendues : $pageTitle, $pageDesc (optionnel), $extraHead (optionnel, HTML brut)
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($pageDesc)): ?>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <?php endif; ?>
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/favicon.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <?= $extraHead ?? '' ?>
</head>
