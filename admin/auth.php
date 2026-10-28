<?php
require_once __DIR__ . '/session.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

csrf_check();

require_once '../src/init.php';
require_once __DIR__ . '/_layout.php';

// Rôle relu à chaque page : un changement de rôle ou une suppression s'applique immédiatement
$stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$role = $stmt->fetchColumn();
if ($role === false) {
    // Compte supprimé entre-temps (page Administrateurs) : la session n'est plus valable
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['admin_role'] = $role;

// RGPD : suppression automatique des messages de contact trop anciens
$pdo->prepare("DELETE FROM messages_contact WHERE created_at < ?")
    ->execute([date('Y-m-d H:i:s', strtotime('-' . CONSERVATION_MESSAGES_ANS . ' years'))]);
// Journal : on ne garde que les derniers mois (ménage l'hébergement, limite la conservation des IP)
$pdo->prepare("DELETE FROM journal WHERE created_at < ?")
    ->execute([date('Y-m-d H:i:s', strtotime('-' . JOURNAL_CONSERVATION_MOIS . ' months'))]);
