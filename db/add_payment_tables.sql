-- TrustPick V2 - Tables pour le système de paiement MeSomb
-- Exécuter ce script pour créer les tables nécessaires

-- Table pour les transactions de paiement Mobile Money
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reference VARCHAR(100) NOT NULL UNIQUE,
    mesomb_reference VARCHAR(100) NULL,
    amount DECIMAL(10, 2) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    channel VARCHAR(20) NOT NULL COMMENT 'orange ou mtn',
    status VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, success, failed',
    payment_url TEXT NULL,
    webhook_data TEXT NULL,
    created_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_reference (reference),
    INDEX idx_mesomb_reference (mesomb_reference),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index composé pour optimiser les requêtes
CREATE INDEX idx_user_status ON payment_transactions(user_id, status);
CREATE INDEX idx_status_created ON payment_transactions(status, created_at);

-- Vérifier que la table transactions existe pour l'historique
-- Si elle n'existe pas, la créer
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('review', 'earning', 'withdrawal', 'deposit', 'bonus', 'referral') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50) NULL COMMENT 'payment, review, withdrawal',
    reference_id INT NULL,
    balance_after DECIMAL(10, 2) NOT NULL,
    created_at DATETIME NOT NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
