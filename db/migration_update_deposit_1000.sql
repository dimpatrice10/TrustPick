-- =================================================
-- TrustPick V2 - Migration: Mise à jour dépôt minimum à 1000 FCFA
-- =================================================
-- Exécuter ce script sur la base PostgreSQL de production (Render)

-- 1. Mettre à jour le setting min_deposit
UPDATE system_settings SET setting_value = '1000' WHERE setting_key = 'min_deposit';

-- 2. Mettre à jour la description de la tâche deposit_5000
UPDATE tasks_definitions 
SET task_name = 'Effectuer un dépôt (1 000 FCFA min)',
    description = 'Déposez au moins 1 000 FCFA comme preuve de transaction avant de pouvoir poster un avis.'
WHERE task_code = 'deposit_5000';

-- 3. S'assurer que la tâche deposit_5000 existe
INSERT INTO tasks_definitions (task_code, task_name, description, reward_amount, is_daily, is_active)
SELECT 'deposit_5000', 
       'Effectuer un dépôt (1 000 FCFA min)', 
       'Déposez au moins 1 000 FCFA comme preuve de transaction avant de pouvoir poster un avis.',
       0, TRUE, TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM tasks_definitions WHERE task_code = 'deposit_5000'
);

-- Vérification
SELECT setting_key, setting_value FROM system_settings WHERE setting_key = 'min_deposit';
SELECT task_code, task_name, description FROM tasks_definitions WHERE task_code = 'deposit_5000';
