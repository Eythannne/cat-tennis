-- Migration du 28/10/2026 — rôle "fondateur" (compte unique, ni modifiable ni supprimable par les admins)
-- Base EN LIGNE (InfinityFree, MariaDB). Relançable sans risque.
-- À exécuter APRÈS migration-prod-2026-10-28-roles.sql.
--
-- Utilisation : phpMyAdmin → cliquer sur la base if0_..._cat_tennis → onglet SQL → coller → Go.
-- ⚠ Si ton compte en ligne utilise un autre email, remplace-le dans la dernière ligne.

ALTER TABLE admins MODIFY role ENUM('fondateur','administrateur','moderateur')
    COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'moderateur';

-- Le fondateur : un seul compte, désigné par son email
UPDATE admins SET role = 'fondateur' WHERE email = 'ethanpassard@gmail.com';
