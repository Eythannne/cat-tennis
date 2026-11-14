<?php
require_once 'auth.php';
require_once __DIR__ . '/../src/ia.php';
$lectureSeule = lecture_seule_pour_moderateur();
// Onglet Intelligence artificielle (clé API payante) : modifiable par le fondateur uniquement
$iaLectureSeule = !est_fondateur();

// La page est découpée en onglets (?onglet=...) pour rester lisible
const ONGLETS_PARAMETRES = [
    'infos'    => '🏛️ Informations du club',
    'chiffres' => '🔢 Chiffres clés',
    'textes'   => '✏️ Textes du site',
    'ia'       => '✨ Intelligence artificielle',
];
$onglet = isset(ONGLETS_PARAMETRES[$_GET['onglet'] ?? '']) ? $_GET['onglet'] : 'infos';

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

// ── Textes du site (voir src/textes.php) ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'textes') {
    $valeurs = [];
    foreach (TEXTES_SITE as [, $champs]) {
        foreach ($champs as $cle => [$libelle, $defaut]) {
            $valeur = trim(str_replace("\r\n", "\n", $_POST['texte'][$cle] ?? ''));
            // L'adresse de réservation doit être un vrai lien web
            if ($cle === 'lien_reservation' && !preg_match('#^https?://#i', $valeur)) $valeur = '';
            // Identique au texte d'origine = on ne stocke rien : le texte par défaut s'applique
            $valeurs["texte_$cle"] = $valeur === $defaut ? '' : $valeur;
        }
    }
    $modifies = enregistrer_parametres($valeurs);
    if ($modifies) {
        journaliser('parametres', 'Modification des textes du site : ' . implode(', ', array_map(
            fn($c) => texte_definition(substr($c, 6))[0], $modifies)));
    }
    $saved = true;
}

// ── Intelligence artificielle (génération d'articles, voir src/ia.php) ────────
$erreurIa = $avertissementIa = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ia') {
    if ($iaLectureSeule) {
        journaliser('parametres', "Tentative refusée de modifier les réglages de l'intelligence artificielle");
        acces_refuse();
    }
    $cleActuelle = param_brut('gemini_api_key') ?? '';
    $cleSaisie   = trim($_POST['gemini_api_key'] ?? '');
    // Champ vide = on garde la clé enregistrée (elle n'est jamais réaffichée dans la page)
    $cle    = !empty($_POST['supprimer_cle']) ? '' : ($cleSaisie !== '' ? $cleSaisie : $cleActuelle);
    $modele = trim($_POST['gemini_modele'] ?? '');
    $prompt = trim(str_replace("\r\n", "\n", $_POST['prompt_ia_articles'] ?? ''));

    if ($modele !== '' && !preg_match('/^[a-z0-9][a-z0-9.\-]*$/', $modele)) {
        $erreurIa = 'Nom de modèle invalide (ex : gemini-flash-latest).';
    } elseif ($cle !== '' && preg_match('/\s/', $cle)) {
        $erreurIa = 'La clé API ne doit pas contenir d\'espace.';
    } elseif ($cle !== '' && ($cle !== $cleActuelle || $modele !== (param_brut('gemini_modele') ?? ''))) {
        // Nouvelle clé ou nouveau modèle : on vérifie auprès de Google avant d'enregistrer
        $verification = gemini_verifier($cle, $modele ?: GEMINI_MODELE_DEFAUT);
        $erreurIa        = $verification['erreur'] ?? null;
        $avertissementIa = $verification['avertissement'] ?? null;
    }

    if ($erreurIa === null) {
        $modifies = enregistrer_parametres([
            'gemini_api_key'     => $cle,
            'gemini_modele'      => $modele === GEMINI_MODELE_DEFAUT ? '' : $modele,
            'prompt_ia_articles' => $prompt === PROMPT_IA_ARTICLES_DEFAUT ? '' : $prompt,
        ]);
        $libelles = ['gemini_api_key' => $cle === '' ? 'clé API supprimée' : 'clé API', 'gemini_modele' => 'modèle', 'prompt_ia_articles' => 'consigne'];
        if ($modifies) {
            journaliser('parametres', 'Modification de l\'intelligence artificielle : ' . implode(', ', array_map(fn($c) => $libelles[$c], $modifies)));
        }
        $saved = true;
    }
}

$params = $pdo->query("SELECT cle, valeur FROM parametres")->fetchAll(PDO::FETCH_KEY_PAIR);

// Valeurs affichées dans l'onglet IA (celles saisies si l'enregistrement a été refusé)
$cleIa    = $params['gemini_api_key'] ?? '';
$modeleIa = $erreurIa ? trim($_POST['gemini_modele'] ?? '') : ($params['gemini_modele'] ?? '');
$promptIa = $erreurIa ? (trim($_POST['prompt_ia_articles'] ?? '') ?: PROMPT_IA_ARTICLES_DEFAUT) : (($params['prompt_ia_articles'] ?? '') ?: PROMPT_IA_ARTICLES_DEFAUT);

admin_header('Paramètres', 'parametres', '<style>
    .main { max-width: 760px; }
    .textes-groupe { border: 1px solid #e3e8e4; border-radius: 8px; margin-bottom: 10px; background: #fafcfa; }
    .textes-groupe summary { cursor: pointer; padding: 12px 14px; font-weight: 600; color: var(--vert); list-style: none; display: flex; justify-content: space-between; }
    .textes-groupe summary::after { content: "▸"; color: #999; }
    .textes-groupe[open] summary::after { content: "▾"; }
    .textes-groupe[open] summary { border-bottom: 1px solid #e3e8e4; }
    .textes-champs { padding: 14px; }
    .texte-label { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; }
    .texte-reset { background: none; border: none; color: var(--vert); font-size: 0.78rem; cursor: pointer; text-decoration: underline; white-space: nowrap; }
    .texte-modifie { font-size: 0.7rem; font-weight: 700; color: #8a6d00; background: #FFF4C2; border-radius: 10px; padding: 1px 7px; margin-left: 6px; }
    fieldset.champs[disabled] .texte-reset { display: none; }
    .onglets { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 20px; border-bottom: 2px solid #e3e8e4; }
    .onglets a { padding: 10px 16px; text-decoration: none; color: #666; font-weight: 600; font-size: 0.92rem; border-bottom: 3px solid transparent; margin-bottom: -2px; border-radius: 6px 6px 0 0; }
    .onglets a:hover { color: var(--vert); background: #fff; }
    .onglets a.actif { color: var(--vert); border-bottom-color: var(--jaune); background: #fff; }
    .onglet[hidden] { display: none; }
    .cle-statut { font-size: 0.85rem; padding: 7px 10px; border-radius: 6px; margin-bottom: 8px; }
    .cle-ok { background: var(--vert-light); color: var(--vert); }
    .cle-absente { background: #FFF4C2; color: #8a6d00; }
    .cle-statut code { background: rgba(255,255,255,0.6); padding: 1px 5px; border-radius: 3px; }
</style>');
?>
    <h1>⚙️ Paramètres du site</h1>
    <?php if ($lectureSeule) bandeau_lecture_seule(); ?>
    <?php if ($saved): ?>
        <div class="alert alert-success">✅ Paramètres enregistrés.</div>
    <?php endif; ?>
    <?php if ($erreurIa): ?>
        <div class="alert alert-error">⚠️ Rien n'a été enregistré : <?= htmlspecialchars($erreurIa) ?></div>
    <?php endif; ?>
    <?php if ($avertissementIa): ?>
        <div class="alert alert-info">ℹ️ <?= htmlspecialchars($avertissementIa) ?></div>
    <?php endif; ?>

    <!-- Onglets : une seule partie affichée à la fois -->
    <nav class="onglets">
        <?php foreach (ONGLETS_PARAMETRES as $cle => $libelle): ?>
        <a href="parametres.php?onglet=<?= $cle ?>" class="<?= $onglet === $cle ? 'actif' : '' ?>"><?= $libelle ?></a>
        <?php endforeach; ?>
    </nav>

    <!-- ── Onglet : Infos club ── -->
    <div class="card onglet" <?= $onglet !== 'infos' ? 'hidden' : '' ?>>
        <h2>Informations du club</h2>
        <p class="form-hint" style="margin:-8px 0 16px">Ces informations s'affichent sur le site public (pied de page, page Contact, mentions légales). Un champ vide n'est pas affiché.</p>
        <form method="POST" action="parametres.php?onglet=<?= $onglet ?>"><fieldset class="champs" <?= $lectureSeule ? 'disabled' : '' ?>>
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

    <!-- ── Onglet : Chiffres clés ── -->
    <div class="card onglet" <?= $onglet !== 'chiffres' ? 'hidden' : '' ?>>
        <h2>🔢 Chiffres clés</h2>
        <p class="form-hint" style="margin:-8px 0 16px">Affichés sur l'accueil et la page « Le club ». Laisser le chiffre vide pour masquer une case. Le nombre d'entraîneurs s'ajoute tout seul.</p>
        <form method="POST" action="parametres.php?onglet=<?= $onglet ?>"><fieldset class="champs" <?= $lectureSeule ? 'disabled' : '' ?>>
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

    <!-- ── Onglet : Textes du site ── -->
    <div class="card onglet" id="textes" <?= $onglet !== 'textes' ? 'hidden' : '' ?>>
        <h2>✏️ Textes du site</h2>
        <p class="form-hint" style="margin:-8px 0 16px">Titres, sous-titres, boutons et paragraphes affichés sur le site, classés par page.
            Un champ vide reprend le texte d'origine. Les textes modifiés sont signalés par « modifié ».</p>
        <form method="POST" action="parametres.php?onglet=<?= $onglet ?>"><fieldset class="champs" <?= $lectureSeule ? 'disabled' : '' ?>>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="textes">
            <?php foreach (TEXTES_SITE as $groupe => [$titreGroupe, $champs]):
                $nbModifies = count(array_filter(array_keys($champs), fn($c) => ($params["texte_$c"] ?? '') !== '')); ?>
            <details class="textes-groupe">
                <summary><span><?= $titreGroupe ?><?php if ($nbModifies): ?><span class="texte-modifie"><?= $nbModifies ?> modifié<?= $nbModifies > 1 ? 's' : '' ?></span><?php endif; ?></span></summary>
                <div class="textes-champs">
                    <?php foreach ($champs as $cle => $def):
                        [$libelle, $defaut] = $def;
                        $long    = ($def[2] ?? '') === 'long';
                        $modifie = ($params["texte_$cle"] ?? '') !== '';
                        $valeur  = $modifie ? $params["texte_$cle"] : $defaut; ?>
                    <div class="form-group">
                        <label class="texte-label">
                            <span><?= htmlspecialchars($libelle) ?><?php if ($modifie): ?><span class="texte-modifie">modifié</span><?php endif; ?></span>
                            <button type="button" class="texte-reset" data-defaut="<?= htmlspecialchars($defaut) ?>" title="<?= htmlspecialchars($defaut) ?>">↺ Texte d'origine</button>
                        </label>
                        <?php if ($long): ?>
                            <textarea name="texte[<?= $cle ?>]" rows="3"><?= htmlspecialchars($valeur) ?></textarea>
                        <?php else: ?>
                            <input type="<?= $cle === 'lien_reservation' ? 'url' : 'text' ?>" name="texte[<?= $cle ?>]" value="<?= htmlspecialchars($valeur) ?>">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </details>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary" style="margin-top:6px">💾 Enregistrer les textes</button>
        </fieldset></form>
    </div>

    <!-- ── Onglet : Intelligence artificielle ── -->
    <div class="card onglet" <?= $onglet !== 'ia' ? 'hidden' : '' ?>>
        <h2>✨ Intelligence artificielle</h2>
        <p class="form-hint" style="margin:-8px 0 16px">Sert au bouton <strong>✨ Générer</strong> de la page Articles : l'IA Gemini de Google rédige l'article à partir du titre et des notes.</p>
        <?php if ($iaLectureSeule): ?>
            <div class="alert alert-info">👁️ <strong>Lecture seule</strong> — seul le fondateur peut modifier les réglages de l'intelligence artificielle.</div>
        <?php endif; ?>
        <form method="POST" action="parametres.php?onglet=<?= $onglet ?>"><fieldset class="champs" <?= $iaLectureSeule ? 'disabled' : '' ?>>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="ia">
            <div class="form-group">
                <label for="gemini_api_key">Clé API Gemini</label>
                <?php if ($cleIa !== ''): ?>
                    <div class="cle-statut cle-ok">✅ Clé enregistrée (se termine par <code>…<?= htmlspecialchars(substr($cleIa, -4)) ?></code>)</div>
                <?php else: ?>
                    <div class="cle-statut cle-absente">Aucune clé enregistrée : le bouton ✨ Générer est inactif.</div>
                <?php endif; ?>
                <input type="password" id="gemini_api_key" name="gemini_api_key" autocomplete="new-password" spellcheck="false"
                       placeholder="<?= $cleIa !== '' ? 'Laisser vide pour garder la clé actuelle' : 'Collez votre clé (commence par AIza…)' ?>">
                <div class="form-hint">Clé gratuite à créer sur <strong>aistudio.google.com/apikey</strong> (compte Google du club). Elle est vérifiée auprès de Google à l'enregistrement et n'est jamais réaffichée.</div>
                <?php if ($cleIa !== ''): ?>
                    <label class="form-check" style="margin-top:8px"><input type="checkbox" name="supprimer_cle" value="1"> Supprimer la clé enregistrée</label>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="gemini_modele">Modèle</label>
                <input type="text" id="gemini_modele" name="gemini_modele" value="<?= htmlspecialchars($modeleIa) ?>" placeholder="<?= GEMINI_MODELE_DEFAUT ?>" spellcheck="false" style="max-width:320px">
                <div class="form-hint">Vide = <code><?= GEMINI_MODELE_DEFAUT ?></code> (rapide, quelques secondes). Si le modèle est surchargé, un modèle de secours est essayé automatiquement. À changer seulement si Google arrête un modèle.</div>
            </div>
            <div class="form-group">
                <label class="texte-label" for="prompt_ia_articles">
                    <span>Prompt IA (consigne donnée à l'IA)<?php if (($params['prompt_ia_articles'] ?? '') !== ''): ?><span class="texte-modifie">modifié</span><?php endif; ?></span>
                    <button type="button" class="texte-reset" data-defaut="<?= htmlspecialchars(PROMPT_IA_ARTICLES_DEFAUT) ?>">↺ Texte d'origine</button>
                </label>
                <textarea id="prompt_ia_articles" name="prompt_ia_articles" style="height:140px"><?= htmlspecialchars($promptIa) ?></textarea>
                <div class="form-hint">Ton, longueur, style… Les règles de mise en forme du site (intertitres, gras, listes) et l'interdiction d'inventer des dates ou des prix sont ajoutées automatiquement.</div>
            </div>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </fieldset></form>
    </div>

    <p class="form-hint">🔒 Pour changer votre mot de passe, allez dans <a href="compte.php">👤 Mon compte</a>.</p>
<?php admin_footer(<<<'HTML'
<script>
// "↺ Texte d'origine" : remet le texte par défaut dans le champ (enregistré au clic sur "Enregistrer les textes")
document.querySelectorAll('.texte-reset').forEach(btn => {
    btn.addEventListener('click', () => {
        const champ = btn.closest('.form-group').querySelector('input, textarea');
        champ.value = btn.dataset.defaut;
        champ.focus();
    });
});
</script>
HTML); ?>
