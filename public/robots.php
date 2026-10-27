<?php
// Servi à l'adresse /robots.txt (voir .htaccess) : indique aux moteurs de recherche quoi explorer.
require_once '../src/init.php';
header('Content-Type: text/plain; charset=utf-8');

$admin = preg_replace('#/public$#', '', BASE_URL) . '/admin/';
?>
User-agent: *
Disallow: <?= $admin ?>

Disallow: <?= BASE_URL ?>/api/

Sitemap: <?= url_site('sitemap.xml') ?>

