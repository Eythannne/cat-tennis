-- Migration du 28/10/2026 — rôles des comptes admin (administrateur / modérateur)
-- Base LOCALE (MySQL de Laragon). Pour la base en ligne : sql/migration-prod-2026-10-28-roles.sql
--
-- Les comptes existants deviennent administrateurs (personne ne perd ses accès),
-- les comptes créés ensuite sont modérateurs par défaut.

ALTER TABLE admins ADD COLUMN role ENUM('administrateur','moderateur') NOT NULL DEFAULT 'administrateur' AFTER nom;
ALTER TABLE admins ALTER COLUMN role SET DEFAULT 'moderateur';
