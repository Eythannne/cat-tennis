<?php
// Copier ce fichier en database.php et renseigner les identifiants.
// database.php est ignoré par git : ne jamais le versionner.

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
    define('DB_HOST', 'sqlXXX.infinityfree.com');
    define('DB_NAME', 'if0_XXXXXXXX_cat_tennis');
    define('DB_USER', 'if0_XXXXXXXX');
    define('DB_PASS', 'CHANGER_MOI');
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Erreur de connexion BDD : ' . $e->getMessage());
    http_response_code(500);
    die('Le site est momentanément indisponible. Merci de réessayer plus tard.');
}
