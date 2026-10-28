<?php
// Mon compte : accessible à tous les rôles (changement de son propre mot de passe)
require_once 'auth.php';

$pwMsg   = '';
$pwError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'password') {
    $actuel  = $_POST['mdp_actuel']  ?? '';
    $nouveau = $_POST['mdp_nouveau'] ?? '';
    $confirm = $_POST['mdp_confirm'] ?? '';

    // Récupère le hash actuel de la personne connectée
    $stmt = $pdo->prepare("SELECT id, mot_de_passe FROM admins WHERE id=?");
    $stmt->execute([$_SESSION['admin_id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($actuel, $row['mot_de_passe'])) {
        $pwMsg   = '❌ Mot de passe actuel incorrect.';
        $pwError = true;
    } elseif (strlen($nouveau) < 8) {
        $pwMsg   = '❌ Le nouveau mot de passe doit faire au moins 8 caractères.';
        $pwError = true;
    } elseif ($nouveau !== $confirm) {
        $pwMsg   = '❌ Les deux nouveaux mots de passe ne correspondent pas.';
        $pwError = true;
    } else {
        $hash = password_hash($nouveau, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE admins SET mot_de_passe=? WHERE id=?")->execute([$hash, $row['id']]);
        $pwMsg = '✅ Mot de passe mis à jour avec succès.';
        journaliser('compte', 'Changement de son propre mot de passe');
    }
}

$stmt = $pdo->prepare("SELECT nom, email, role, created_at FROM admins WHERE id=?");
$stmt->execute([$_SESSION['admin_id']]);
$moi = $stmt->fetch();

admin_header('Mon compte', 'compte', '<style>
    .main { max-width: 760px; }
    .infos-compte { display: grid; grid-template-columns: max-content 1fr; gap: 8px 20px; font-size: 0.92rem; }
    .infos-compte dt { color: #888; }
    .infos-compte dd { font-weight: 600; }
</style>');
?>
    <h1>👤 Mon compte</h1>
    <?php if ($pwMsg): ?>
        <div class="alert <?= $pwError ? 'alert-error' : 'alert-success' ?>"><?= htmlspecialchars($pwMsg) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Mes informations</h2>
        <dl class="infos-compte">
            <dt>Nom</dt>    <dd><?= htmlspecialchars($moi['nom']) ?></dd>
            <dt>Email</dt>  <dd><?= htmlspecialchars($moi['email']) ?></dd>
            <dt>Rôle</dt>   <dd><?= badge_role($moi['role']) ?></dd>
            <dt>Membre depuis</dt> <dd><?= date('d/m/Y', strtotime($moi['created_at'])) ?></dd>
        </dl>
        <?php if (!est_administrateur()): ?>
            <p class="form-hint" style="margin-top:14px">Pour modifier votre nom, votre email ou votre rôle, demandez à un administrateur.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>🔒 Changer mon mot de passe</h2>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="password">
            <div class="form-group">
                <label>Mot de passe actuel</label>
                <input type="password" name="mdp_actuel" autocomplete="current-password" required>
            </div>
            <div class="form-group">
                <label>Nouveau mot de passe <span style="color:#999;font-weight:400">(8 caractères min.)</span></label>
                <input type="password" name="mdp_nouveau" autocomplete="new-password" minlength="8" required>
            </div>
            <div class="form-group">
                <label>Confirmer le nouveau mot de passe</label>
                <input type="password" name="mdp_confirm" autocomplete="new-password" minlength="8" required>
            </div>
            <button type="submit" class="btn btn-primary">🔑 Mettre à jour</button>
        </form>
    </div>
<?php admin_footer(); ?>
