-- ============================================================
-- MIGRATION: Super Admin Task Management
-- Ajout de colonnes pour gestion avancée des tâches
-- ============================================================

-- Nouvelles colonnes booléennes et dates
ALTER TABLE tasks_definitions
    ADD COLUMN is_repeatable BOOLEAN DEFAULT TRUE COMMENT 'La tâche peut être refaite chaque jour' AFTER is_active,
    ADD COLUMN is_available_anytime BOOLEAN DEFAULT TRUE COMMENT 'Disponible sans restriction de dates' AFTER is_repeatable,
    ADD COLUMN is_ignorable BOOLEAN DEFAULT FALSE COMMENT 'Peut être ignorée dans la progression' AFTER is_available_anytime,
    ADD COLUMN start_date DATE DEFAULT NULL COMMENT 'Date de début (si non disponible tout le temps)' AFTER is_ignorable,
    ADD COLUMN end_date DATE DEFAULT NULL COMMENT 'Date de fin (si non disponible tout le temps)' AFTER start_date,
    ADD COLUMN task_order INT DEFAULT 0 COMMENT 'Ordre d''affichage et de progression' AFTER end_date,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Initialiser task_order à partir de l'ordre existant dans le code
UPDATE tasks_definitions SET task_order = 1, is_repeatable = TRUE, is_ignorable = FALSE WHERE task_code = 'daily_login';
UPDATE tasks_definitions SET task_order = 2, is_repeatable = TRUE, is_ignorable = FALSE WHERE task_code = 'leave_review';
UPDATE tasks_definitions SET task_order = 3, is_repeatable = TRUE, is_ignorable = FALSE WHERE task_code = 'like_review';
UPDATE tasks_definitions SET task_order = 4, is_repeatable = TRUE, is_ignorable = FALSE WHERE task_code = 'recommend_product';
UPDATE tasks_definitions SET task_order = 5, is_repeatable = FALSE, is_ignorable = TRUE WHERE task_code = 'deposit_5000';
UPDATE tasks_definitions SET task_order = 6, is_repeatable = FALSE, is_ignorable = TRUE WHERE task_code = 'invite_user';

-- Index pour tri et filtrage
ALTER TABLE tasks_definitions ADD INDEX idx_task_order (task_order);
ALTER TABLE tasks_definitions ADD INDEX idx_active_order (is_active, task_order);
