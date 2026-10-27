<?php
// ── Session sécurisée ────────────────────────────────────────────────────────
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

// ── Protection CSRF ──────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Requête invalide (jeton CSRF). Rechargez la page et réessayez.');
    }
}

// ── Validation des images uploadées ──────────────────────────────────────────
define('IMAGE_MAX_SIZE', 5 * 1024 * 1024); // 5 Mo
const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

// Renvoie l'extension correspondant au vrai type de l'image, ou null si invalide
function image_extension($data) {
    if (strlen($data) === 0 || strlen($data) > IMAGE_MAX_SIZE) return null;
    $info = @getimagesizefromstring($data);
    return $info ? (IMAGE_TYPES[$info['mime']] ?? null) : null;
}
