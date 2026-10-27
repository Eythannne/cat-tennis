<?php
require_once __DIR__ . '/session.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

csrf_check();

require_once '../src/init.php';
require_once __DIR__ . '/_layout.php';

// Compte supprimé entre-temps (page Administrateurs) : la session n'est plus valable
$stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
if (!$stmt->fetchColumn()) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}
