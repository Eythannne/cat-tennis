-- ════════════════════════════════════════════════════════════════════════════
-- Schéma complet de la base du site CAT
-- Sert à créer une base NEUVE (nouveau PC, nouvel hébergeur...).
-- Pour une base existante, utiliser plutôt les fichiers sql/migration-*.sql.
--
-- Utilisation : phpMyAdmin → sélectionner la base vide → onglet "Importer".
-- ⚠ À tenir à jour : après chaque migration, régénérer ce fichier.
-- ════════════════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;

CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('fondateur','administrateur','moderateur') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'moderateur',
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publie` tinyint(1) DEFAULT '0',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `entraineurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `role` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `evenements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime DEFAULT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#1A5276',
  `type` enum('tournoi','cours','evenement','vacances','autre') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'autre',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `galerie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `messages_contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sujet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lu` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `parametres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cle` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valeur` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cle` (`cle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tarifs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_cible` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prix` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `periode` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '/an',
  `avantages` text COLLATE utf8mb4_unicode_ci,
  `mis_en_avant` tinyint(1) DEFAULT '0',
  `ordre` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `bureau` (
  `id` int NOT NULL AUTO_INCREMENT,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `role` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `journal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int DEFAULT NULL,
  `admin_nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `categorie` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_categorie` (`categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── Données de départ ───────────────────────────────────────────────────────
INSERT INTO parametres (cle, valeur) VALUES
    ('nom_club',      'Club Aulnaysien de Tennis'),
    ('adresse',       'Stade du Moulin Neuf, 4 avenue du Maréchal Juin, 93600 Aulnay-sous-Bois'),
    ('email_contact', 'contact@cat-tennis.fr'),
    ('telephone',     ''),
    ('horaires',      'Lundi – Vendredi : 14h – 18h\nSamedi : 9h – 12h'),
    ('description',   'Club de tennis à Aulnay-sous-Bois'),
    ('saison',        '2026-2027'),
    ('fiche_inscription', '');

-- ── Premier compte administrateur ───────────────────────────────────────────
-- Le mot de passe est stocké chiffré. Pour obtenir le hash d'un mot de passe,
-- dans un terminal Laragon :  php -r "echo password_hash('MonMotDePasse', PASSWORD_DEFAULT);"
-- puis remplacer COLLER_LE_HASH_ICI ci-dessous et décommenter la ligne.
-- (le premier compte est le fondateur : accès à tout, ni modifiable ni supprimable par les admins)
-- INSERT INTO admins (nom, role, email, mot_de_passe) VALUES ('Admin', 'fondateur', 'admin@exemple.fr', 'COLLER_LE_HASH_ICI');
