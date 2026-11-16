-- Migration du 28/10/2026 — rôle "fondateur" (compte unique, ni modifiable ni supprimable par les admins)
-- Base LOCALE (MySQL de Laragon). Pour la base en ligne : 2026-10-28_2-fondateur.prod.sql
-- À exécuter APRÈS 2026-10-28_1-roles.local.sql.

ALTER TABLE admins MODIFY role ENUM('fondateur','administrateur','moderateur')
    COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'moderateur';

-- Le fondateur : un seul compte, désigné par son email
UPDATE admins SET role = 'fondateur' WHERE email = 'ethanpassard@gmail.com';
