<?php
require_once 'auth.php';
exiger_administrateur();

$erreur  = '';
$message = '';
$moi = (int)$_SESSION['admin_id'];

// Peut-on modifier / supprimer ce compte ? Le compte fondateur n'est géré que par le fondateur lui-même.
function peut_gerer($compte) {
    return $compte && ($compte['role'] !== 'fondateur' || est_fondateur());
}
function compte_par_id($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, nom, role, email FROM admins WHERE id=?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch();
}
const ERREUR_FONDATEUR = 'Le compte fondateur ne peut être modifié ou supprimé que par le fondateur lui-même.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    $nom    = trim($_POST['nom'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $mdp    = $_POST['mot_de_passe'] ?? '';
    $mdp2   = $_POST['mot_de_passe_confirm'] ?? '';
    // Seuls les rôles attribuables sont acceptés : impossible de créer un second fondateur
    $role   = in_array($_POST['role'] ?? '', ROLES_ATTRIBUABLES, true) ? $_POST['role'] : 'moderateur';
    $cible  = in_array($action, ['edit', 'delete'], true) ? compte_par_id($id) : null;

    if (in_array($action, ['edit', 'delete'], true) && !$cible) {
        $erreur = 'Compte introuvable.';
    } elseif ($cible && !peut_gerer($cible)) {
        $erreur = ERREUR_FONDATEUR;
    } elseif ($action === 'create' || $action === 'edit') {
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
            $pdo->prepare("INSERT INTO admins (nom, role, email, mot_de_passe) VALUES (?,?,?,?)")
                ->execute([$nom, $role, $email, password_hash($mdp, PASSWORD_DEFAULT)]);
            $message = "Compte " . mb_strtolower(ROLES[$role]) . " créé pour $nom. Transmettez-lui son mot de passe, qui pourra ensuite être changé dans « Mon compte ».";
        } else {
            $pdo->prepare("UPDATE admins SET nom=?, email=? WHERE id=?")->execute([$nom, $email, $id]);
            // Rôle inchangeable pour soi-même et pour le fondateur
            if ($id !== $moi && $cible['role'] !== 'fondateur') {
                $pdo->prepare("UPDATE admins SET role=? WHERE id=?")->execute([$role, $id]);
            }
            if ($mdp !== '') {
                $pdo->prepare("UPDATE admins SET mot_de_passe=? WHERE id=?")->execute([password_hash($mdp, PASSWORD_DEFAULT), $id]);
            }
            if ($id === $moi) $_SESSION['admin_nom'] = $nom;
            $message = 'Compte mis à jour.';
        }
    } elseif ($action === 'delete') {
        if ($id === $moi) {
            $erreur = 'Vous ne pouvez pas supprimer votre propre compte.';
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

$admins = $pdo->query("SELECT id, nom, role, email, created_at FROM admins
                       ORDER BY FIELD(role, 'fondateur', 'administrateur', 'moderateur'), nom")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $edit = compte_par_id($_GET['edit']) ?: null;
    if ($edit && !peut_gerer($edit)) {
        $erreur = ERREUR_FONDATEUR;
        $edit = null;
    }
}
// En cas d'erreur, on garde ce qui a été saisi
$saisie = $erreur && $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ['nom' => $_POST['nom'] ?? '', 'email' => $_POST['email'] ?? '', 'role' => $_POST['role'] ?? 'moderateur']
    : ($edit ?: []);
// Rôle non modifiable : le sien, et celui du fondateur
$roleBloque = $edit && ((int)$edit['id'] === $moi || $edit['role'] === 'fondateur');

admin_header('Administrateurs', 'admins');
?>
    <h1>👥 Administrateurs</h1>
    <?php if ($erreur): ?><div class="alert alert-error">❌ <?= htmlspecialchars($erreur) ?></div><?php endif; ?>
    <?php if ($message): ?><div class="alert alert-success">✅ <?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="card">
        <h2>Les rôles</h2>
        <p class="form-hint" style="font-size:0.88rem;line-height:1.8">
            <?= badge_role('fondateur') ?> compte unique du créateur du site : accès à tout, et <strong>ne peut être ni modifié ni supprimé</strong> par les administrateurs.<br>
            <?= badge_role('administrateur') ?> accès à <strong>tout</strong> le back-office, y compris les Paramètres du site et la gestion des comptes.<br>
            <?= badge_role('moderateur') ?> gère le contenu (articles, messages, événements, galerie, entraîneurs, tarifs) et peut <strong>consulter</strong> les Paramètres sans pouvoir les modifier. Il ne voit pas cette page Administrateurs.
        </p>
    </div>

    <div class="card">
        <h2><?= $edit ? 'Modifier le compte' : 'Ajouter un compte' ?></h2>
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
            <div class="form-group">
                <label>Rôle</label>
                <select name="role" <?= $roleBloque ? 'disabled' : '' ?>>
                    <?php if ($roleBloque): ?>
                        <option selected><?= ROLES[$edit['role']] ?></option>
                    <?php else: ?>
                        <?php foreach (ROLES_ATTRIBUABLES as $valeur): ?>
                        <option value="<?= $valeur ?>" <?= ($saisie['role'] ?? 'moderateur') === $valeur ? 'selected' : '' ?>><?= ROLES[$valeur] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if ($roleBloque): ?>
                    <div class="form-hint"><?= $edit['role'] === 'fondateur'
                        ? 'Le rôle de fondateur est définitif.'
                        : 'Vous ne pouvez pas modifier votre propre rôle : il doit toujours rester au moins un administrateur.' ?></div>
                <?php endif; ?>
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
            <thead><tr><th>Nom</th><th>Rôle</th><th>Email</th><th>Créé le</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($admins as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['nom']) ?> <?php if ((int)$a['id'] === $moi): ?><span class="badge badge-gray">Vous</span><?php endif; ?></td>
                <td><?= badge_role($a['role']) ?></td>
                <td><?= htmlspecialchars($a['email']) ?></td>
                <td><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                <td>
                    <div class="actions">
                        <?php if (peut_gerer($a)): ?>
                            <a href="admins.php?edit=<?= $a['id'] ?>" class="btn btn-secondary btn-sm">✏️ Modifier</a>
                            <?php if ((int)$a['id'] !== $moi): ?>
                            <form method="POST" onsubmit="return confirm('Supprimer le compte de <?= htmlspecialchars(addslashes($a['nom'])) ?> ?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                            </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="form-hint">🔒 Protégé</span>
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
