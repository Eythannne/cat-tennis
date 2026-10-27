<?php
require_once 'auth.php';

$saved   = false;
$pwMsg   = '';
$pwError = false;

// ── Infos club ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'infos') {
    $champs = ['nom_club', 'adresse', 'email_contact', 'telephone', 'horaires', 'description'];
    foreach ($champs as $cle) {
        $valeur = trim($_POST[$cle] ?? '');
        $stmt = $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?,?) ON DUPLICATE KEY UPDATE valeur=?");
        $stmt->execute([$cle, $valeur, $valeur]);
    }
    $saved = true;
}

// ── Changement de mot de passe ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'password') {
    $actuel    = $_POST['mdp_actuel']    ?? '';
    $nouveau   = $_POST['mdp_nouveau']   ?? '';
    $confirm   = $_POST['mdp_confirm']   ?? '';

    // Récupère le hash actuel de l'admin connecté
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
    }
}

$params = $pdo->query("SELECT cle, valeur FROM parametres")->fetchAll(PDO::FETCH_KEY_PAIR);

admin_header('Paramètres', 'parametres', '<style>.main { max-width: 760px; }</style>');
?>
    <h1>⚙️ Paramètres du site</h1>
    <?php if ($saved): ?>
        <div class="alert alert-success">✅ Paramètres enregistrés.</div>
    <?php endif; ?>
    <?php if ($pwMsg): ?>
        <div class="alert <?= $pwError ? 'alert-error' : 'alert-success' ?>"><?= htmlspecialchars($pwMsg) ?></div>
    <?php endif; ?>

    <!-- ── Card : Infos club ── -->
    <div class="card">
        <h2>Informations du club</h2>
        <p class="form-hint" style="margin:-8px 0 16px">Ces informations s'affichent sur le site public (pied de page, page Contact, mentions légales). Un champ vide n'est pas affiché.</p>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="infos">
            <div class="form-group">
                <label>Nom du club</label>
                <input type="text" name="nom_club" value="<?= htmlspecialchars($params['nom_club'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Adresse</label>
                <input type="text" name="adresse" value="<?= htmlspecialchars($params['adresse'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email de contact</label>
                    <input type="email" name="email_contact" value="<?= htmlspecialchars($params['email_contact'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($params['telephone'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Horaires du secrétariat</label>
                <textarea name="horaires" placeholder="Lundi – Vendredi : 14h – 18h&#10;Samedi : 9h – 12h"><?= htmlspecialchars($params['horaires'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Description courte</label>
                <textarea name="description"><?= htmlspecialchars($params['description'] ?? '') ?></textarea>
                <div class="form-hint">Utilisée pour la description de la page d'accueil dans Google.</div>
            </div>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </form>
    </div>

    <!-- ── Card : Changer le mot de passe ── -->
    <div class="card">
        <h2>🔒 Changer le mot de passe</h2>
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
