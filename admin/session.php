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
define('UPLOAD_ARTICLES',    __DIR__ . '/../public/assets/uploads/articles/');
define('UPLOAD_ENTRAINEURS', __DIR__ . '/../public/assets/uploads/entraineurs/');
define('UPLOAD_DOCUMENTS',   __DIR__ . '/../public/assets/uploads/documents/');
define('PDF_MAX_SIZE', 10 * 1024 * 1024); // 10 Mo
const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

// Renvoie l'extension correspondant au vrai type de l'image, ou null si invalide
function image_extension($data) {
    if (strlen($data) === 0 || strlen($data) > IMAGE_MAX_SIZE) return null;
    $info = @getimagesizefromstring($data);
    return $info ? (IMAGE_TYPES[$info['mime']] ?? null) : null;
}

// Enregistre une image recadrée (base64 envoyé par admin/crop.js) dans $dir.
// Renvoie le nouveau nom de fichier (et supprime $ancienne), ou $ancienne si rien de valide n'a été envoyé.
function save_cropped_image($base64, $dir, $prefix, $ancienne = null) {
    if (empty($base64) || !preg_match('/^data:image\/(jpeg|png|webp);base64,(.+)$/s', $base64, $m)) {
        return $ancienne;
    }
    $data = base64_decode($m[2], true);
    $ext  = $data ? image_extension($data) : null;
    if (!$ext) return $ancienne;

    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = uniqid($prefix) . '.' . $ext;
    if (!file_put_contents($dir . $filename, $data)) return $ancienne;

    if ($ancienne) delete_upload($dir, $ancienne);
    return $filename;
}

// Supprime un fichier uploadé (basename() empêche de sortir du dossier)
function delete_upload($dir, $fichier) {
    $chemin = $dir . basename($fichier);
    if ($fichier && is_file($chemin)) unlink($chemin);
}
