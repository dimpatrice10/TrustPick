-- TrustPick — schema + seed data for local XAMPP import
-- Create database (run or import into phpMyAdmin and set database to 'trustpick')
CREATE DATABASE IF NOT EXISTS `trustpick` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `trustpick`;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','company','admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Companies
CREATE TABLE IF NOT EXISTS companies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  price DECIMAL(9,2) DEFAULT 0,
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT NOT NULL,
  rating TINYINT NOT NULL DEFAULT 5,
  title VARCHAR(200),
  body TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wallets
CREATE TABLE IF NOT EXISTS wallets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  balance DECIMAL(10,2) DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Withdraw requests (simple)
CREATE TABLE IF NOT EXISTS withdrawals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount DECIMAL(9,2) NOT NULL,
  status ENUM('pending','completed','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: users (passwords are plaintext for local dev; change in production)
INSERT INTO users (name,email,password,role) VALUES
('Jean Dupont','jean@example.com','password','user'),
('Marie Jouve','marie@example.com','password','user'),
('Admin','admin@example.com','adminpass','admin');

-- Seed: companies
INSERT INTO companies (name,description) VALUES
('Acme Corp','Spécialiste électronique premium'),
('Nova Tech','Innovation & accessoires tech'),
('EcoGoods','Produits éco-responsables');

-- Seed: products
INSERT INTO products (company_id,title,description,price,image) VALUES
(1,'Casque sans fil premium X','Casque Bluetooth ANC, confortable, 30h autonomie',89.99,'assets/img/elements/1.jpg'),
(2,'Chargeur USB-C 65W','Chargeur rapide 65W pour laptop & mobile',29.90,'assets/img/elements/2.jpg'),
(3,'Sac à dos urbain Eco','Sac éco-responsable, compartiments multiples',79.00,'assets/img/elements/3.jpg'),
(1,'Souris sans fil ergonomique','Souris bluetooth ergonomique, 2 ans de garantie',49.50,'assets/img/elements/4.jpg'),
(2,'Clavier mécanique RGB Pro','Switchs tactiles, rétroéclairage RGB',129.99,'assets/img/elements/5.jpg'),
(2,'Écran 4K 27" USB-C','Écran professionnel 4K, USB-C passthrough',399.00,'assets/img/elements/6.jpg'),
(1,'Webcam 4K autofocus','Webcam 4K pour streaming & meetings',89.50,'assets/img/elements/7.jpg'),
(3,'Hub USB-C 7-en-1','Hub pour laptop avec charge et HDMI',59.99,'assets/img/elements/8.jpg');

-- Seed: reviews
INSERT INTO reviews (product_id,user_id,rating,title,body) VALUES
(1,1,5,'Excellent !','Très bon casque, ANC efficace.'),
(1,2,5,'Top qualité','Confortable et son clair.'),
(2,1,4,'Rapide','Charge vite, un peu chaud parfois.'),
(3,2,4,'Pratique','Beau design et solide.'),
(4,1,5,'Ergonomique','Parfait pour les journées longues.'),
(5,2,5,'Parfait pour gaming','Switchs agréables.'),
(6,1,4,'Très bon écran','Couleurs précises.'),
(7,2,4,'Bonne webcam','Image nette.'),
(8,1,4,'Utile','Beaucoup de ports utiles.');

-- Seed: wallets
INSERT INTO wallets (user_id,balance) VALUES
(1,124.00),(2,12.50),(3,0.00);

-- Done