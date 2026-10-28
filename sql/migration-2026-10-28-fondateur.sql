-- Migration du 28/10/2026 — rôle "fondateur" (compte unique, ni modifiable ni supprimable par les admins)
-- Base LOCALE (MySQL de Laragon). Pour la base en ligne : sql/migration-prod-2026-10-28-fondateur.sql
-- À exécuter APRÈS migration-2026-10-28-roles.sql.

ALTER TABLE admins MODIFY role ENUM('fondateur','administrateur','moderateur')
    COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'moderateur';

-- Le fondateur : un seul compte, désigné par son email
UPDATE admins SET role = 'fondateur' WHERE email = 'ethanpassard@gmail.com';
