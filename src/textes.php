<?php
// ── Textes du site modifiables dans Admin → Paramètres → « Textes du site » ──
// Chaque texte : clé => [libellé dans l'admin, texte par défaut, 'long' pour un texte sur plusieurs lignes].
// Le texte saisi est stocké dans la table parametres sous la clé "texte_<clé>".
// Vide (ou identique au texte par défaut) = texte par défaut : il suit donc les évolutions du code.

const TEXTES_SITE = [
    'accueil' => ['🏠 Accueil', [
        'accueil_hero_titre'      => ['Grand titre (bannière)', 'Club Aulnaysien de Tennis'],
        'accueil_hero_sous_titre' => ['Sous-titre (bannière)', 'Rejoignez-nous à Aulnay-sous-Bois'],
        'accueil_hero_bouton'     => ['Bouton de la bannière', 'Nous rejoindre'],
        'accueil_club_etiquette'  => ['Présentation — étiquette', 'Le club'],
        'accueil_club_titre'      => ['Présentation — titre', 'Bienvenue au CAT'],
        'accueil_club_texte'      => ['Présentation — texte', 'Fondé à Aulnay-sous-Bois, le Club Aulnaysien de Tennis accueille joueurs de tous niveaux, des débutants aux compétiteurs. Cours collectifs, tournois internes, animations jeunes — il y en a pour tout le monde.', 'long'],
        'accueil_club_bouton'     => ['Présentation — bouton', 'En savoir plus'],
        'accueil_actus_etiquette' => ['Actualités — étiquette', 'Actualités'],
        'accueil_actus_titre'     => ['Actualités — titre', 'Les dernières nouvelles du club'],
        'accueil_agenda_etiquette'=> ['Agenda — étiquette', 'Agenda'],
        'accueil_agenda_titre'    => ['Agenda — titre', 'Prochains événements'],
        'accueil_resa_titre'      => ['Réservation — titre', 'Réservez votre court'],
        'accueil_resa_texte'      => ['Réservation — texte', 'La réservation des courts se fait via la plateforme Ten\'up, le service officiel de la Fédération Française de Tennis.', 'long'],
        'accueil_resa_bouton'     => ['Réservation — bouton', 'Réserver sur Ten\'up'],
    ]],
    'club' => ['🎾 Le club', [
        'club_hero_titre'         => ['Titre (bannière)', 'Le club'],
        'club_hero_sous_titre'    => ['Sous-titre (bannière)', 'Notre histoire, nos terrains, nos entraîneurs'],
        'club_histoire_etiquette' => ['Histoire — étiquette', 'Notre histoire'],
        'club_histoire_titre'     => ['Histoire — titre', 'Un club ancré dans Aulnay-sous-Bois'],
        'club_terrains_etiquette' => ['Terrains — étiquette', 'Installations'],
        'club_terrains_titre'     => ['Terrains — titre', 'Nos terrains'],
        'club_terrain1_icone'     => ['Terrain 1 — icône (emoji)', '🏟️'],
        'club_terrain1_titre'     => ['Terrain 1 — titre', 'Courts couverts'],
        'club_terrain1_texte'     => ['Terrain 1 — texte', '6 courts en dur couverts, disponibles toute l\'année quelle que soit la météo.', 'long'],
        'club_terrain2_icone'     => ['Terrain 2 — icône (emoji)', '☀️'],
        'club_terrain2_titre'     => ['Terrain 2 — titre', 'Courts extérieurs'],
        'club_terrain2_texte'     => ['Terrain 2 — texte', 'Courts en accès libre selon disponibilité. Réservation via Ten\'up recommandée.', 'long'],
        'club_terrain3_icone'     => ['Terrain 3 — icône (emoji)', '🏃'],
        'club_terrain3_titre'     => ['Terrain 3 — titre', 'Espace échauffement'],
        'club_terrain3_texte'     => ['Terrain 3 — texte', 'Zone dédiée à l\'échauffement et aux exercices spécifiques tennis.', 'long'],
        'club_bureau_etiquette'   => ['Bureau — étiquette', 'Le bureau'],
        'club_bureau_titre'       => ['Bureau — titre', 'L\'équipe administrative'],
        'club_coachs_etiquette'   => ['Entraîneurs — étiquette', 'L\'équipe'],
        'club_coachs_titre'       => ['Entraîneurs — titre', 'Nos entraîneurs'],
        'club_tarifs_etiquette'   => ['Tarifs — étiquette', 'Adhésion'],
        'club_tarifs_note'        => ['Tarifs — note sous les cartes (le téléphone s\'ajoute tout seul)', 'Renseignements au secrétariat du club'],
    ]],
    'pages' => ['📄 Bannières des autres pages', [
        'actualites_titre'        => ['Actualités — titre', 'Actualités'],
        'actualites_sous_titre'   => ['Actualités — sous-titre', 'Articles, événements et galerie photos du club'],
        'calendrier_titre'        => ['Calendrier — titre', 'Calendrier'],
        'calendrier_sous_titre'   => ['Calendrier — sous-titre', 'Animations, cours, tournois et événements du club'],
        'contact_titre'           => ['Contact — titre', 'Contact'],
        'contact_sous_titre'      => ['Contact — sous-titre', 'Une question ? Écrivez-nous'],
        'mentions_titre'          => ['Mentions légales — titre', 'Mentions légales'],
        'mentions_sous_titre'     => ['Mentions légales — sous-titre', 'Informations légales et politique de confidentialité'],
    ]],
    'contact' => ['✉️ Page Contact', [
        'contact_trouver_titre'   => ['Titre de la colonne d\'informations', 'Nous trouver'],
        'contact_resa_titre'      => ['Réservation — intitulé', 'Réservation courts'],
        'contact_resa_lien'       => ['Réservation — texte du lien', 'Via Ten\'up →'],
        'contact_bouton'          => ['Bouton du formulaire', 'Envoyer le message'],
    ]],
    'divers' => ['🔗 Liens et page d\'erreur', [
        'lien_reservation'        => ['Adresse du site de réservation (boutons et liens Ten\'up)', 'https://tenup.fft.fr/'],
        'erreur404_titre'         => ['Page 404 — titre', 'Cette page est hors-jeu'],
        'erreur404_texte'         => ['Page 404 — texte', 'La page que vous cherchez n\'existe pas ou a été déplacée.', 'long'],
    ]],
];

// Définition d'un texte (libellé, défaut, type) à partir de sa clé
function texte_definition($cle) {
    static $toutes = null;
    if ($toutes === null) {
        $toutes = [];
        foreach (TEXTES_SITE as [, $champs]) $toutes += $champs;
    }
    return $toutes[$cle] ?? null;
}

// Texte brut (à échapper à l'affichage) : celui saisi dans l'admin, sinon le texte par défaut
function texte($cle) {
    $def = texte_definition($cle);
    return param('texte_' . $cle, $def[1] ?? '');
}

// Texte prêt à afficher dans une page : échappé, retours à la ligne conservés
function t($cle) {
    return nl2br(htmlspecialchars(texte($cle)));
}
