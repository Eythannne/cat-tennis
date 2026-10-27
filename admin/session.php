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
define('GALERIE_MAX_SIZE', 10 * 1024 * 1024); // 10 Mo : photos de téléphone, réduites à l'envoi
define('GALERIE_LARGEUR', 1600);   // photo affichée en plein écran
define('MINIATURE_LARGEUR', 600);  // vignette de la grille
const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

// Renvoie l'extension correspondant au vrai type de l'image, ou null si invalide
function image_extension($data, $tailleMax = IMAGE_MAX_SIZE) {
    if (strlen($data) === 0 || strlen($data) > $tailleMax) return null;
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

// Redresse (orientation EXIF des téléphones) et réduit une image à $largeurMax pixels de large.
// Écrit le résultat dans $destination (par défaut : remplace le fichier). Renvoie false si GD échoue.
function optimiser_image($source, $largeurMax, $destination = null, $qualite = 82) {
    $destination = $destination ?? $source;
    $info = @getimagesize($source);
    if (!$info || !function_exists('imagecreatetruecolor')) return false;

    $img = match ($info['mime']) {
        'image/jpeg' => @imagecreatefromjpeg($source),
        'image/png'  => @imagecreatefrompng($source),
        'image/webp' => @imagecreatefromwebp($source),
        default      => false,
    };
    if (!$img) return false;

    // Photo prise de côté au téléphone : on applique la rotation indiquée dans l'EXIF
    if ($info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
        $orientation = @exif_read_data($source)['Orientation'] ?? 1;
        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
        if ($angle) $img = imagerotate($img, $angle, 0);
    }

    $largeur = imagesx($img);
    $hauteur = imagesy($img);
    if ($largeur > $largeurMax) {
        $nouvelleHauteur = (int)round($hauteur * $largeurMax / $largeur);
        $reduite = imagecreatetruecolor($largeurMax, $nouvelleHauteur);
        imagealphablending($reduite, false);
        imagesavealpha($reduite, true);
        imagecopyresampled($reduite, $img, 0, 0, 0, 0, $largeurMax, $nouvelleHauteur, $largeur, $hauteur);
        $img = $reduite;
    }

    if (!is_dir(dirname($destination))) mkdir(dirname($destination), 0755, true);
    return match ($info['mime']) {
        'image/jpeg' => imagejpeg($img, $destination, $qualite),
        'image/png'  => imagepng($img, $destination, 6),
        'image/webp' => imagewebp($img, $destination, $qualite),
    };
}

// Supprime un fichier uploadé (basename() empêche de sortir du dossier)
function delete_upload($dir, $fichier) {
    $chemin = $dir . basename($fichier);
    if ($fichier && is_file($chemin)) unlink($chemin);
}
