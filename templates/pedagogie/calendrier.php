<?php
// Onglet Calendrier de la page Pédagogie : calendrier interne des animations (indépendant du
// calendrier public), avec publication possible sur le calendrier public.
$animations = $pdo->query("SELECT id, nom FROM peda_animations ORDER BY nom")->fetchAll();
// Statut de publication : l'événement public doit toujours exister (il a pu être supprimé depuis l'admin)
$planifs = $pdo->query("SELECT c.*, e.id AS publie FROM peda_calendrier c LEFT JOIN evenements e ON e.id = c.evenement_id
                        ORDER BY c.date_debut")->fetchAll();
$aujourdhui = date('Y-m-d');
$aVenir = array_filter($planifs, fn($p) => substr($p['date_fin'] ?: $p['date_debut'], 0, 10) >= $aujourdhui);
$passees = array_reverse(array_filter($planifs, fn($p) => substr($p['date_fin'] ?: $p['date_debut'], 0, 10) < $aujourdhui));

$edit = isset($_GET['modifier']) ? peda_ligne('peda_calendrier', $_GET['modifier']) : null;
$p = $edit ?: ['animation_id' => (int)($_GET['planifier'] ?? 0) ?: null];
$formulaireOuvert = $edit || isset($_GET['planifier']) || ($erreurs && ($_POST['action'] ?? '') === 'planif_enregistrer');
$dateChamp = fn($champ) => ($v = peda_valeur($champ, $p)) ? date('Y-m-d\TH:i', strtotime($v)) : '';

function peda_ligne_planif($pl) {
    $debut = strtotime($pl['date_debut']);
    $quand = quand_evenement($pl);
    ?>
    <li class="peda-planif" id="planif-<?= $pl['id'] ?>">
        <div class="peda-planif-date"><b><?= date('j', $debut) ?></b><small><?= PEDA_MOIS_COURTS[date('n', $debut) - 1] ?><br><?= date('Y', $debut) ?></small></div>
        <div class="peda-planif-infos">
            <strong><?= htmlspecialchars($pl['titre']) ?></strong>
            <span><?= $quand ?></span>
            <?php if ($pl['publie']): ?><span class="peda-badge peda-badge--publie">🌐 Sur le calendrier public</span>
            <?php else: ?><span class="peda-badge peda-badge--interne">🔒 Interne</span><?php endif; ?>
        </div>
        <div class="peda-fiche-actions">
            <a href="<?= peda_url(['modifier' => $pl['id']]) ?>#formulaire" class="peda-bouton">✏️ Modifier</a>
            <form method="POST" action="<?= peda_url() ?>" <?= $pl['publie'] ? '' : 'data-confirmer="Publier « ' . htmlspecialchars($pl['titre']) . ' » sur le calendrier public ? Elle sera visible par tous les visiteurs du site."' ?>>
                <input type="hidden" name="action" value="<?= $pl['publie'] ? 'planif_retirer' : 'planif_publier' ?>">
                <input type="hidden" name="id" value="<?= $pl['id'] ?>">
                <button type="submit" class="peda-bouton <?= $pl['publie'] ? '' : 'peda-bouton--publier' ?>"><?= $pl['publie'] ? '↩️ Retirer du calendrier public' : '🌐 Publier sur le calendrier public' ?></button>
            </form>
            <form method="POST" action="<?= peda_url() ?>" data-confirmer="Supprimer « <?= htmlspecialchars($pl['titre']) ?> » du calendrier interne<?= $pl['publie'] ? ' et du calendrier public' : '' ?> ?">
                <input type="hidden" name="action" value="planif_supprimer">
                <input type="hidden" name="id" value="<?= $pl['id'] ?>">
                <button type="submit" class="peda-bouton peda-bouton--danger">🗑️</button>
            </form>
        </div>
    </li>
    <?php
}
?>
<div class="peda-entete">
    <p>Calendrier <strong>interne</strong> des entraîneurs : il n'est pas visible sur le site. Le bouton « Publier sur le calendrier public » ajoute une animation au calendrier que voient tous les visiteurs.</p>
</div>

<div class="cal-legend peda-legende">
    <span class="legend-item"><span class="legend-dot" style="background:#8E44AD"></span>Interne</span>
    <span class="legend-item"><span class="legend-dot" style="background:#1B6B2F"></span>Publiée sur le calendrier public</span>
</div>
<div id="calendar" data-source="<?= BASE_URL ?>/api/pedagogie.php" data-lien="<?= htmlspecialchars(peda_url()) ?>"></div>

<details class="peda-carte peda-formulaire" id="formulaire" <?= $formulaireOuvert ? 'open' : '' ?>>
    <summary><?= $edit ? '✏️ Modifier « ' . htmlspecialchars($edit['titre']) . ' »' : '＋ Planifier une animation' ?></summary>
    <form method="POST" action="<?= peda_url() ?>#formulaire" class="peda-form">
        <input type="hidden" name="action" value="planif_enregistrer">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="peda-champ peda-champ--duo">
            <div>
                <label for="p-animation">Animation de la bibliothèque</label>
                <select id="p-animation" name="animation_id">
                    <option value="">— Aucune (titre libre) —</option>
                    <?php foreach ($animations as $an): ?>
                    <option value="<?= $an['id'] ?>" <?= (int)peda_valeur('animation_id', $p) === (int)$an['id'] ? 'selected' : '' ?>><?= htmlspecialchars($an['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="p-titre">Titre</label>
                <input type="text" id="p-titre" name="titre" maxlength="150" value="<?= htmlspecialchars(peda_valeur('titre', $p)) ?>" placeholder="Vide = nom de l'animation choisie">
            </div>
        </div>
        <div class="peda-champ peda-champ--duo">
            <div>
                <label for="p-debut">Début *</label>
                <input type="datetime-local" id="p-debut" name="date_debut" required value="<?= $dateChamp('date_debut') ?>">
            </div>
            <div>
                <label for="p-fin">Fin (facultatif)</label>
                <input type="datetime-local" id="p-fin" name="date_fin" value="<?= $dateChamp('date_fin') ?>">
            </div>
        </div>
        <div class="peda-champ peda-champ--large">
            <label for="p-notes">Description</label>
            <textarea id="p-notes" name="notes" rows="3" placeholder="Infos pratiques. Reprise sur le calendrier public si l'animation est publiée."><?= htmlspecialchars(peda_valeur('notes', $p)) ?></textarea>
            <?php if ($edit && $edit['evenement_id']): ?><p class="peda-aide">Cette animation est publiée : vos modifications seront aussi appliquées au calendrier public.</p><?php endif; ?>
        </div>
        <div class="peda-actions">
            <button type="submit" class="btn-primary"><?= $edit ? '💾 Enregistrer les modifications' : '📅 Ajouter au calendrier' ?></button>
            <?php if ($edit || isset($_GET['planifier'])): ?><a href="<?= peda_url() ?>" class="peda-lien">Annuler</a><?php endif; ?>
        </div>
    </form>
</details>

<div class="peda-carte">
    <h2 class="peda-titre-carte">À venir (<?= count($aVenir) ?>)</h2>
    <?php if (!$aVenir): ?><p class="peda-vide">Aucune animation planifiée à venir.</p><?php endif; ?>
    <ul class="peda-planifs"><?php foreach ($aVenir as $pl) peda_ligne_planif($pl); ?></ul>
    <?php if ($passees): ?>
    <details class="peda-passees">
        <summary>Animations passées (<?= count($passees) ?>)</summary>
        <ul class="peda-planifs"><?php foreach ($passees as $pl) peda_ligne_planif($pl); ?></ul>
    </details>
    <?php endif; ?>
</div>
