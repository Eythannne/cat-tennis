<?php
// Équipe administrative du club (président, secrétaire...) affichée sur la page « Le club »
require_once 'auth.php';
require_once __DIR__ . '/_personnes.php';

page_personnes([
    'table'               => 'bureau',
    'page'                => 'bureau',
    'titre'               => 'Bureau',
    'icone'               => '🏛️',
    'dossier_upload'      => UPLOAD_BUREAU,
    'dossier_url'         => 'bureau',
    'prefixe_photo'       => 'bureau_',
    'journal'             => 'bureau',
    'personne'            => 'le membre du bureau',
    'de_personne'         => 'du membre du bureau',
    'nouveau'             => 'Ajouter un membre du bureau',
    'label_role'          => 'Fonction',
    'exemple_role'        => 'ex : Président',
    'exemple_description' => 'ex : Joueur au club depuis 1995, en charge des relations avec la mairie...',
    'suggestions_role'    => ['Président', 'Présidente', 'Vice-président', 'Vice-présidente', 'Secrétaire',
                              'Secrétaire adjoint(e)', 'Trésorier', 'Trésorière', 'Trésorier adjoint(e)', 'Membre du bureau'],
]);
