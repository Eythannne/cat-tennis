<?php
// ── Base URL (détectée automatiquement) ──────────────────────────────────────
// En local Laragon : /cat-tennis/public  |  En prod (racine) : vide ''
define('BASE_URL', strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false
    ? '/cat-tennis/public'
    : '');

define('DB_HOST', 'localhost');
define('DB_NAME', 'cat_tennis');
define('DB_USER', 'root');
define('DB_PASS', '');
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}   