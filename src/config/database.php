<?php
// ── Base URL (détectée automatiquement) ──────────────────────────────────────
define('BASE_URL', strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false
    ? '/cat-tennis/public'
    : '');

// ── Credentials DB (auto-switch local / prod) ────────────────────────────────
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'cat_tennis');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_HOST', 'sql210.infinityfree.com');
    define('DB_NAME', 'if0_43277584_cat_tennis');
    define('DB_USER', 'if0_43277584');
    define('DB_PASS', 'HQzOn6rvaaahdLx');
}

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