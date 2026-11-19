<?php
// Onglet Animations de la page Pédagogie : bibliothèque de fiches d'animations de club.
$animations = $pdo->query("SELECT * FROM peda_animations ORDER BY nom")->fetchAll();
$edit = isset($_GET['modifier']) ? peda_ligne('peda_animations', $_GET['modifier']) : null;
$formulaireOuvert = $edit || ($erreurs && ($_POST['action'] ?? '') === 'animation_enregistrer');
$a = $edit ?: [];
$niveauxCoches = $_SERVER['REQUEST_METHOD'] === 'POST' ? (array)($_POST['niveaux'] ?? []) : peda_liste($a['niveaux'] ?? '');
$agesCoches    = $_SERVER['REQUEST_METHOD'] === 'POST' ? (array)($_POST['ages'] ?? []) : peda_liste($a['ages'] ?? '');
$categorie     = peda_valeur('categorie', $a, 'tennistique');
?>
<div class="peda-entete">
    <p><strong><?= count($animations) ?></strong> animation<?= count($animations) > 1 ? 's' : '' ?> dans la bibliothèque. Planifiez-les ensuite dans l'onglet Calendrier.</p>
</div>

<details class="peda-carte peda-formulaire" id="formulaire" <?= $formulaireOuvert ? 'open' : '' ?>>
    <summary><?= $edit ? '✏️ Modifier « ' . htmlspecialchars($edit['nom']) . ' »' : '＋ Nouvelle animation' ?></summary>
    <form method="POST" action="<?= peda_url() ?>#formulaire" class="peda-form">
        <input type="hidden" name="action" value="animation_enregistrer">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>

        <div class="peda-champ peda-champ--large">
            <label for="a-nom">Nom de l'animation *</label>
            <input type="text" id="a-nom" name="nom" maxlength="150" required value="<?= htmlspecialchars(peda_valeur('nom', $a)) ?>" placeholder="ex : Olympiade de fin de saison">
        </div>

        <fieldset class="peda-champ">
            <legend>Catégorie</legend>
            <div class="peda-choix">
                <?php foreach (PEDA_CATEGORIES as $cle => $libelle): ?>
                <label class="peda-puce"><input type="radio" name="categorie" value="<?= $cle ?>" <?= $categorie === $cle ? 'checked' : '' ?> data-affiche="categorie-autre" data-si="autre"> <?= $libelle ?></label>
                <?php endforeach; ?>
            </div>
            <input type="text" id="categorie-autre" name="categorie_autre" maxlength="100" class="peda-conditionnel" placeholder="Précisez la catégorie"
                   value="<?= htmlspecialchars(peda_valeur('categorie_autre', $a)) ?>" <?= $categorie !== 'autre' ? 'hidden' : '' ?>>
        </fieldset>

        <div class="peda-champ">
            <label for="a-lieu">Lieu</label>
            <select id="a-lieu" name="lieu">
                <option value="">— Non précisé —</option>
                <?php foreach (PEDA_LIEUX as $cle => $libelle): ?>
                <option value="<?= $cle ?>" <?= peda_valeur('lieu', $a) === $cle ? 'selected' : '' ?>><?= $libelle ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="lieu_precision" maxlength="150" placeholder="Précision (facultatif) : ex. court n°3, salle des fêtes…"
                   value="<?= htmlspecialchars(peda_valeur('lieu_precision', $a)) ?>">
        </div>

        <fieldset class="peda-champ peda-champ--large">
            <legend>Niveaux concernés</legend>
            <div class="peda-choix">
                <?php foreach (PEDA_NIVEAUX as $cle => [$libelle, $couleur]): ?>
                <label class="peda-puce"><input type="checkbox" name="niveaux[]" value="<?= $cle ?>" <?= in_array($cle, $niveauxCoches, true) ? 'checked' : '' ?>>
                    <span class="peda-pastille" style="background:<?= $couleur ?>"></span><?= $libelle ?></label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <fieldset class="peda-champ peda-champ--large">
            <legend>Âge</legend>
            <div class="peda-choix">
                <?php foreach (PEDA_AGES as $cle => $libelle): ?>
                <label class="peda-puce"><input type="checkbox" name="ages[]" value="<?= $cle ?>" <?= in_array($cle, $agesCoches, true) ? 'checked' : '' ?>> <?= $libelle ?></label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="peda-champ peda-champ--duo">
            <div>
                <label for="a-duree">Durée (heures : minutes)</label>
                <input type="time" id="a-duree" name="duree" step="300" value="<?= htmlspecialchars(peda_duree_heure(peda_valeur('duree', $a))) ?>">
            </div>
            <div>
                <label for="a-prix">Prix (coût pour le club)</label>
                <input type="text" id="a-prix" name="prix" inputmode="decimal" placeholder="ex : 150" value="<?= htmlspecialchars(str_replace('.', ',', (string)peda_valeur('prix', $a))) ?>">
            </div>
        </div>

        <div class="peda-champ peda-champ--large">
            <label for="a-deroulement">Déroulement</label>
            <textarea id="a-deroulement" name="deroulement" rows="6" placeholder="Étapes, matériel, organisation…"><?= htmlspecialchars(peda_valeur('deroulement', $a)) ?></textarea>
        </div>

        <div class="peda-champ">
            <label class="peda-case"><input type="checkbox" name="main_oeuvre" value="1" data-affiche="main-oeuvre-nb" <?= peda_valeur('main_oeuvre', $a) ? 'checked' : '' ?>> Besoin de main d'œuvre</label>
            <div id="main-oeuvre-nb" class="peda-conditionnel" <?= peda_valeur('main_oeuvre', $a) ? '' : 'hidden' ?>>
                <label for="a-nb">Combien de personnes ?</label>
                <input type="number" id="a-nb" name="main_oeuvre_nb" min="1" max="500" value="<?= htmlspecialchars((string)peda_valeur('main_oeuvre_nb', $a)) ?>">
            </div>
        </div>
        <div class="peda-champ">
            <label class="peda-case"><input type="checkbox" name="participation" value="1" data-affiche="participation-prix" <?= peda_valeur('participation', $a) ? 'checked' : '' ?>> Demander une participation aux membres</label>
            <div id="participation-prix" class="peda-conditionnel" <?= peda_valeur('participation', $a) ? '' : 'hidden' ?>>
                <label for="a-part">Prix demandé par personne</label>
                <input type="text" id="a-part" name="participation_prix" inputmode="decimal" placeholder="ex : 5" value="<?= htmlspecialchars(str_replace('.', ',', (string)peda_valeur('participation_prix', $a))) ?>">
            </div>
        </div>

        <div class="peda-actions">
            <button type="submit" class="btn-primary"><?= $edit ? '💾 Enregistrer les modifications' : '＋ Créer l\'animation' ?></button>
            <?php if ($edit): ?><a href="<?= peda_url() ?>" class="peda-lien">Annuler</a><?php endif; ?>
        </div>
    </form>
</details>

<?php if ($animations): ?>
<div class="peda-filtres">
    <input type="search" class="peda-recherche" data-cible="#liste-animations .peda-fiche" placeholder="🔎 Rechercher une animation…" aria-label="Rechercher une animation">
    <div class="peda-choix" data-filtre-categorie>
        <button type="button" class="peda-puce actif" data-categorie="">Toutes</button>
        <?php foreach (PEDA_CATEGORIES as $cle => $libelle): ?>
        <button type="button" class="peda-puce" data-categorie="<?= $cle ?>"><?= $libelle ?></button>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="peda-grille" id="liste-animations">
    <?php if (!$animations): ?>
        <p class="peda-vide">Aucune animation pour l'instant. Créez la première avec « ＋ Nouvelle animation ».</p>
    <?php endif; ?>
    <?php foreach ($animations as $an):
        $cat = $an['categorie'] === 'autre' && $an['categorie_autre'] !== '' ? $an['categorie_autre'] : (PEDA_CATEGORIES[$an['categorie']] ?? '');
        $lieu = trim((PEDA_LIEUX[$an['lieu']] ?? '') . ($an['lieu_precision'] !== '' ? ' — ' . $an['lieu_precision'] : ''), ' —'); ?>
    <article class="peda-carte peda-fiche" data-categorie="<?= $an['categorie'] ?>">
        <div class="peda-fiche-tete">
            <h3><?= htmlspecialchars($an['nom']) ?></h3>
            <span class="peda-badge peda-badge--<?= $an['categorie'] ?>"><?= htmlspecialchars($cat) ?></span>
        </div>
        <ul class="peda-infos">
            <?php if ($lieu !== ''): ?><li>📍 <?= htmlspecialchars($lieu) ?></li><?php endif; ?>
            <?php if ($an['duree'] !== ''): ?><li>⏱️ <?= htmlspecialchars(peda_duree_affiche($an['duree'])) ?></li><?php endif; ?>
            <?php if ($an['prix'] !== null): ?><li>💶 Coût : <?= peda_prix_affiche($an['prix']) ?></li><?php endif; ?>
            <?php if ($an['main_oeuvre']): ?><li>🙋 Main d'œuvre : <?= (int)$an['main_oeuvre_nb'] ?> personne<?= $an['main_oeuvre_nb'] > 1 ? 's' : '' ?></li><?php endif; ?>
            <?php if ($an['participation']): ?><li>🎟️ Participation : <?= peda_prix_affiche($an['participation_prix']) ?> / membre</li><?php endif; ?>
        </ul>
        <?php if ($an['niveaux'] !== ''): ?>
        <div class="peda-etiquettes">
            <?php foreach (peda_liste($an['niveaux']) as $n): if (!isset(PEDA_NIVEAUX[$n])) continue; ?>
            <span class="peda-etiquette"><span class="peda-pastille" style="background:<?= PEDA_NIVEAUX[$n][1] ?>"></span><?= PEDA_NIVEAUX[$n][0] ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($an['ages'] !== ''): ?>
        <div class="peda-etiquettes">
            <?php foreach (peda_liste($an['ages']) as $ag): if (!isset(PEDA_AGES[$ag])) continue; ?>
            <span class="peda-etiquette peda-etiquette--age"><?= PEDA_AGES[$ag] ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if (trim($an['deroulement'] ?? '') !== ''): ?>
        <details class="peda-deroulement">
            <summary>Déroulement</summary>
            <div><?= nl2br(htmlspecialchars($an['deroulement'])) ?></div>
        </details>
        <?php endif; ?>
        <div class="peda-fiche-actions">
            <a href="<?= peda_url(['modifier' => $an['id']]) ?>#formulaire" class="peda-bouton">✏️ Modifier</a>
            <a href="pedagogie.php?onglet=calendrier&amp;planifier=<?= $an['id'] ?>#formulaire" class="peda-bouton">📅 Planifier</a>
            <form method="POST" action="<?= peda_url() ?>" data-confirmer="Supprimer l'animation « <?= htmlspecialchars($an['nom']) ?> » ? Les dates déjà planifiées sont conservées.">
                <input type="hidden" name="action" value="animation_supprimer">
                <input type="hidden" name="id" value="<?= $an['id'] ?>">
                <button type="submit" class="peda-bouton peda-bouton--danger" title="Supprimer l'animation" aria-label="Supprimer l'animation">🗑️</button>
            </form>
        </div>
    </article>
    <?php endforeach; ?>
</div>
