<?php
require_once 'auth.php';
require_once __DIR__ . '/_personnes.php';

page_personnes([
    'table'               => 'entraineurs',
    'page'                => 'entraineurs',
    'titre'               => 'Entraîneurs',
    'icone'               => '🎾',
    'dossier_upload'      => UPLOAD_ENTRAINEURS,
    'dossier_url'         => 'entraineurs',
    'prefixe_photo'       => 'coach_',
    'journal'             => 'entraineur',
    'personne'            => "l'entraîneur",
    'de_personne'         => "de l'entraîneur",
    'nouveau'             => 'Ajouter un entraîneur',
    'label_role'          => 'Rôle / spécialité',
    'exemple_role'        => 'ex : Enfants & Pôle compétition',
    'exemple_description' => 'ex : Cours collectifs enfants, stages vacances...',
]);
