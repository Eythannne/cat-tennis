-- Migration du 19/11/2026 — scores des jeux de l'espace Jeux (page jeux.php)
-- Une seule table pour tous les jeux : la colonne « jeu » indique le jeu (voir JEUX dans src/jeux.php).
-- Fonctionne sur la base LOCALE (MySQL) comme EN LIGNE (MariaDB). Relançable sans risque.
-- Utilisation en ligne : phpMyAdmin → cliquer sur la base if0_..._cat_tennis → onglet SQL → coller → Go.

CREATE TABLE IF NOT EXISTS jeux_scores (
    id          INT NOT NULL AUTO_INCREMENT,
    jeu         VARCHAR(30) NOT NULL,             -- identifiant du jeu (ex : echange)
    pseudo      VARCHAR(20) NOT NULL,
    score       INT NOT NULL,
    duree       INT NOT NULL DEFAULT 0,           -- durée de la partie en secondes (mesurée par le serveur)
    membre_id   INT NULL DEFAULT NULL,            -- futur espace membre : NULL = partie en invité
    jeton       CHAR(32) NOT NULL,                -- identifiant unique de la partie (un seul score par partie)
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY jeton (jeton),
    KEY idx_jeu_score (jeu, score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
