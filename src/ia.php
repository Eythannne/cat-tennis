<?php
// ── Génération d'articles par IA (API Gemini de Google) ──────────────────────
// La clé API, le modèle et la consigne sont stockés dans la table parametres
// (admin > Paramètres > Intelligence artificielle), jamais dans le code.

defined('GEMINI_API_BASE') || define('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta/models/');
// Alias de Google qui suivent les derniers modèles (les anciens sont arrêtés au fil du temps).
// « lite » : rapide (quelques secondes), sans temps de réflexion, largement suffisant pour un article.
const GEMINI_MODELE_DEFAUT = 'gemini-flash-lite-latest';
// Essayés dans l'ordre si le modèle choisi est surchargé ou trop lent (une nouvelle tentative
// sur le même modèle réussit souvent : les surcharges de Google sont brèves)
const GEMINI_MODELES_SECOURS = ['gemini-flash-lite-latest', 'gemini-flash-latest'];
const GEMINI_DELAI       = 25;            // secondes au maximum par tentative
const GEMINI_DELAI_TOTAL = 45;            // secondes au maximum pour l'ensemble des tentatives

const PROMPT_IA_ARTICLES_DEFAUT = "Tu es rédacteur pour le Club Aulnaysien de Tennis (CAT). À partir du titre et des informations suivantes, rédige un article complet, dynamique et en français, avec une introduction, des paragraphes bien structurés et une conclusion. Adopte un ton convivial et professionnel.";

// Ajouté à la consigne de l'admin : le texte doit respecter la syntaxe de format_article()
const IA_CONSIGNE_FORMAT = <<<TXT


Contraintes de mise en forme (obligatoires) :
- Réponds uniquement avec le corps de l'article : pas de titre principal, pas de phrase du type « Voici l'article », aucun commentaire.
- Sépare les paragraphes par une ligne vide.
- Intertitres éventuels : sur leur propre ligne, commençant par « ## ».
- Mise en valeur possible : **gras**, *italique*, listes avec « - » en début de ligne. Aucune autre syntaxe (pas de HTML, pas de tableau).
- N'invente aucune information précise (date, horaire, prix, lieu, nom, score) qui ne figure pas dans les informations fournies.
TXT;

function gemini_modele() {
    return param('gemini_modele', GEMINI_MODELE_DEFAUT);
}

// Appel à l'API : [code HTTP, réponse JSON décodée], ou [0, message] si le serveur est injoignable
function gemini_requete($cle, $chemin, $corps = null, $delai = GEMINI_DELAI) {
    $ch = curl_init(GEMINI_API_BASE . $chemin);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'x-goog-api-key: ' . $cle],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => $delai,
    ]);
    if ($corps !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($corps));
    }
    $reponse = curl_exec($ch);
    $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erreur  = curl_error($ch);

    if ($reponse === false) return [0, $erreur];
    return [$code, json_decode($reponse, true) ?: []];
}

// Message compréhensible pour une réponse en erreur
function gemini_message_erreur($code, $reponse) {
    $detail = is_array($reponse) ? ($reponse['error']['message'] ?? '') : (string)$reponse;
    if ($code === 0 && stripos($detail, 'timed out') !== false) return 'Google Gemini met trop de temps à répondre (serveurs surchargés) : réessayez dans quelques minutes.';
    if ($code === 0)   return "Impossible de joindre Google Gemini ($detail). Réessayez dans un instant.";
    if ($code === 400 && stripos($detail, 'API key') !== false) return 'Clé API Gemini invalide : le fondateur doit la remplacer dans Paramètres > Intelligence artificielle.';
    if ($code === 401 || $code === 403) return "Clé API Gemini refusée par Google (droits insuffisants ou API non activée).";
    if ($code === 404) return "Le modèle « " . gemini_modele() . " » n'existe pas ou plus : le fondateur doit le changer dans Paramètres > Intelligence artificielle.";
    if ($code === 429) return 'Quota Gemini atteint : réessayez dans quelques minutes.';
    if ($code >= 500)  return 'Google Gemini est indisponible pour le moment : réessayez dans quelques minutes.';
    return "Erreur Gemini ($code)" . ($detail !== '' ? " : $detail" : '');
}

// Vérifie une clé et un modèle (enregistrement des Paramètres).
// null = valides ; ['erreur' => ...] = refusés ; ['avertissement' => ...] = vérification impossible
function gemini_verifier($cle, $modele) {
    [$code, $reponse] = gemini_requete($cle, rawurlencode($modele), null, 15);
    if ($code === 200) return null;
    if ($code === 0 || $code >= 500 || $code === 429) {
        return ['avertissement' => 'La clé n\'a pas pu être vérifiée auprès de Google pour le moment. Elle est enregistrée quand même.'];
    }
    if ($code === 404) return ['erreur' => "Le modèle « $modele » n'existe pas chez Google. Vérifiez son nom (ex : gemini-flash-latest)."];
    return ['erreur' => gemini_message_erreur($code, $reponse)];
}

// Rédige un article : ['texte' => ...] ou ['erreur' => ...]
function gemini_generer_article($cle, $titre, $notes) {
    $consigne = param('prompt_ia_articles', PROMPT_IA_ARTICLES_DEFAUT) . IA_CONSIGNE_FORMAT;
    $corps = [
        'system_instruction' => ['parts' => [['text' => $consigne]]],
        'contents' => [[
            'role'  => 'user',
            'parts' => [['text' => "Titre de l'article : $titre\n\nInformations et notes :\n$notes"]],
        ]],
        'generationConfig' => ['temperature' => 0.7],
    ];
    // Modèle choisi, puis les modèles de secours si Google est surchargé, trop lent ou en panne,
    // tant qu'il reste assez de temps (l'hébergeur coupe les scripts trop longs)
    $debut = time();
    foreach (array_merge([gemini_modele()], GEMINI_MODELES_SECOURS) as $modele) {
        $reste = GEMINI_DELAI_TOTAL - (time() - $debut);
        if ($reste < 5) break;
        [$code, $reponse] = gemini_requete($cle, rawurlencode($modele) . ':generateContent', $corps, min(GEMINI_DELAI, $reste));
        if ($code === 200 || ($code !== 0 && $code !== 429 && $code < 500)) break;
    }
    if ($code !== 200) return ['erreur' => gemini_message_erreur($code, $reponse)];

    $candidat = $reponse['candidates'][0] ?? [];
    $texte = implode('', array_map(fn($p) => $p['text'] ?? '', $candidat['content']['parts'] ?? []));
    if (trim($texte) === '') {
        $raison = $candidat['finishReason'] ?? ($reponse['promptFeedback']['blockReason'] ?? '');
        return ['erreur' => in_array($raison, ['SAFETY', 'PROHIBITED_CONTENT', 'BLOCKLIST', 'OTHER'], true)
            ? 'Gemini a refusé de rédiger ce texte. Reformulez les notes et réessayez.'
            : 'Gemini a renvoyé une réponse vide. Réessayez.'];
    }
    return ['texte' => ia_nettoyer_texte($texte, $titre)];
}

// Remet le texte dans la syntaxe de format_article() si le modèle s'en est écarté
function ia_nettoyer_texte($texte, $titre) {
    $texte = trim(str_replace("\r\n", "\n", $texte));
    $texte = preg_replace('/^```[a-z]*\n|\n```$/i', '', $texte);              // bloc de code autour du texte
    $lignes = explode("\n", $texte);
    // Première ligne = titre de l'article répété : on la retire
    $premiere = trim(preg_replace('/^#+\s*|\*+/', '', $lignes[0] ?? ''));
    if (preg_match('/^#\s/', $lignes[0] ?? '') || mb_strtolower($premiere) === mb_strtolower(trim($titre))) {
        array_shift($lignes);
    }
    $texte = implode("\n", $lignes);
    $texte = preg_replace('/^#{1,6}\s+/m', '## ', $texte);                      // tous les intertitres en ##
    return trim(preg_replace("/\n{3,}/", "\n\n", $texte));
}
