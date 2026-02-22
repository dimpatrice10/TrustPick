-- =================================================
-- TrustPick V2 - Migration: Ajout tâche dépôt 1000 FCFA
-- =================================================
-- Exécuter ce script après le schéma principal

-- Vérifier si la tâche existe déjà avant d'insérer
INSERT INTO tasks_definitions (task_code, task_name, description, reward_amount, is_daily, is_active)
SELECT 'deposit_5000', 
       'Effectuer un dépôt (1 000 FCFA min)', 
       'Déposez au moins 1 000 FCFA comme preuve de transaction avant de pouvoir poster un avis.',
       0, -- Pas de récompense directe, c''est une condition
       TRUE, 
       TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM tasks_definitions WHERE task_code = 'deposit_5000'
);

-- Mettre à jour les autres tâches obligatoires si nécessaires
UPDATE tasks_definitions SET is_daily = TRUE, is_active = TRUE 
WHERE task_code IN ('leave_review', 'like_review', 'recommend_product')
AND is_daily = FALSE;

-- Vérifier que toutes les tâches obligatoires existent
-- leave_review
INSERT INTO tasks_definitions (task_code, task_name, description, reward_amount, is_daily, is_active)
SELECT 'leave_review', 
       'Laisser un avis sur un produit', 
       'Donnez votre avis sur un produit et gagnez une récompense.',
       500, 
       TRUE, 
       TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM tasks_definitions WHERE task_code = 'leave_review'
);

-- like_review
INSERT INTO tasks_definitions (task_code, task_name, description, reward_amount, is_daily, is_active)
SELECT 'like_review', 
       'Aimer un avis existant', 
       'Aimez l\'avis d\'un autre utilisateur pour gagner une récompense.',
       200, 
       TRUE, 
       TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM tasks_definitions WHERE task_code = 'like_review'
);

-- recommend_product
INSERT INTO tasks_definitions (task_code, task_name, description, reward_amount, is_daily, is_active)
SELECT 'recommend_product', 
       'Recommander un produit', 
       'Recommandez un produit de qualité à la communauté.',
       300, 
       TRUE, 
       TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM tasks_definitions WHERE task_code = 'recommend_product'
);

-- Afficher les tâches configurées
SELECT id, task_code, task_name, reward_amount, is_daily, is_active 
FROM tasks_definitions 
ORDER BY id;
