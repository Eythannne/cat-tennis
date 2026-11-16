-- Migration du 28/10/2026 — journal des actions importantes (page "Journal", fondateur uniquement)
-- Fonctionne sur la base LOCALE (MySQL) comme EN LIGNE (MariaDB). Relançable sans risque.
-- Utilisation en ligne : phpMyAdmin → cliquer sur la base if0_..._cat_tennis → onglet SQL → coller → Go.

CREATE TABLE IF NOT EXISTS journal (
    id          INT NOT NULL AUTO_INCREMENT,
    admin_id    INT NULL DEFAULT NULL,             -- NULL : action sans compte connecté (ex : échec de connexion)
    admin_nom   VARCHAR(150) NOT NULL DEFAULT '',  -- nom au moment de l'action (reste lisible si le compte est supprimé)
    categorie   VARCHAR(30)  NOT NULL,             -- connexion, compte, parametres, article, evenement...
    description VARCHAR(255) NOT NULL,
    ip          VARCHAR(45)  NOT NULL DEFAULT '',
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_created (created_at),
    KEY idx_categorie (categorie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
