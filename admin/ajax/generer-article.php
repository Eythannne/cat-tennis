<?php
// ✨ Générer (page Articles) : rédige le contenu d'un article avec Gemini
// à partir du titre et des notes brutes. Répond en JSON : {texte} ou {erreur}.
const ADMIN_AJAX = true;
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../src/ia.php';

const IA_NOTES_MAX         = 8000;   // caractères
const IA_GENERATIONS_HEURE = 20;     // par adresse IP, pour protéger le quota de la clé

header('Content-Type: application/json; charset=utf-8');

function repondre($donnees, $code = 200) {
    http_response_code($code);
    exit(json_encode($donnees, JSON_UNESCAPED_UNICODE));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') repondre(['erreur' => 'Requête invalide.'], 405);

$titre = trim($_POST['titre'] ?? '');
$notes = trim(str_replace("\r\n", "\n", $_POST['notes'] ?? ''));
if ($titre === '' || $notes === '') {
    repondre(['erreur' => 'Renseignez le titre et quelques notes avant de générer.'], 422);
}
if (mb_strlen($notes) > IA_NOTES_MAX) {
    repondre(['erreur' => 'Notes trop longues (' . IA_NOTES_MAX . ' caractères maximum).'], 422);
}

$cle = param('gemini_api_key');
if ($cle === '') {
    repondre(['erreur' => est_fondateur()
        ? 'Aucune clé API Gemini : ajoutez-la dans Paramètres > Intelligence artificielle.'
        : "La génération n'est pas encore configurée : demandez au fondateur d'ajouter la clé API Gemini."], 503);
}

if (limite_atteinte('ia_article', IA_GENERATIONS_HEURE, 3600)) {
    repondre(['erreur' => 'Trop de générations en une heure : réessayez un peu plus tard.'], 429);
}
limite_enregistrer('ia_article', 3600);

// La génération peut durer : on libère la session pour ne pas bloquer les autres pages de l'admin
session_write_close();

$resultat = gemini_generer_article($cle, $titre, $notes);
repondre($resultat, isset($resultat['erreur']) ? 502 : 200);
