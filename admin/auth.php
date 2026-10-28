<?php
require_once __DIR__ . '/session.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../src/init.php';
require_once __DIR__ . '/_layout.php';

// ── Déconnexion automatique ──────────────────────────────────────────────────
// Sortie : quand on quitte l'admin (onglet fermé, autre site...), la page prévient le serveur
// (quitter.php). Si aucune page admin n'est rechargée dans le délai, la session est coupée.
// Inactivité : filet de sécurité si le signal de sortie n'a pas pu partir (téléphone en veille...).
const ADMIN_DELAI_SORTIE     = 15;        // secondes
const ADMIN_DELAI_INACTIVITE = 30 * 60;   // secondes

$raison = null;
if (isset($_SESSION['quitte_a']) && time() - $_SESSION['quitte_a'] > ADMIN_DELAI_SORTIE) {
    $raison = 'sortie';
} elseif (time() - ($_SESSION['derniere_activite'] ?? time()) > ADMIN_DELAI_INACTIVITE) {
    $raison = 'inactivite';
}
if ($raison) {
    journaliser('connexion', $raison === 'sortie'
        ? 'Déconnexion automatique (sortie de l\'admin)'
        : 'Déconnexion automatique (30 minutes d\'inactivité)');
    $_SESSION = [];
    session_destroy();
    header('Location: login.php?raison=' . $raison);
    exit;
}
unset($_SESSION['quitte_a']);           // une page admin a été chargée : on est toujours là
$_SESSION['derniere_activite'] = time();

csrf_check();

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
