<?php
// ── Espace Jeux : liste des jeux et règles communes côté serveur ─────────────
// Chaque jeu tourne dans le navigateur ; le serveur fournit un jeton au début de la partie,
// puis vérifie le score envoyé à la fin avant de l'enregistrer (table jeux_scores).
// Aucun cookie : le jeton est signé, il ne peut être ni inventé ni réutilisé.
//
// ➕ Ajouter un jeu : une entrée dans JEUX ci-dessous + sa page public/jeu-<identifiant>.php
//    (sur le modèle de jeu-echange.php) + son script. Il apparaît alors tout seul sur la page Jeux,
//    dans l'API des scores et dans l'admin.

const JEUX = [
    'echange' => [
        'titre'       => 'Un petit échange',
        'icone'       => '🎾',
        'description' => 'Renvoyez la balle le plus longtemps possible : elle accélère à chaque échange !',
        'page'        => 'jeu-echange.php',
        'unite'       => 'échanges',      // ce que compte le score
        // Anti-triche : un point ne peut pas être marqué plus vite que ça (balle à sa vitesse maximale,
        // un échange dure au minimum ~1 s), et score plafonné
        'ms_par_point' => 500,
        'score_max'    => 5000,
    ],
];

const JEU_TOP            = 15;          // nombre de joueurs dans le tableau des scores de chaque jeu
const JEU_PSEUDO_MIN     = 2;
const JEU_PSEUDO_MAX     = 16;
const JEU_PARTIE_MAX     = 3 * 3600;    // une partie expire au bout de 3 heures
const JEU_PARTIES_HEURE  = 200;         // limites par adresse IP (tous jeux confondus)
const JEU_SCORES_HEURE   = 60;

// Mots refusés dans les pseudos (comparés sans accents ni chiffres déguisés : « c0n » = « con »).
// Mots courts : refusés seulement en mot entier (pour ne pas bloquer « Connor » ou « Nicolas »).
const JEU_MOTS_INTERDITS = ['connard', 'connasse', 'salope', 'salaud', 'putain', 'pute', 'encule', 'enculer',
    'merde', 'batard', 'niquer', 'nique', 'bite', 'couille', 'chier', 'abruti', 'debile', 'nazi', 'hitler',
    'pedale', 'pede', 'tapette', 'bouffon', 'ordure'];
const JEU_MOTS_COURTS    = ['con', 'conne', 'fdp', 'ntm', 'tg', 'pd', 'cul', 'nik'];

// Infos d'un jeu, ou null s'il n'existe pas
function jeu_info($jeu) {
    return JEUX[$jeu] ?? null;
}

// Clé secrète qui signe les jetons, créée automatiquement au premier usage (table parametres)
function jeu_secret() {
    global $pdo;
    $secret = param_brut('jeux_secret');
    if ($secret) return $secret;
    $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES ('jeux_secret', ?) ON DUPLICATE KEY UPDATE valeur = valeur")
        ->execute([bin2hex(random_bytes(32))]);
    return $pdo->query("SELECT valeur FROM parametres WHERE cle = 'jeux_secret'")->fetchColumn();
}

// Jeton de début de partie : identifiant unique + jeu + heure de début (ms), signés
function jeu_jeton_creer($jeu) {
    $donnees = bin2hex(random_bytes(16)) . '.' . $jeu . '.' . (int)round(microtime(true) * 1000);
    return $donnees . '.' . hash_hmac('sha256', $donnees, jeu_secret());
}

// ['id' => ..., 'jeu' => ..., 'debut' => ms] si le jeton est authentique, sinon null
function jeu_jeton_lire($jeton) {
    $morceaux = explode('.', (string)$jeton);
    if (count($morceaux) !== 4) return null;
    [$id, $jeu, $debut, $signature] = $morceaux;
    if (!preg_match('/^[0-9a-f]{32}$/', $id) || !jeu_info($jeu) || !ctype_digit($debut)) return null;
    if (!hash_equals(hash_hmac('sha256', "$id.$jeu.$debut", jeu_secret()), $signature)) return null;
    return ['id' => $id, 'jeu' => $jeu, 'debut' => (int)$debut];
}

// Version comparable d'un texte : minuscules, sans accents, chiffres déguisés remplacés
function jeu_normaliser($texte) {
    $texte = mb_strtolower($texte);
    $texte = strtr($texte, ['à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','î'=>'i','ï'=>'i',
        'ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','0'=>'o','1'=>'i','3'=>'e','4'=>'a','5'=>'s','@'=>'a','$'=>'s']);
    return $texte;
}

// [pseudo nettoyé, null] ou [null, message d'erreur]
function jeu_pseudo_valide($pseudo) {
    $pseudo = trim(preg_replace('/\s+/u', ' ', (string)$pseudo));
    $longueur = mb_strlen($pseudo);
    if ($longueur < JEU_PSEUDO_MIN || $longueur > JEU_PSEUDO_MAX) {
        return [null, 'Le pseudo doit faire entre ' . JEU_PSEUDO_MIN . ' et ' . JEU_PSEUDO_MAX . ' caractères.'];
    }
    if (!preg_match('/^[\p{L}\p{N} ._\-]+$/u', $pseudo)) {
        return [null, 'Le pseudo ne peut contenir que des lettres, des chiffres, des espaces et . _ -'];
    }
    $normal = jeu_normaliser($pseudo);
    $colle  = preg_replace('/[^a-z]/', '', $normal);                    // « s.a.l.o.p.e » → « salope »
    $mots   = preg_split('/[^a-z]+/', $normal, -1, PREG_SPLIT_NO_EMPTY);
    foreach (JEU_MOTS_INTERDITS as $mot) {
        if (str_contains($colle, $mot)) return [null, 'Ce pseudo n\'est pas autorisé.'];
    }
    if (array_intersect($mots, JEU_MOTS_COURTS)) return [null, 'Ce pseudo n\'est pas autorisé.'];
    return [$pseudo, null];
}

// Tableau des scores d'un jeu : le meilleur score de chaque pseudo, du plus haut au plus bas
function jeu_classement($jeu, $limite = JEU_TOP) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT pseudo, score, created_at FROM jeux_scores WHERE jeu = ? ORDER BY score DESC, created_at ASC LIMIT 500");
    $stmt->execute([$jeu]);
    $classement = [];
    foreach ($stmt->fetchAll() as $l) {
        $cle = mb_strtolower($l['pseudo']);
        if (isset($classement[$cle])) continue;                        // déjà présent avec un meilleur score
        $classement[$cle] = ['pseudo' => $l['pseudo'], 'score' => (int)$l['score'], 'date' => date('d/m/Y', strtotime($l['created_at']))];
        if (count($classement) >= $limite) break;
    }
    return array_values($classement);
}

// Meilleur score d'un pseudo à un jeu (0 s'il n'y a jamais joué)
function jeu_record($jeu, $pseudo) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT MAX(score) FROM jeux_scores WHERE jeu = ? AND LOWER(pseudo) = LOWER(?)");
    $stmt->execute([$jeu, $pseudo]);
    return (int)$stmt->fetchColumn();
}

// Chiffres d'un jeu pour la page Jeux : nombre de joueurs et meilleur joueur
function jeu_resume($jeu) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT LOWER(pseudo)) FROM jeux_scores WHERE jeu = ?");
    $stmt->execute([$jeu]);
    $premier = jeu_classement($jeu, 1)[0] ?? null;
    return ['joueurs' => (int)$stmt->fetchColumn(), 'premier' => $premier];
}
