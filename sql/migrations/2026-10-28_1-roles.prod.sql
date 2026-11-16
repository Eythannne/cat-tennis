-- Migration du 28/10/2026 — rôles des comptes admin (administrateur / modérateur)
-- Base EN LIGNE (InfinityFree, MariaDB). Relançable sans risque.
--
-- Utilisation : phpMyAdmin → cliquer sur la base if0_..._cat_tennis → onglet SQL → coller → Go.
--
-- Les comptes existants deviennent administrateurs (personne ne perd ses accès),
-- les comptes créés ensuite sont modérateurs par défaut.
-- (Pas de UPDATE : relancer le script ne repasse jamais un modérateur en administrateur.)

ALTER TABLE admins ADD COLUMN IF NOT EXISTS role ENUM('administrateur','moderateur') NOT NULL DEFAULT 'administrateur' AFTER nom;
ALTER TABLE admins ALTER COLUMN role SET DEFAULT 'moderateur';
