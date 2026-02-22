-- ============================================================
-- TRUSTPICK V2 - Schema PostgreSQL pour Render.com
-- Converti depuis MySQL pour compatibilité PostgreSQL
-- ============================================================

-- ============================================================
-- 1. UTILISATEURS & AUTHENTIFICATION
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  cau VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('super_admin', 'admin_entreprise', 'user')),
  company_id INT DEFAULT NULL,
  balance DECIMAL(12,2) DEFAULT 0,
  referral_code VARCHAR(15) NOT NULL UNIQUE,
  referred_by INT DEFAULT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL
);

CREATE INDEX IF NOT EXISTS idx_users_cau ON users(cau);
CREATE INDEX IF NOT EXISTS idx_users_referral ON users(referral_code);
CREATE INDEX IF NOT EXISTS idx_users_company ON users(company_id);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_referred_by ON users(referred_by);

-- Historique des connexions
CREATE TABLE IF NOT EXISTS login_history (
  id BIGSERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  ip_address VARCHAR(45),
  user_agent TEXT,
  login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_login_user ON login_history(user_id, login_at);

-- ============================================================
-- 2. ENTREPRISES
-- ============================================================

CREATE TABLE IF NOT EXISTS companies (
  id SERIAL PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  logo VARCHAR(255) DEFAULT NULL,
  description TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  created_by INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_companies_slug ON companies(slug);
CREATE INDEX IF NOT EXISTS idx_companies_active ON companies(is_active);

-- Ajouter FK après création de companies
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint WHERE conname = 'fk_users_company'
  ) THEN
    ALTER TABLE users ADD CONSTRAINT fk_users_company 
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL;
  END IF;
END
$$;

-- ============================================================
-- 3. CATÉGORIES DE PRODUITS
-- ============================================================

CREATE TABLE IF NOT EXISTS categories (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(120) NOT NULL UNIQUE,
  icon VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 4. PRODUITS
-- ============================================================

CREATE TABLE IF NOT EXISTS products (
  id SERIAL PRIMARY KEY,
  company_id INT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
  category_id INT DEFAULT NULL REFERENCES categories(id) ON DELETE SET NULL,
  title VARCHAR(250) NOT NULL,
  slug VARCHAR(270) NOT NULL,
  description TEXT,
  price DECIMAL(12,2) DEFAULT 0,
  image VARCHAR(255) DEFAULT NULL,
  is_auto_generated BOOLEAN DEFAULT FALSE,
  is_active BOOLEAN DEFAULT TRUE,
  views_count INT DEFAULT 0,
  created_by INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (slug, company_id)
);

CREATE INDEX IF NOT EXISTS idx_products_company ON products(company_id);
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_active ON products(is_active);

-- ============================================================
-- 5. AVIS & INTERACTIONS
-- ============================================================

CREATE TABLE IF NOT EXISTS reviews (
  id BIGSERIAL PRIMARY KEY,
  product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  rating SMALLINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  title VARCHAR(200) DEFAULT NULL,
  body TEXT NOT NULL,
  likes_count INT DEFAULT 0,
  dislikes_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (user_id, product_id)
);

CREATE INDEX IF NOT EXISTS idx_reviews_product ON reviews(product_id);
CREATE INDEX IF NOT EXISTS idx_reviews_user ON reviews(user_id);
CREATE INDEX IF NOT EXISTS idx_reviews_rating ON reviews(rating);

-- Likes/Dislikes sur les avis
CREATE TABLE IF NOT EXISTS review_reactions (
  id BIGSERIAL PRIMARY KEY,
  review_id BIGINT NOT NULL REFERENCES reviews(id) ON DELETE CASCADE,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  reaction_type VARCHAR(10) NOT NULL CHECK (reaction_type IN ('like', 'dislike')),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (user_id, review_id)
);

CREATE INDEX IF NOT EXISTS idx_reactions_review ON review_reactions(review_id);

-- ============================================================
-- 6. RECOMMANDATIONS
-- ============================================================

CREATE TABLE IF NOT EXISTS recommendations (
  id BIGSERIAL PRIMARY KEY,
  product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
  recommender_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  recommended_to_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  message TEXT DEFAULT NULL,
  is_viewed BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_recommendations_receiver ON recommendations(recommended_to_id, is_viewed);
CREATE INDEX IF NOT EXISTS idx_recommendations_product ON recommendations(product_id);

-- ============================================================
-- 7. SYSTÈME DE TÂCHES
-- ============================================================

CREATE TABLE IF NOT EXISTS tasks_definitions (
  id SERIAL PRIMARY KEY,
  task_code VARCHAR(50) NOT NULL UNIQUE,
  task_name VARCHAR(150) NOT NULL,
  description TEXT,
  reward_amount DECIMAL(10,2) NOT NULL,
  is_daily BOOLEAN DEFAULT TRUE,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_tasks (
  id BIGSERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  task_id INT NOT NULL REFERENCES tasks_definitions(id) ON DELETE CASCADE,
  completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reward_earned DECIMAL(10,2) NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_user_tasks_user ON user_tasks(user_id, task_id);
CREATE INDEX IF NOT EXISTS idx_user_tasks_completed ON user_tasks(completed_at);

-- ============================================================
-- 8. SYSTÈME DE PARRAINAGE
-- ============================================================

CREATE TABLE IF NOT EXISTS referrals (
  id BIGSERIAL PRIMARY KEY,
  referrer_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  referred_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  reward_amount DECIMAL(10,2) DEFAULT 0,
  is_rewarded BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  rewarded_at TIMESTAMP NULL,
  UNIQUE (referred_id)
);

CREATE INDEX IF NOT EXISTS idx_referrals_referrer ON referrals(referrer_id);
CREATE INDEX IF NOT EXISTS idx_referrals_rewarded ON referrals(is_rewarded);

-- ============================================================
-- 9. TRANSACTIONS & PORTEFEUILLE
-- ============================================================

CREATE TABLE IF NOT EXISTS transactions (
  id BIGSERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  type VARCHAR(20) NOT NULL CHECK (type IN ('reward', 'referral', 'withdrawal', 'bonus', 'penalty', 'deposit', 'earning', 'review')),
  amount DECIMAL(12,2) NOT NULL,
  description VARCHAR(255),
  reference_id BIGINT DEFAULT NULL,
  reference_type VARCHAR(50) DEFAULT NULL,
  balance_after DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_transactions_user_type ON transactions(user_id, type);
CREATE INDEX IF NOT EXISTS idx_transactions_created ON transactions(created_at);

-- Retraits
CREATE TABLE IF NOT EXISTS withdrawals (
  id BIGSERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  amount DECIMAL(12,2) NOT NULL,
  phone_number VARCHAR(20) NOT NULL,
  status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'completed', 'rejected')),
  notes TEXT DEFAULT NULL,
  processed_by INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at TIMESTAMP NULL
);

CREATE INDEX IF NOT EXISTS idx_withdrawals_user_status ON withdrawals(user_id, status);
CREATE INDEX IF NOT EXISTS idx_withdrawals_status ON withdrawals(status);

-- ============================================================
-- 10. NOTIFICATIONS
-- ============================================================

CREATE TABLE IF NOT EXISTS notifications (
  id BIGSERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  type VARCHAR(30) NOT NULL CHECK (type IN ('task_reminder', 'new_product', 'new_review', 'reward', 'referral', 'withdrawal', 'system')),
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  link VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX IF NOT EXISTS idx_notifications_type ON notifications(type);
CREATE INDEX IF NOT EXISTS idx_notifications_created ON notifications(created_at);

-- ============================================================
-- 11. LOGS & AUDIT
-- ============================================================

CREATE TABLE IF NOT EXISTS activity_logs (
  id BIGSERIAL PRIMARY KEY,
  user_id INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50) DEFAULT NULL,
  entity_id BIGINT DEFAULT NULL,
  details TEXT DEFAULT NULL,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_activity_user_action ON activity_logs(user_id, action);
CREATE INDEX IF NOT EXISTS idx_activity_entity ON activity_logs(entity_type, entity_id);
CREATE INDEX IF NOT EXISTS idx_activity_created ON activity_logs(created_at);

-- ============================================================
-- 12. CONFIGURATION SYSTÈME
-- ============================================================

CREATE TABLE IF NOT EXISTS system_settings (
  id SERIAL PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  description VARCHAR(255),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 13. PAIEMENTS MOBILE MONEY (MeSomb)
-- ============================================================

CREATE TABLE IF NOT EXISTS payment_transactions (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  reference VARCHAR(100) NOT NULL UNIQUE,
  mesomb_reference VARCHAR(100),
  amount DECIMAL(10,2) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  channel VARCHAR(20) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  payment_url TEXT,
  webhook_data TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL
);

CREATE INDEX IF NOT EXISTS idx_payment_user ON payment_transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_payment_reference ON payment_transactions(reference);
CREATE INDEX IF NOT EXISTS idx_payment_status ON payment_transactions(status);

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
('Automobile', 'automobile', '🚗')
ON CONFLICT (name) DO NOTHING;

-- Configuration système
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('referral_reward', '5000', 'Récompense de parrainage en FCFA'),
('min_deposit', '1000', 'Montant minimum de dépôt en FCFA'),
('min_withdrawal', '5000', 'Montant minimum de retrait en FCFA'),
('daily_notifications_count', '2', 'Nombre minimum de notifications par jour'),
('products_generation_frequency', '3', 'Nombre de générations de produits par jour'),
('review_reward', '500', 'Récompense pour un avis en FCFA')
ON CONFLICT (setting_key) DO NOTHING;

-- Super Admin par défaut (CAU: ADMIN001)
INSERT INTO users (cau, name, phone, role, balance, referral_code, is_active) VALUES
('ADMIN001', 'Super Administrateur', '+22500000000', 'super_admin', 0, 'ADMIN001REF', TRUE)
ON CONFLICT (cau) DO NOTHING;

-- Entreprises de démonstration
INSERT INTO companies (name, slug, logo, description, created_by) VALUES
('TechnoPlus CI', 'technoplus-ci', 'assets/img/companies/technoplus.png', 'Leader de l''électronique en Côte d''Ivoire', 1),
('Mode Afrique', 'mode-afrique', 'assets/img/companies/mode-afrique.png', 'Vêtements et accessoires africains modernes', 1),
('BioMarket', 'biomarket', 'assets/img/companies/biomarket.png', 'Produits bio et naturels', 1)
ON CONFLICT (slug) DO NOTHING;

-- Admin entreprise pour TechnoPlus (CAU: TECH001)
INSERT INTO users (cau, name, phone, role, company_id, balance, referral_code, is_active, created_by) VALUES
('TECH001', 'Kouassi Admin', '+22501010101', 'admin_entreprise', 1, 0, 'TECH001REF', TRUE, 1)
ON CONFLICT (cau) DO NOTHING;

-- Utilisateurs de test
INSERT INTO users (cau, name, phone, role, company_id, balance, referral_code, referred_by, is_active, created_by) VALUES
('USER001', 'Ama Kouadio', '+22502020202', 'user', NULL, 2500, 'AMA2024REF', NULL, TRUE, 2),
('USER002', 'Yao Koffi', '+22503030303', 'user', NULL, 1000, 'YAO2024REF', NULL, TRUE, 2)
ON CONFLICT (cau) DO NOTHING;

-- Définitions des tâches
INSERT INTO tasks_definitions (task_code, task_name, description, reward_amount, is_daily, is_active) VALUES
('deposit_5000', 'Effectuer un dépôt (1 000 FCFA min)', 'Déposez au moins 1 000 FCFA comme preuve de transaction', 0, TRUE, TRUE),
('leave_review', 'Laisser un avis', 'Rédiger un avis détaillé sur un produit', 500, TRUE, TRUE),
('recommend_product', 'Recommander un produit', 'Recommander un produit à un ami', 200, TRUE, TRUE),
('like_review', 'Aimer un avis', 'Liker l''avis d''un autre utilisateur', 50, TRUE, TRUE),
('invite_user', 'Inviter un utilisateur', 'Inviter quelqu''un via votre lien de parrainage', 1000, FALSE, TRUE),
('daily_login', 'Connexion quotidienne', 'Se connecter chaque jour', 100, TRUE, TRUE)
ON CONFLICT (task_code) DO NOTHING;

-- Produits de démonstration
INSERT INTO products (company_id, category_id, title, slug, description, price, image, is_auto_generated, created_by) VALUES
(1, 1, 'Smartphone Galaxy Pro 12', 'smartphone-galaxy-pro-12', 'Dernier smartphone avec écran AMOLED 6.7" et caméra 108MP', 450000, 'assets/img/products/smartphone-1.jpg', FALSE, 2),
(1, 1, 'Laptop UltraBook X15', 'laptop-ultrabook-x15', 'PC portable professionnel Intel i7, 16GB RAM, SSD 512GB', 650000, 'assets/img/products/laptop-1.jpg', FALSE, 2),
(1, 1, 'Écouteurs Wireless Pro', 'ecouteurs-wireless-pro', 'Écouteurs Bluetooth avec réduction de bruit active', 45000, 'assets/img/products/ecouteurs-1.jpg', FALSE, 2)
ON CONFLICT (slug, company_id) DO NOTHING;

-- ============================================================
-- FIN DU SCHEMA POSTGRESQL
-- ============================================================
