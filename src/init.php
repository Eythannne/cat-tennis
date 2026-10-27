<?php
// Point d'entrée commun : connexion BDD + fonctions utilitaires.
// À inclure à la place de config/database.php.
require_once __DIR__ . '/config/database.php';

date_default_timezone_set('Europe/Paris');

// ── Localisation du club (carte de la page Contact) ──────────────────────────
const ADRESSE_DEFAUT = 'Stade du Moulin Neuf, 6 avenue du Maréchal Juin, 93600 Aulnay-sous-Bois';
const CLUB_LAT = 48.937439;
const CLUB_LNG = 2.505437;

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
function param($cle, $defaut = '') {
    global $pdo;
    static $params = null;
    if ($params === null) {
        $params = $pdo->query("SELECT cle, valeur FROM parametres")->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return ($params[$cle] ?? '') !== '' ? $params[$cle] : $defaut;
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

// Initiales d'un entraîneur (affichées quand il n'a pas de photo)
function initiales($e) {
    return mb_strtoupper(mb_substr($e['prenom'], 0, 1) . mb_substr($e['nom'] ?: mb_substr($e['prenom'], 1), 0, 1));
}

// Articles visibles publiquement : publiés et dont la date de publication est passée
function sql_article_visible() {
    return "publie = 1 AND published_at <= '" . date('Y-m-d H:i:s') . "'";
}
