-- Migration du 27/10/2026
-- À exécuter une fois sur chaque base (local + prod) via phpMyAdmin.

-- 1. Encodage complet (emojis dans les messages de contact, etc.)
ALTER TABLE admins           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE articles         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE evenements       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE galerie          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE messages_contact CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE parametres       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Date de publication des articles (brouillon → publié, publication programmée)
ALTER TABLE articles ADD COLUMN published_at DATETIME NULL DEFAULT NULL AFTER publie;
UPDATE articles SET published_at = created_at WHERE publie = 1;

-- 3. Nouveau paramètre : horaires du secrétariat (affichés sur la page Contact)
INSERT IGNORE INTO parametres (cle, valeur) VALUES ('horaires', 'Lundi – Vendredi : 14h – 18h\nSamedi : 9h – 12h');

-- 4. Nouveau type d'événement "vacances" (la couleur dépend maintenant du type)
ALTER TABLE evenements MODIFY type ENUM('tournoi','cours','evenement','vacances','autre')
    COLLATE utf8mb4_unicode_ci DEFAULT 'autre';

-- 5. Entraîneurs gérés depuis l'admin (page Le club)
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

INSERT INTO entraineurs (prenom, nom, role, description, ordre)
SELECT 'Ethan', '', 'Enfants & Pôle compétition', 'Cours collectifs enfants, stages vacances et suivi des jeunes compétiteurs.', 1
WHERE NOT EXISTS (SELECT 1 FROM entraineurs);

-- 6. Adresse complète du club
INSERT INTO parametres (cle, valeur) VALUES ('adresse', 'Stade du Moulin Neuf, 4 avenue du Maréchal Juin, 93600 Aulnay-sous-Bois')
ON DUPLICATE KEY UPDATE valeur = VALUES(valeur);

-- 7. Tarifs gérés depuis l'admin (page Le club) + fiche d'inscription PDF
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

INSERT IGNORE INTO parametres (cle, valeur) VALUES ('saison', '2026-2027'), ('fiche_inscription', '');
