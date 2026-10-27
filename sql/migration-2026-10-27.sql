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
