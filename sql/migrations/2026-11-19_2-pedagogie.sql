-- Migration du 19/11/2026 — page Pédagogie (entraîneurs) + rôle « Peda »
-- Fonctionne sur la base LOCALE (MySQL) comme EN LIGNE (MariaDB). Relançable sans risque.
-- Utilisation en ligne : phpMyAdmin → cliquer sur la base if0_..._cat_tennis → onglet SQL → coller → Go.

-- 1. Nouveau rôle « peda » pour les comptes admin (sans droits pour l'instant)
ALTER TABLE admins MODIFY role ENUM('fondateur','administrateur','moderateur','peda')
    COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'moderateur';

-- 2. Bibliothèque d'animations de club (olympiade, soirée jeux vidéo, galette des rois...)
CREATE TABLE IF NOT EXISTS peda_animations (
    id                  INT NOT NULL AUTO_INCREMENT,
    nom                 VARCHAR(150) NOT NULL,
    categorie           VARCHAR(20)  NOT NULL DEFAULT 'tennistique',  -- tennistique | extrasportif | autre
    categorie_autre     VARCHAR(100) NOT NULL DEFAULT '',             -- précision si « autre »
    lieu                VARCHAR(50)  NOT NULL DEFAULT '',             -- voir PEDA_LIEUX (src/pedagogie.php)
    lieu_precision      VARCHAR(150) NOT NULL DEFAULT '',
    niveaux             VARCHAR(100) NOT NULL DEFAULT '',             -- clés séparées par des virgules (blanc,violet...)
    ages                VARCHAR(60)  NOT NULL DEFAULT '',             -- clés séparées par des virgules (enfant,ado...)
    duree               VARCHAR(50)  NOT NULL DEFAULT '',
    deroulement         TEXT,
    prix                DECIMAL(8,2) NULL DEFAULT NULL,               -- coût de l'animation pour le club
    main_oeuvre         TINYINT(1)   NOT NULL DEFAULT 0,
    main_oeuvre_nb      INT          NULL DEFAULT NULL,               -- nombre de personnes nécessaires
    participation       TINYINT(1)   NOT NULL DEFAULT 0,
    participation_prix  DECIMAL(8,2) NULL DEFAULT NULL,               -- participation demandée aux membres
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Calendrier interne : animations planifiées sur l'année (indépendant du calendrier public)
CREATE TABLE IF NOT EXISTS peda_calendrier (
    id            INT NOT NULL AUTO_INCREMENT,
    animation_id  INT NULL DEFAULT NULL,          -- animation de la bibliothèque (NULL = titre libre)
    titre         VARCHAR(150) NOT NULL,
    date_debut    DATETIME NOT NULL,
    date_fin      DATETIME NULL DEFAULT NULL,
    notes         TEXT,                           -- reprises comme description si publié
    evenement_id  INT NULL DEFAULT NULL,          -- événement du calendrier public, si publié
    created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_date (date_debut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bibliothèque d'exercices de tennis
CREATE TABLE IF NOT EXISTS peda_exercices (
    id                INT NOT NULL AUTO_INCREMENT,
    titre             VARCHAR(150) NOT NULL,
    description       TEXT,
    utilite           TEXT,                       -- à quoi ça sert
    cas_usage         TEXT,                       -- dans quel cas l'utiliser
    points_attention  TEXT,                       -- ex : jeu de jambes du joueur
    created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La programmation annuelle (PDF + notes) et les critères de passage sont stockés dans la table
-- parametres (clés peda_programmation_pdf, peda_programmation_notes, peda_criteres_<niveau>).
