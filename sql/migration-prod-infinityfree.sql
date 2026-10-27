-- ════════════════════════════════════════════════════════════════════════════
-- Mise à jour de la base EN LIGNE (InfinityFree, MariaDB 11.4) — 27/10/2026
--
-- La base en ligne avait été créée avec une version plus ancienne du projet
-- (pas de colonne evenements.type, galerie.categorie, articles.updated_at...).
-- Ce script l'aligne sur la structure actuelle SANS RIEN SUPPRIMER.
--
-- Relançable sans risque : chaque étape vérifie si elle a déjà été faite
-- (syntaxe "IF NOT EXISTS" propre à MariaDB : ne pas l'exécuter sur le MySQL de Laragon,
--  qui utilise sql/migration-2026-10-27.sql).
--
-- Utilisation : phpMyAdmin → cliquer sur la base if0_..._cat_tennis → onglet SQL → coller → Go.
-- ════════════════════════════════════════════════════════════════════════════

-- 1. Encodage complet (emojis dans les messages de contact, etc.)
ALTER TABLE admins           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE articles         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE evenements       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE galerie          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE messages_contact CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE parametres       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Articles : date de publication + date de modification (sitemap)
ALTER TABLE articles ADD COLUMN IF NOT EXISTS published_at DATETIME NULL DEFAULT NULL AFTER publie;
ALTER TABLE articles ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
UPDATE articles SET published_at = created_at WHERE publie = 1 AND published_at IS NULL;

-- 3. Événements : type (la couleur et la légende du calendrier en dépendent)
ALTER TABLE evenements ADD COLUMN IF NOT EXISTS type ENUM('tournoi','cours','evenement','vacances','autre')
    COLLATE utf8mb4_unicode_ci DEFAULT 'autre' AFTER couleur;
ALTER TABLE evenements MODIFY type ENUM('tournoi','cours','evenement','vacances','autre')
    COLLATE utf8mb4_unicode_ci DEFAULT 'autre';

-- 4. Galerie : catégorie (filtres de la galerie)
ALTER TABLE galerie ADD COLUMN IF NOT EXISTS categorie VARCHAR(100) NULL DEFAULT NULL AFTER fichier;

-- 5. Paramètres : une seule ligne par clé (indispensable pour que l'enregistrement fonctionne)
CREATE UNIQUE INDEX IF NOT EXISTS cle_unique ON parametres (cle);
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('horaires', 'Lundi – Vendredi : 14h – 18h\nSamedi : 9h – 12h');
INSERT INTO parametres (cle, valeur) VALUES ('adresse', 'Stade du Moulin Neuf, 4 avenue du Maréchal Juin, 93600 Aulnay-sous-Bois')
    ON DUPLICATE KEY UPDATE valeur = VALUES(valeur);
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('saison', '2026-2027'), ('fiche_inscription', '');

-- 6. Entraîneurs (page Le club)
CREATE TABLE IF NOT EXISTS entraineurs (
    id          INT NOT NULL AUTO_INCREMENT,
    prenom      VARCHAR(100) NOT NULL,
    nom         VARCHAR(100) NOT NULL DEFAULT '',
    role        VARCHAR(150) DEFAULT NULL,
    description TEXT,
    photo       VARCHAR(255) DEFAULT NULL,
    ordre       INT DEFAULT 0,
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tarifs (page Le club)
CREATE TABLE IF NOT EXISTS tarifs (
    id           INT NOT NULL AUTO_INCREMENT,
    nom          VARCHAR(100) NOT NULL,
    public_cible VARCHAR(150) DEFAULT NULL,
    prix         VARCHAR(50)  NOT NULL DEFAULT '',
    periode      VARCHAR(30)  DEFAULT '/an',
    avantages    TEXT,
    mis_en_avant TINYINT(1)   DEFAULT 0,
    ordre        INT          DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tarifs (nom, public_cible, prix, periode, avantages, mis_en_avant, ordre)
SELECT * FROM (
    SELECT 'Enfant' AS nom, 'Moins de 18 ans' AS public_cible, 'À définir' AS prix, '/an' AS periode,
           'Cotisation FFT incluse\nAccès aux cours collectifs\nTournois internes\nAnimations vacances' AS avantages, 0 AS mis_en_avant, 1 AS ordre
    UNION ALL SELECT 'Adulte', '18 ans et plus', 'À définir', '/an',
           'Cotisation FFT incluse\nAccès aux cours collectifs\nTournois internes\nCompétitions par équipes', 1, 2
    UNION ALL SELECT 'Famille', '2 adultes + enfants', 'À définir', '/an',
           'Cotisations FFT incluses\nAccès cours collectifs\nTournois internes\nTarif préférentiel', 0, 3
) AS depart
WHERE NOT EXISTS (SELECT 1 FROM tarifs);
