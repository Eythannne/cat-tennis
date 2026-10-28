<?php
require_once 'auth.php';
$lectureSeule = lecture_seule_pour_moderateur();

$saved = false;

// Libellés des champs, pour le journal
const LIBELLES_PARAMETRES = [
    'nom_club' => 'nom du club', 'adresse' => 'adresse', 'email_contact' => 'email', 'telephone' => 'téléphone',
    'horaires' => 'horaires', 'description' => 'description', 'presentation_club' => 'présentation du club',
    'responsable_publication' => 'responsable de publication', 'rna' => 'numéro RNA', 'siret' => 'SIRET',
];

// Enregistre des paramètres et renvoie la liste des clés réellement modifiées
function enregistrer_parametres($valeurs) {
    global $pdo;
    $avant = $pdo->query("SELECT cle, valeur FROM parametres")->fetchAll(PDO::FETCH_KEY_PAIR);
    $stmt  = $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?,?) ON DUPLICATE KEY UPDATE valeur=?");
    $modifies = [];
    foreach ($valeurs as $cle => $valeur) {
        $stmt->execute([$cle, $valeur, $valeur]);
        if (($avant[$cle] ?? '') !== $valeur) $modifies[] = $cle;
    }
    return $modifies;
}

// ── Infos club ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'infos') {
    $valeurs = [];
    foreach (array_keys(LIBELLES_PARAMETRES) as $cle) $valeurs[$cle] = trim($_POST[$cle] ?? '');
    $modifies = enregistrer_parametres($valeurs);
    if ($modifies) {
        journaliser('parametres', 'Modification des informations du club : ' . implode(', ', array_map(fn($c) => LIBELLES_PARAMETRES[$c], $modifies)));
    }
    $saved = true;
}

// ── Chiffres clés (accueil + page Le club) ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'chiffres') {
    $valeurs = [];
    foreach (array_keys(CHIFFRES_DEFAUT) as $n) {
        foreach (['valeur', 'label'] as $champ) {
            $valeurs["stat{$n}_{$champ}"] = trim($_POST["stat{$n}_{$champ}"] ?? '');
        }
    }
    if (enregistrer_parametres($valeurs)) {
        journaliser('parametres', 'Modification des chiffres clés : ' . implode(' · ', array_map(
            fn($n) => $valeurs["stat{$n}_valeur"] . ' ' . $valeurs["stat{$n}_label"], array_keys(CHIFFRES_DEFAUT))));
    }
    $saved = true;
}

$params = $pdo->query("SELECT cle, valeur FROM parametres")->fetchAll(PDO::FETCH_KEY_PAIR);

admin_header('Paramètres', 'parametres', '<style>.main { max-width: 760px; }</style>');
?>
    <h1>⚙️ Paramètres du site</h1>
    <?php if ($lectureSeule) bandeau_lecture_seule(); ?>
    <?php if ($saved): ?>
        <div class="alert alert-success">✅ Paramètres enregistrés.</div>
    <?php endif; ?>

    <!-- ── Card : Infos club ── -->
    <div class="card">
        <h2>Informations du club</h2>
        <p class="form-hint" style="margin:-8px 0 16px">Ces informations s'affichent sur le site public (pied de page, page Contact, mentions légales). Un champ vide n'est pas affiché.</p>
        <form method="POST"><fieldset class="champs" <?= $lectureSeule ? 'disabled' : '' ?>>
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
            <div class="form-group">
                <label>Présentation du club (page « Le club », section « Notre histoire »)</label>
                <textarea name="presentation_club" style="height:150px" placeholder="<?= htmlspecialchars(presentation_club_defaut()) ?>"><?= htmlspecialchars($params['presentation_club'] ?? '') ?></textarea>
                <div class="form-hint">Vide = texte par défaut (affiché en grisé), avec l'année de création des chiffres clés. Une ligne vide = nouveau paragraphe ; <code>**gras**</code> possible.</div>
            </div>

            <h2 style="margin-top:28px">⚖️ Informations légales <span style="font-weight:400;font-size:0.85rem;color:#888">(page Mentions légales)</span></h2>
            <?php if (empty($params['responsable_publication']) || empty($params['rna'])): ?>
                <div class="alert alert-error">⚠️ À compléter avant la mise en ligne : le responsable de publication et le numéro RNA sont obligatoires pour une association.</div>
            <?php endif; ?>
            <div class="form-group">
                <label>Responsable de la publication</label>
                <input type="text" name="responsable_publication" value="<?= htmlspecialchars($params['responsable_publication'] ?? '') ?>" placeholder="ex : Prénom Nom, président(e) du club">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Numéro RNA</label>
                    <input type="text" name="rna" value="<?= htmlspecialchars($params['rna'] ?? '') ?>" placeholder="ex : W931000000">
                    <div class="form-hint">Sur le récépissé de déclaration en préfecture ou sur le Journal officiel des associations.</div>
                </div>
                <div class="form-group">
                    <label>Numéro SIRET (optionnel)</label>
                    <input type="text" name="siret" value="<?= htmlspecialchars($params['siret'] ?? '') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </fieldset></form>
    </div>

    <!-- ── Card : Chiffres clés ── -->
    <div class="card">
        <h2>🔢 Chiffres clés</h2>
        <p class="form-hint" style="margin:-8px 0 16px">Affichés sur l'accueil et la page « Le club ». Laisser le chiffre vide pour masquer une case. Le nombre d'entraîneurs s'ajoute tout seul.</p>
        <form method="POST"><fieldset class="champs" <?= $lectureSeule ? 'disabled' : '' ?>>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="chiffres">
            <?php foreach (CHIFFRES_DEFAUT as $n => [$valeurDefaut, $labelDefaut]): ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Chiffre <?= $n ?></label>
                    <input type="text" name="stat<?= $n ?>_valeur" value="<?= htmlspecialchars($params["stat{$n}_valeur"] ?? $valeurDefaut) ?>" placeholder="ex : 300+">
                </div>
                <div class="form-group">
                    <label>Légende</label>
                    <input type="text" name="stat<?= $n ?>_label" value="<?= htmlspecialchars($params["stat{$n}_label"] ?? $labelDefaut) ?>" placeholder="ex : Adhérents">
                </div>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </fieldset></form>
    </div>

    <p class="form-hint">🔒 Pour changer votre mot de passe, allez dans <a href="compte.php">👤 Mon compte</a>.</p>
<?php admin_footer(); ?>
