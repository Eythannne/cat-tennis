-- Migration du 28/10/2026 — équipe administrative du club (page admin "Bureau", section sur "Le club")
-- Fonctionne sur la base LOCALE (MySQL) comme EN LIGNE (MariaDB). Relançable sans risque.
-- Utilisation en ligne : phpMyAdmin → cliquer sur la base if0_..._cat_tennis → onglet SQL → coller → Go.

CREATE TABLE IF NOT EXISTS bureau (
    id          INT NOT NULL AUTO_INCREMENT,
    prenom      VARCHAR(100) NOT NULL,
    nom         VARCHAR(100) NOT NULL DEFAULT '',
    role        VARCHAR(150) DEFAULT NULL,      -- fonction : Président, Secrétaire, Trésorier...
    description TEXT,
    photo       VARCHAR(255) DEFAULT NULL,
    ordre       INT DEFAULT 0,
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
