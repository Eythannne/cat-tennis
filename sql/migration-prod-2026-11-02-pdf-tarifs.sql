-- Migration du 02/11/2026 — une fiche d'inscription PDF par formule de tarif
-- Base EN LIGNE (InfinityFree, MariaDB). Relançable sans risque.
-- Utilisation : phpMyAdmin → cliquer sur la base if0_..._cat_tennis → onglet SQL → coller → Go.

ALTER TABLE tarifs ADD COLUMN IF NOT EXISTS pdf VARCHAR(255) DEFAULT NULL AFTER mis_en_avant;
