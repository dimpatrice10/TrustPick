-- =================================================
-- TrustPick V2 - Migration: Système de Likes sur Avis
-- =================================================

-- Table pour stocker les likes sur les avis (nouveau système simplifié)
CREATE TABLE IF NOT EXISTS review_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    review_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_like (user_id, review_id),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajouter colonne likes_count à la table reviews si elle n'existe pas
-- Cette commande est idempotente (ne fait rien si la colonne existe déjà)
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS likes_count INT DEFAULT 0;

fk_users_company
companies_ibfk_1

-- Index pour performances
CREATE INDEX IF NOT EXISTS idx_review_likes_user ON review_likes(user_id);
CREATE INDEX IF NOT EXISTS idx_review_likes_review ON review_likes(review_id);

-- Synchroniser les compteurs depuis review_reactions (si la table existe)
-- Exécuter uniquement si vous migrez depuis review_reactions
-- UPDATE reviews r SET likes_count = (
--     SELECT COUNT(*) FROM review_reactions rr 
--     WHERE rr.review_id = r.id AND rr.reaction_type = 'like'
-- ) WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'review_reactions');

-- Migrer les données de review_reactions vers review_likes (optionnel)
-- INSERT IGNORE INTO review_likes (user_id, review_id, created_at)
-- SELECT user_id, review_id, created_at FROM review_reactions WHERE reaction_type = 'like';

-- Vérification
SELECT 'Table review_likes créée avec succès' AS status;
SELECT COUNT(*) AS total_likes FROM review_likes;
