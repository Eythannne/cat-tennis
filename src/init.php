<?php
// Point d'entrée commun : connexion BDD + fonctions utilitaires.
// À inclure à la place de config/database.php.
require_once __DIR__ . '/config/database.php';

date_default_timezone_set('Europe/Paris');

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

// Articles visibles publiquement : publiés et dont la date de publication est passée
function sql_article_visible() {
    return "publie = 1 AND published_at <= '" . date('Y-m-d H:i:s') . "'";
}
