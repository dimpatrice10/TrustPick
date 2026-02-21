-- ============================================================
-- TRUSTPICK V2 - Schema Complet avec CAU & Système de Parrainage
-- Date: 24 janvier 2026
-- Monnaie: FCFA (Franc CFA)
-- ============================================================

DROP DATABASE IF EXISTS trustpick_v2;
CREATE DATABASE trustpick_v2 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE trustpick_v2;

-- ============================================================
-- 1. UTILISATEURS & AUTHENTIFICATION
-- ============================================================

-- Table des utilisateurs avec système CAU
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cau VARCHAR(20) NOT NULL UNIQUE COMMENT 'Code d''Accès Utilisateur unique',
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  role ENUM('super_admin', 'admin_entreprise', 'user') DEFAULT 'user',
  company_id INT DEFAULT NULL COMMENT 'Entreprise de rattachement (NULL pour super_admin)',
  balance DECIMAL(12,2) DEFAULT 0 COMMENT 'Solde en FCFA',
  referral_code VARCHAR(15) NOT NULL UNIQUE COMMENT 'Code de parrainage unique',
  referred_by INT DEFAULT NULL COMMENT 'ID de l''utilisateur parrain',
  is_active BOOLEAN DEFAULT TRUE,
  created_by INT DEFAULT NULL COMMENT 'ID de l''admin qui a créé ce compte',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL,
  
  INDEX idx_cau (cau),
  INDEX idx_referral (referral_code),
  INDEX idx_company (company_id),
  INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historique des connexions
CREATE TABLE login_history (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  ip_address VARCHAR(45),
  user_agent TEXT,
  login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_login (user_id, login_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. ENTREPRISES
-- ============================================================

CREATE TABLE companies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  logo VARCHAR(255) DEFAULT NULL,
  description TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  created_by INT DEFAULT NULL COMMENT 'Super admin qui a créé l''entreprise',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_slug (slug),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Liaison utilisateur-entreprise (pour gérer plusieurs admins par entreprise)
ALTER TABLE users 
ADD CONSTRAINT fk_users_company 
FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL;

-- ============================================================
-- 3. CATÉGORIES DE PRODUITS
-- ============================================================

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(120) NOT NULL UNIQUE,
  icon VARCHAR(50) DEFAULT NULL COMMENT 'Nom d''icône CSS ou emoji',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. PRODUITS
-- ============================================================

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  category_id INT DEFAULT NULL,
  title VARCHAR(250) NOT NULL,
  slug VARCHAR(270) NOT NULL,
  description TEXT,
  price DECIMAL(12,2) DEFAULT 0 COMMENT 'Prix en FCFA',
  image VARCHAR(255) DEFAULT NULL,
  is_auto_generated BOOLEAN DEFAULT FALSE COMMENT 'Généré automatiquement ou manuellement',
  is_active BOOLEAN DEFAULT TRUE,
  views_count INT DEFAULT 0,
  created_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_company (company_id),
  INDEX idx_category (category_id),
  INDEX idx_active (is_active),
  INDEX idx_auto_gen (is_auto_generated),
  UNIQUE KEY unique_slug_company (slug, company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. AVIS & INTERACTIONS
-- ============================================================

-- Avis utilisateurs
CREATE TABLE reviews (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  title VARCHAR(200) DEFAULT NULL,
  body TEXT NOT NULL,
  likes_count INT DEFAULT 0,
  dislikes_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_product (user_id, product_id) COMMENT 'Un utilisateur = 1 seul avis par produit',
  INDEX idx_product (product_id),
  INDEX idx_user (user_id),
  INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Likes/Dislikes sur les avis
CREATE TABLE review_reactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  review_id BIGINT NOT NULL,
  user_id INT NOT NULL,
  reaction_type ENUM('like', 'dislike') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_review (user_id, review_id) COMMENT 'Un utilisateur = 1 réaction par avis',
  INDEX idx_review (review_id),
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. RECOMMANDATIONS
-- ============================================================

CREATE TABLE recommendations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  recommender_id INT NOT NULL COMMENT 'Utilisateur qui recommande',
  recommended_to_id INT NOT NULL COMMENT 'Utilisateur qui reçoit la recommandation',
  message TEXT DEFAULT NULL,
  is_viewed BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (recommender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (recommended_to_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_receiver (recommended_to_id, is_viewed),
  INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. SYSTÈME DE TÂCHES
-- ============================================================

-- Définition des tâches
CREATE TABLE tasks_definitions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'leave_review, recommend_product, etc.',
  task_name VARCHAR(150) NOT NULL,
  description TEXT,
  reward_amount DECIMAL(10,2) NOT NULL COMMENT 'Récompense en FCFA',
  is_daily BOOLEAN DEFAULT TRUE COMMENT 'Peut être fait chaque jour',
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tâches complétées par utilisateur
CREATE TABLE user_tasks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  task_id INT NOT NULL,
  completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reward_earned DECIMAL(10,2) NOT NULL COMMENT 'Montant gagné en FCFA',
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (task_id) REFERENCES tasks_definitions(id) ON DELETE CASCADE,
  INDEX idx_user_task (user_id, task_id),
  INDEX idx_completed (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. SYSTÈME DE PARRAINAGE
-- ============================================================

CREATE TABLE referrals (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  referrer_id INT NOT NULL COMMENT 'Parrain',
  referred_id INT NOT NULL COMMENT 'Filleul',
  reward_amount DECIMAL(10,2) DEFAULT 0 COMMENT 'Bonus de parrainage en FCFA',
  is_rewarded BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  rewarded_at TIMESTAMP NULL,
  
  FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (referred_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_referred (referred_id) COMMENT 'Un utilisateur ne peut être parrainé qu''une fois',
  INDEX idx_referrer (referrer_id),
  INDEX idx_rewarded (is_rewarded)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. TRANSACTIONS & PORTEFEUILLE
-- ============================================================

CREATE TABLE transactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('reward', 'referral', 'withdrawal', 'bonus', 'penalty') NOT NULL,
  amount DECIMAL(12,2) NOT NULL COMMENT 'Montant en FCFA (+ ou -)',
  description VARCHAR(255),
  reference_id BIGINT DEFAULT NULL COMMENT 'ID de référence (task, review, etc.)',
  reference_type VARCHAR(50) DEFAULT NULL COMMENT 'Type: task, review, referral, etc.',
  balance_after DECIMAL(12,2) NOT NULL COMMENT 'Solde après transaction',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_type (user_id, type),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Retraits
CREATE TABLE withdrawals (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL COMMENT 'Montant en FCFA',
  phone_number VARCHAR(20) NOT NULL COMMENT 'Numéro Mobile Money',
  status ENUM('pending', 'approved', 'completed', 'rejected') DEFAULT 'pending',
  notes TEXT DEFAULT NULL,
  processed_by INT DEFAULT NULL COMMENT 'Admin qui a traité',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at TIMESTAMP NULL,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_status (user_id, status),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. NOTIFICATIONS
-- ============================================================

CREATE TABLE notifications (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('task_reminder', 'new_product', 'new_review', 'reward', 'referral', 'withdrawal', 'system') NOT NULL,
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  link VARCHAR(255) DEFAULT NULL COMMENT 'Lien vers la ressource concernée',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_read (user_id, is_read),
  INDEX idx_type (type),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. LOGS & AUDIT
-- ============================================================

CREATE TABLE activity_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  action VARCHAR(100) NOT NULL COMMENT 'create_user, delete_product, etc.',
  entity_type VARCHAR(50) DEFAULT NULL COMMENT 'user, product, company, etc.',
  entity_id BIGINT DEFAULT NULL,
  details TEXT DEFAULT NULL COMMENT 'JSON ou texte descriptif',
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_action (user_id, action),
  INDEX idx_entity (entity_type, entity_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. CONFIGURATION SYSTÈME
-- ============================================================

CREATE TABLE system_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  description VARCHAR(255),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DONNÉES DE SEED
-- ============================================================

-- Catégories
INSERT INTO categories (name, slug, icon) VALUES
('Électronique', 'electronique', '📱'),
('Mode & Accessoires', 'mode-accessoires', '👗'),
('Maison & Jardin', 'maison-jardin', '🏠'),
('Alimentation', 'alimentation', '🍔'),
('Santé & Beauté', 'sante-beaute', '💄'),
('Sports & Loisirs', 'sports-loisirs', '⚽'),
('Livres & Culture', 'livres-culture', '📚'),
('Automobile', 'automobile', '🚗');

-- Configuration système
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('referral_reward', '5000', 'Récompense de parrainage en FCFA'),
('min_deposit', '10', 'Montant minimum de dépôt en FCFA'),
('min_withdrawal', '5000', 'Montant minimum de retrait en FCFA'),
('daily_notifications_count', '2', 'Nombre minimum de notifications par jour'),
('products_generation_frequency', '3', 'Nombre de générations de produits par jour'),
('review_reward', '500', 'Récompense pour un avis en FCFA');

-- Super Admin par défaut (CAU: ADMIN001)
INSERT INTO users (cau, name, phone, role, balance, referral_code, is_active, created_by) VALUES
('ADMIN001', 'Super Administrateur', '+22500000000', 'super_admin', 0, 'ADMIN001REF', TRUE, NULL);

-- Entreprises de démonstration
INSERT INTO companies (name, slug, logo, description, created_by) VALUES
('TechnoPlus CI', 'technoplus-ci', 'assets/img/companies/technoplus.png', 'Leader de l''électronique en Côte d''Ivoire', 1),
('Mode Afrique', 'mode-afrique', 'assets/img/companies/mode-afrique.png', 'Vêtements et accessoires africains modernes', 1),
('BioMarket', 'biomarket', 'assets/img/companies/biomarket.png', 'Produits bio et naturels', 1);

-- Admin entreprise pour TechnoPlus (CAU: TECH001)
INSERT INTO users (cau, name, phone, role, company_id, balance, referral_code, is_active, created_by) VALUES
('TECH001', 'Kouassi Admin', '+22501010101', 'admin_entreprise', 1, 0, 'TECH001REF', TRUE, 1);

-- Utilisateurs de test
INSERT INTO users (cau, name, phone, role, company_id, balance, referral_code, referred_by, is_active, created_by) VALUES
('USER001', 'Ama Kouadio', '+22502020202', 'user', NULL, 2500, 'AMA2024REF', NULL, TRUE, 2),
('USER002', 'Yao Koffi', '+22503030303', 'user', NULL, 1000, 'YAO2024REF', NULL, TRUE, 2);

-- Définitions des tâches
INSERT INTO tasks_definitions (task_code, task_name, description, reward_amount, is_daily, is_active) VALUES
('leave_review', 'Laisser un avis', 'Rédiger un avis détaillé sur un produit', 500, TRUE, TRUE),
('recommend_product', 'Recommander un produit', 'Recommander un produit à un ami', 200, TRUE, TRUE),
('like_review', 'Aimer un avis', 'Liker l''avis d''un autre utilisateur', 50, TRUE, TRUE),
('invite_user', 'Inviter un utilisateur', 'Inviter quelqu''un via votre lien de parrainage', 1000, FALSE, TRUE),
('daily_login', 'Connexion quotidienne', 'Se connecter chaque jour', 100, TRUE, TRUE);

-- Produits de démonstration pour TechnoPlus
INSERT INTO products (company_id, category_id, title, slug, description, price, image, is_auto_generated, created_by) VALUES
(1, 1, 'Smartphone Galaxy Pro 12', 'smartphone-galaxy-pro-12', 'Dernier smartphone avec écran AMOLED 6.7" et caméra 108MP', 450000, 'assets/img/products/smartphone-1.jpg', FALSE, 2),
(1, 1, 'Laptop UltraBook X15', 'laptop-ultrabook-x15', 'PC portable professionnel Intel i7, 16GB RAM, SSD 512GB', 650000, 'assets/img/products/laptop-1.jpg', FALSE, 2),
(1, 1, 'Écouteurs Wireless Pro', 'ecouteurs-wireless-pro', 'Écouteurs Bluetooth avec réduction de bruit active', 45000, 'assets/img/products/ecouteurs-1.jpg', FALSE, 2);

-- ============================================================
-- INDEX COMPLÉMENTAIRES POUR PERFORMANCE
-- ============================================================

-- Référence utilisateur-parrain (pour requêtes rapides)
ALTER TABLE users ADD INDEX idx_referred_by (referred_by);

-- Recherche full-text sur produits
ALTER TABLE products ADD FULLTEXT idx_fulltext_products (title, description);

-- Recherche full-text sur avis
ALTER TABLE reviews ADD FULLTEXT idx_fulltext_reviews (title, body);

-- ============================================================
-- FIN DU SCHEMA
-- ============================================================
