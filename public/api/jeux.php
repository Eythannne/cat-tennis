<?php
// API de l'espace Jeux (voir src/jeux.php). Réponses JSON.
//   GET  ?action=classement&jeu=echange        → tableau des scores du jeu
//   POST action=debut, jeu                     → jeton de la partie qui commence
//   POST action=score, jeton, pseudo, score    → vérifie et enregistre le score (le jeu est dans le jeton)
require_once '../../src/init.php';
require_once '../../src/jeux.php';
header('Content-Type: application/json; charset=utf-8');

function repondre($donnees, $code = 200) {
    http_response_code($code);
    exit(json_encode($donnees, JSON_UNESCAPED_UNICODE));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'classement') {
    $jeu = $_GET['jeu'] ?? '';
    if (!jeu_info($jeu)) repondre(['erreur' => 'Jeu inconnu.'], 404);
    repondre(['classement' => jeu_classement($jeu)]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') repondre(['erreur' => 'Requête invalide.'], 405);

if ($action === 'debut') {
    $jeu = $_POST['jeu'] ?? '';
    if (!jeu_info($jeu)) repondre(['erreur' => 'Jeu inconnu.'], 404);
    if (limite_atteinte('jeu_debut', JEU_PARTIES_HEURE, 3600)) {
        repondre(['erreur' => 'Trop de parties en une heure : faites une pause et revenez plus tard !'], 429);
    }
    limite_enregistrer('jeu_debut', 3600);
    repondre(['jeton' => jeu_jeton_creer($jeu)]);
}

if ($action === 'score') {
    $partie = jeu_jeton_lire($_POST['jeton'] ?? '');
    if (!$partie) repondre(['erreur' => 'Partie inconnue : rechargez la page et rejouez.'], 400);
    $jeu  = $partie['jeu'];
    $info = jeu_info($jeu);

    [$pseudo, $erreur] = jeu_pseudo_valide($_POST['pseudo'] ?? '');
    if ($erreur) repondre(['erreur' => $erreur], 422);

    $score = filter_var($_POST['score'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => $info['score_max']]]);
    if ($score === false) repondre(['erreur' => 'Score invalide.'], 422);

    // Durée réelle de la partie, mesurée par le serveur (le navigateur ne peut pas la falsifier)
    $dureeMs = (int)round(microtime(true) * 1000) - $partie['debut'];
    if ($dureeMs > JEU_PARTIE_MAX * 1000) repondre(['erreur' => 'Partie expirée : rejouez pour enregistrer un score.'], 400);
    if ($score > intdiv(max(0, $dureeMs), $info['ms_par_point'])) repondre(['erreur' => 'Score refusé.'], 422);

    // Score nul : rien à enregistrer
    if ($score === 0) {
        repondre(['enregistre' => false, 'record' => jeu_record($jeu, $pseudo), 'classement' => jeu_classement($jeu)]);
    }

    if (limite_atteinte('jeu_score', JEU_SCORES_HEURE, 3600)) {
        repondre(['erreur' => 'Trop de scores envoyés en une heure : revenez plus tard !'], 429);
    }
    limite_enregistrer('jeu_score', 3600);

    try {
        $pdo->prepare("INSERT INTO jeux_scores (jeu, pseudo, score, duree, jeton) VALUES (?, ?, ?, ?, ?)")
            ->execute([$jeu, $pseudo, $score, intdiv($dureeMs, 1000), $partie['id']]);
    } catch (PDOException $e) {
        // Jeton déjà utilisé (contrainte UNIQUE) : un seul score par partie
        repondre(['erreur' => 'Ce score a déjà été enregistré.'], 409);
    }

    // Place du joueur dans le tableau (celle de son meilleur score)
    $classement = jeu_classement($jeu);
    $rang = null;
    foreach ($classement as $i => $ligne) {
        if (mb_strtolower($ligne['pseudo']) === mb_strtolower($pseudo)) { $rang = $i + 1; break; }
    }
    repondre(['enregistre' => true, 'record' => jeu_record($jeu, $pseudo), 'rang' => $rang, 'classement' => $classement]);
}

repondre(['erreur' => 'Action inconnue.'], 400);
