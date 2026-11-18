<?php
require_once '../src/init.php';
require_once '../src/jeux.php';
// Jeu « Un petit échange » de l'espace Jeux (voir JEUX dans src/jeux.php)
$jeu  = 'echange';
$info = jeu_info($jeu);
$pageTitle = $info['titre'] . " — Jeux du CAT";
$pageDesc = "Mini-jeu de tennis du Club Aulnaysien de Tennis : " . $info['description'];
// Styles propres à cette page, ajoutés dans le <head> (voir templates/head.php)
ob_start();
?>
<style>
    .section-jeu { padding: 30px 0 70px; }
    .jeu-retour { display: inline-block; margin-bottom: 18px; color: var(--vert); font-weight: 600; text-decoration: none; }
    .jeu-retour:hover { text-decoration: underline; }
    .jeu-grille { display: grid; grid-template-columns: minmax(0, 420px) minmax(0, 340px); gap: 32px; justify-content: center; align-items: start; }
    .jeu-zone { position: relative; width: 100%; max-width: 420px; margin: 0 auto; }
    .jeu-hud { display: flex; justify-content: space-between; align-items: baseline; padding: 0 4px 10px; font-family: 'Outfit', sans-serif; }
    .jeu-hud .valeur { font-size: 1.6rem; font-weight: 700; color: var(--vert); }
    .jeu-hud .label { font-size: 0.8rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; }
    .jeu-cadre { position: relative; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 30px rgba(18,74,32,0.25); border: 4px solid var(--vert-dark); }
    #jeu-canvas { display: block; width: 100%; aspect-ratio: 2 / 3; touch-action: none; outline: none; cursor: pointer; }
    .jeu-ecran[hidden] { display: none; }
    .jeu-ecran { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; padding: 24px; text-align: center; color: #fff; background: rgba(10,40,20,0.72); backdrop-filter: blur(2px); }
    .jeu-ecran h2 { color: #fff; font-size: 1.7rem; }
    .jeu-ecran p { color: rgba(255,255,255,0.85); font-size: 0.95rem; max-width: 300px; }
    .jeu-ecran form { display: flex; flex-direction: column; gap: 10px; width: 100%; max-width: 260px; }
    .jeu-ecran input { padding: 12px 14px; border-radius: 10px; border: 2px solid transparent; font-size: 1rem; font-family: inherit; text-align: center; }
    .jeu-ecran input:focus { outline: none; border-color: var(--jaune); }
    .jeu-ecran .btn-primary, .jeu-ecran .btn-secondaire { border: none; cursor: pointer; font-family: inherit; font-size: 1rem; }
    .btn-secondaire { background: transparent; color: #fff; text-decoration: underline; padding: 6px; }
    .jeu-erreur { color: #FFD3CC; font-size: 0.85rem; min-height: 1.2em; }
    .jeu-gros-score { font-family: 'Outfit', sans-serif; font-size: 4rem; font-weight: 800; color: var(--jaune); line-height: 1; }
    .jeu-aide { font-size: 0.82rem !important; color: rgba(255,255,255,0.65) !important; }
    /* Choix du personnage */
    #jeu-accueil { gap: 10px; overflow-y: auto; justify-content: safe center; }
    .jeu-perso-titre strong { color: var(--jaune); }
    .jeu-persos { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; width: 100%; max-width: 330px; }
    .jeu-persos button { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 4px 2px 6px; border: 2px solid transparent; border-radius: 10px;
        background: rgba(255,255,255,0.1); color: #fff; font-family: inherit; font-size: 0.72rem; font-weight: 600; cursor: pointer; transition: background 0.15s, transform 0.15s; }
    .jeu-persos button:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); }
    .jeu-persos button.choisi { border-color: var(--jaune); background: rgba(240,196,25,0.22); }
    .jeu-persos button:focus-visible { outline: 2px solid #fff; outline-offset: 1px; }
    .jeu-persos canvas { display: block; width: 52px; height: auto; }
    @media (max-width: 420px) { .jeu-persos canvas { width: 42px; } .jeu-ecran h2 { font-size: 1.35rem; } .jeu-aide { display: none; } }
    .jeu-fleches { display: none; gap: 12px; margin-top: 12px; }
    .jeu-fleches button { flex: 1; padding: 16px; font-size: 1.6rem; border: none; border-radius: 12px; background: var(--vert); color: #fff; touch-action: none; user-select: none; -webkit-user-select: none; }
    .jeu-fleches button:active { background: var(--vert-dark); }
    @media (hover: none), (max-width: 700px) { .jeu-fleches { display: flex; } }

    .jeu-classement { background: var(--white); border: 1px solid var(--border); border-radius: 14px; padding: 22px; box-shadow: var(--shadow-card); }
    .jeu-classement h2 { font-size: 1.2rem; margin-bottom: 14px; }
    #jeu-classement { list-style: none; }
    #jeu-classement li { display: flex; align-items: center; gap: 10px; padding: 8px 6px; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
    #jeu-classement li:last-child { border-bottom: none; }
    #jeu-classement li.moi { background: #FFF6D6; border-radius: 8px; font-weight: 700; }
    #jeu-classement .rang { width: 28px; text-align: center; font-weight: 700; color: var(--muted); }
    #jeu-classement .pseudo { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    #jeu-classement .points { font-weight: 700; color: var(--vert); }
    #jeu-classement .jeu-vide { justify-content: center; color: var(--muted); border: none; text-align: center; }
    .jeu-classement-note { margin-top: 12px; font-size: 0.8rem; color: var(--muted); }
    @media (max-width: 860px) { .jeu-grille { grid-template-columns: minmax(0, 420px); } }
</style>
<?php $extraHead = ob_get_clean(); ?>
<!DOCTYPE html>
<html lang="fr">
<?php include '../templates/head.php'; ?>
<body>

<?php
$activePage = 'jeux';
$navScrolled = 'scrolled';
include '../templates/navbar.php';
?>

<section class="page-hero page-hero--jeux">
    <div class="container">
        <h1><?= $info['icone'] ?> <?= htmlspecialchars($info['titre']) ?></h1>
        <p><?= htmlspecialchars($info['description']) ?></p>
    </div>
</section>

<section class="section-jeu" id="jeu" data-api="<?= BASE_URL ?>/api/jeux.php" data-jeu="<?= $jeu ?>">
    <div class="container"><a href="<?= BASE_URL ?>/jeux.php" class="jeu-retour">← Tous les jeux</a></div>
    <div class="container jeu-grille">
        <div class="jeu-zone">
            <div class="jeu-hud">
                <div><div class="label">Échanges</div><div class="valeur" id="jeu-score">0</div></div>
                <div style="text-align:right"><div class="label">Mon record</div><div class="valeur" id="jeu-record">0</div></div>
            </div>
            <div class="jeu-cadre">
                <canvas id="jeu-canvas" width="400" height="600" tabindex="0" aria-label="Court de tennis du jeu"></canvas>

                <div class="jeu-ecran" id="jeu-accueil">
                    <h2>🎾 Prêt pour l'échange ?</h2>
                    <p class="jeu-perso-titre">Votre joueur : <strong id="jeu-perso-nom"></strong></p>
                    <div class="jeu-persos" id="jeu-persos" role="radiogroup" aria-label="Choix du personnage"></div>
                    <form id="jeu-form" autocomplete="off">
                        <input type="text" id="jeu-pseudo" maxlength="<?= JEU_PSEUDO_MAX ?>" placeholder="Votre pseudo" aria-label="Votre pseudo" required>
                        <div class="jeu-erreur" id="jeu-pseudo-erreur" role="alert"></div>
                        <button type="submit" class="btn-primary">Jouer</button>
                    </form>
                    <p class="jeu-aide">Ordinateur : flèches ← → du clavier<br>Téléphone : boutons ← → ou glisser le doigt sur le court</p>
                </div>

                <div class="jeu-ecran" id="jeu-fin" hidden>
                    <h2 id="jeu-fin-titre">Balle out !</h2>
                    <div class="jeu-gros-score" id="jeu-fin-score">0</div>
                    <p>échange(s) réussi(s)</p>
                    <p id="jeu-fin-statut" role="status"></p>
                    <button type="button" class="btn-primary" id="jeu-rejouer">Rejouer</button>
                    <button type="button" class="btn-secondaire" id="jeu-changer">Changer de joueur ou de pseudo</button>
                </div>
            </div>
            <div class="jeu-fleches" aria-hidden="true">
                <button type="button" data-jeu-sens="gauche">←</button>
                <button type="button" data-jeu-sens="droite">→</button>
            </div>
        </div>

        <aside class="jeu-classement">
            <h2>🏆 Meilleurs joueurs</h2>
            <ol id="jeu-classement"><li class="jeu-vide">Chargement…</li></ol>
            <p class="jeu-classement-note">Top <?= JEU_TOP ?> : le meilleur score de chaque joueur.</p>
            <p class="jeu-classement-note">Personnages caricaturaux et humoristiques, sans lien officiel avec les joueurs cités.</p>
        </aside>
    </div>
</section>

<?php include '../templates/footer.php'; ?>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/personnages.js"></script>
<script src="<?= BASE_URL ?>/assets/js/jeu-echange.js"></script>
</body>
</html>
