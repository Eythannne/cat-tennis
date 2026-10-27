<?php
// Point d'entrée commun : connexion BDD + fonctions utilitaires.
// À inclure à la place de config/database.php.
require_once __DIR__ . '/config/database.php';

date_default_timezone_set('Europe/Paris');

// ── Localisation du club (carte de la page Contact) ──────────────────────────
const ADRESSE_DEFAUT = 'Stade du Moulin Neuf, 4 avenue du Maréchal Juin, 93600 Aulnay-sous-Bois';
const CLUB_LAT = 48.936124;
const CLUB_LNG = 2.503562;

// ── Chiffres clés par défaut (modifiables dans Admin → Paramètres) ───────────
const CHIFFRES_DEFAUT = [
    1 => ['6',    'Courts couverts'],
    2 => ['300+', 'Adhérents'],
    3 => ['1975', 'Année de création'],
];

const MOIS_FR = ['01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
                 '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
                 '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'];

// ── Adresses complètes (partage sur les réseaux, sitemap) ─────────────────────
function url_site($chemin = '') {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/' . ltrim($chemin, '/');
}

// ── Types d'événements du calendrier (libellé + couleur) ─────────────────────
const EVENT_TYPES = [
    'tournoi'   => ['label' => 'Tournoi',            'couleur' => '#1B6B2F'],
    'cours'     => ['label' => 'Cours / Stage',      'couleur' => '#D4A800'],
    'evenement' => ['label' => 'Animation',          'couleur' => '#2E86C1'],
    'vacances'  => ['label' => 'Vacances scolaires', 'couleur' => '#884EA0'],
    'autre'     => ['label' => 'Autre',              'couleur' => '#7F8C8D'],
];

// ── Sujets du formulaire de contact ──────────────────────────────────────────
const SUJETS_CONTACT = [
    'inscription' => 'Inscription au club',
    'cours'       => 'Cours & entraînements',
    'tournoi'     => 'Tournois & compétitions',
    'reservation' => 'Réservation de courts',
    'autre'       => 'Autre',
];

// ── Paramètres du site (table parametres, modifiables dans l'admin) ──────────
// Valeur telle qu'enregistrée (null si le paramètre n'existe pas encore)
function param_brut($cle) {
    global $pdo;
    static $params = null;
    if ($params === null) {
        $params = $pdo->query("SELECT cle, valeur FROM parametres")->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $params[$cle] ?? null;
}

// Valeur du paramètre, ou $defaut s'il est absent ou vide
function param($cle, $defaut = '') {
    $valeur = param_brut($cle);
    return $valeur !== null && $valeur !== '' ? $valeur : $defaut;
}

// ── Limite d'actions par adresse IP (anti brute-force / anti spam) ───────────
// Les horodatages sont stockés dans un petit fichier temporaire par IP et par usage.
function limite_fichier($usage) {
    return sys_get_temp_dir() . '/cat_' . $usage . '_' . md5($_SERVER['REMOTE_ADDR'] ?? '') . '.json';
}
function limite_atteinte($usage, $max, $fenetre) {
    $fichier = limite_fichier($usage);
    $horodatages = is_file($fichier) ? (json_decode(file_get_contents($fichier), true) ?: []) : [];
    $recents = array_filter($horodatages, fn($t) => $t > time() - $fenetre);
    return count($recents) >= $max;
}
function limite_enregistrer($usage, $fenetre) {
    $fichier = limite_fichier($usage);
    $horodatages = is_file($fichier) ? (json_decode(file_get_contents($fichier), true) ?: []) : [];
    $horodatages = array_values(array_filter($horodatages, fn($t) => $t > time() - $fenetre));
    $horodatages[] = time();
    file_put_contents($fichier, json_encode($horodatages));
}
function limite_reinitialiser($usage) {
    @unlink(limite_fichier($usage));
}

// ── Dates des événements (en français) ───────────────────────────────────────
function jour_mois($ts) {
    return date('j', $ts) . ' ' . mb_strtolower(MOIS_FR[date('m', $ts)]);
}
function heure_fr($ts) {
    return date('G\hi', $ts);
}
// Texte court d'un événement : "14h00 – 18h00", "Du 21 au 24 décembre", ...
function quand_evenement($e) {
    $debut = strtotime($e['date_debut']);
    $fin   = $e['date_fin'] ? strtotime($e['date_fin']) : null;
    $aHeure = fn($ts) => date('H:i', $ts) !== '00:00';

    if (!$fin || date('Y-m-d', $debut) === date('Y-m-d', $fin)) {
        if (!$aHeure($debut)) return 'Toute la journée';
        return heure_fr($debut) . ($fin && $aHeure($fin) ? ' – ' . heure_fr($fin) : '');
    }
    $jourDebut = date('m', $debut) === date('m', $fin) ? date('j', $debut) : jour_mois($debut);
    return 'Du ' . $jourDebut . ' au ' . jour_mois($fin);
}

// ── Mise en forme des articles ───────────────────────────────────────────────
// Syntaxe simple (proche du Markdown) saisie dans l'admin :
//   **gras**  *italique*  ## Titre  - liste  1. liste  [texte](https://lien)
// Le texte est échappé AVANT la mise en forme : aucun HTML saisi ne passe.
function format_inline($ligne) {
    $ligne = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $ligne);
    $ligne = preg_replace('/(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])/', '<em>$1</em>', $ligne);
    // Liens : seulement http(s) et mailto
    return preg_replace_callback('/\[([^\]]+)\]\(((?:https?:\/\/|mailto:)[^\s)]+)\)/', function ($m) {
        $externe = str_starts_with($m[2], 'http') ? ' target="_blank" rel="noopener"' : '';
        return '<a href="' . $m[2] . '"' . $externe . '>' . $m[1] . '</a>';
    }, $ligne);
}

function format_article($texte) {
    $lignes = explode("\n", str_replace("\r\n", "\n", htmlspecialchars($texte)));
    $html = '';
    $paragraphe = [];
    $liste = null;      // 'ul' ou 'ol' quand on est dans une liste

    $fermerParagraphe = function () use (&$html, &$paragraphe) {
        if ($paragraphe) $html .= '<p>' . implode('<br>', $paragraphe) . "</p>\n";
        $paragraphe = [];
    };
    $fermerListe = function () use (&$html, &$liste) {
        if ($liste) $html .= "</$liste>\n";
        $liste = null;
    };

    foreach ($lignes as $ligne) {
        $ligne = rtrim($ligne);
        if (preg_match('/^#{2,3}\s+(.+)$/', $ligne, $m)) {
            $fermerParagraphe(); $fermerListe();
            $html .= '<h2>' . format_inline($m[1]) . "</h2>\n";
        } elseif (preg_match('/^\s*(?:[-*•]\s+|(\d+)[.)]\s+)(.+)$/', $ligne, $m)) {
            $type = $m[1] !== '' ? 'ol' : 'ul';
            $fermerParagraphe();
            if ($liste !== $type) { $fermerListe(); $html .= "<$type>"; $liste = $type; }
            $html .= '<li>' . format_inline($m[2]) . '</li>';
        } elseif (trim($ligne) === '') {
            $fermerParagraphe(); $fermerListe();
        } else {
            $fermerListe();
            $paragraphe[] = format_inline($ligne);
        }
    }
    $fermerParagraphe(); $fermerListe();
    return $html;
}

// Version texte seul (extraits sur les cartes, description Google)
function texte_brut($texte, $longueur = null) {
    $texte = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $texte);
    $texte = preg_replace('/^\s*(#{2,3}|[-*•]|\d+[.)])\s+/m', '', $texte);
    $texte = trim(preg_replace('/\s+/', ' ', str_replace(['**', '*'], '', $texte)));
    return $longueur ? mb_strimwidth($texte, 0, $longueur, '…') : $texte;
}

// Chemin (relatif à uploads/galerie/) de la vignette d'une photo, ou la photo elle-même
// si la vignette n'existe pas (photos envoyées avant l'ajout des vignettes)
function miniature_galerie($fichier) {
    $fichier = basename($fichier);
    return is_file(__DIR__ . '/../public/assets/uploads/galerie/miniatures/' . $fichier) ? 'miniatures/' . $fichier : $fichier;
}

// Initiales d'un entraîneur (affichées quand il n'a pas de photo)
function initiales($e) {
    return mb_strtoupper(mb_substr($e['prenom'], 0, 1) . mb_substr($e['nom'] ?: mb_substr($e['prenom'], 1), 0, 1));
}

// Articles visibles publiquement : publiés et dont la date de publication est passée
function sql_article_visible() {
    return "publie = 1 AND published_at <= '" . date('Y-m-d H:i:s') . "'";
}
