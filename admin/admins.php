<?php
require_once 'auth.php';

$erreur  = '';
$message = '';
$moi = (int)$_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    $nom    = trim($_POST['nom'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $mdp    = $_POST['mot_de_passe'] ?? '';
    $mdp2   = $_POST['mot_de_passe_confirm'] ?? '';

    if ($action === 'create' || $action === 'edit') {
        // Mot de passe obligatoire à la création, optionnel en modification (vide = inchangé)
        $mdpDemande = $action === 'create' || $mdp !== '';
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = ? AND id <> ?");
        $stmt->execute([$email, $id]);

        if ($nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'Nom et adresse email valides obligatoires.';
        } elseif ($stmt->fetchColumn() > 0) {
            $erreur = 'Un autre compte utilise déjà cette adresse email.';
        } elseif ($mdpDemande && strlen($mdp) < 8) {
            $erreur = 'Le mot de passe doit faire au moins 8 caractères.';
        } elseif ($mdpDemande && $mdp !== $mdp2) {
            $erreur = 'Les deux mots de passe ne correspondent pas.';
        } elseif ($action === 'create') {
            $pdo->prepare("INSERT INTO admins (nom, email, mot_de_passe) VALUES (?,?,?)")
                ->execute([$nom, $email, password_hash($mdp, PASSWORD_DEFAULT)]);
            $message = "Compte créé pour $nom. Transmettez-lui son mot de passe, qui pourra ensuite être changé dans Paramètres.";
        } else {
            $pdo->prepare("UPDATE admins SET nom=?, email=? WHERE id=?")->execute([$nom, $email, $id]);
            if ($mdp !== '') {
                $pdo->prepare("UPDATE admins SET mot_de_passe=? WHERE id=?")->execute([password_hash($mdp, PASSWORD_DEFAULT), $id]);
            }
            if ($id === $moi) $_SESSION['admin_nom'] = $nom;
            $message = 'Compte mis à jour.';
        }
    }

    if ($action === 'delete') {
        $total = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        if ($id === $moi) {
            $erreur = 'Vous ne pouvez pas supprimer votre propre compte.';
        } elseif ($total <= 1) {
            $erreur = 'Impossible de supprimer le dernier compte administrateur.';
        } else {
            $pdo->prepare("DELETE FROM admins WHERE id=?")->execute([$id]);
            $message = 'Compte supprimé.';
        }
    }

    // Succès : on recharge la page (évite un double envoi), le message passe par la session
    if (!$erreur) {
        $_SESSION['flash'] = $message;
        header('Location: admins.php');
        exit;
    }
}

$message = $message ?: ($_SESSION['flash'] ?? '');
unset($_SESSION['flash']);

$admins = $pdo->query("SELECT id, nom, email, created_at FROM admins ORDER BY nom")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT id, nom, email FROM admins WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
// En cas d'erreur, on garde ce qui a été saisi
$saisie = $erreur ? ['nom' => $_POST['nom'] ?? '', 'email' => $_POST['email'] ?? ''] : ($edit ?: []);

admin_header('Administrateurs', 'admins');
?>
    <h1>👥 Administrateurs</h1>
    <?php if ($erreur): ?><div class="alert alert-error">❌ <?= htmlspecialchars($erreur) ?></div><?php endif; ?>
    <?php if ($message): ?><div class="alert alert-success">✅ <?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="card">
        <h2><?= $edit ? 'Modifier le compte' : 'Ajouter un administrateur' ?></h2>
        <form method="POST" autocomplete="off">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'create' ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($saisie['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email (sert d'identifiant)</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($saisie['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><?= $edit ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe (8 caractères min.)' ?></label>
                    <input type="password" name="mot_de_passe" autocomplete="new-password" minlength="8" <?= $edit ? '' : 'required' ?>>
                </div>
                <div class="form-group">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="mot_de_passe_confirm" autocomplete="new-password" minlength="8" <?= $edit ? '' : 'required' ?>>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Mettre à jour' : 'Créer le compte' ?></button>
                <?php if ($edit): ?><a href="admins.php" class="link-cancel">Annuler</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Comptes (<?= count($admins) ?>)</h2>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Nom</th><th>Email</th><th>Créé le</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($admins as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['nom']) ?> <?php if ((int)$a['id'] === $moi): ?><span class="badge badge-green">Vous</span><?php endif; ?></td>
                <td><?= htmlspecialchars($a['email']) ?></td>
                <td><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                <td>
                    <div class="actions">
                        <a href="admins.php?edit=<?= $a['id'] ?>" class="btn btn-secondary btn-sm">✏️ Modifier</a>
                        <?php if ((int)$a['id'] !== $moi): ?>
                        <form method="POST" onsubmit="return confirm('Supprimer le compte de <?= htmlspecialchars(addslashes($a['nom'])) ?> ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
<?php admin_footer(); ?>
