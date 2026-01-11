# TrustPick — Plateforme d'Avis & Récompenses

**Version** : 1.0 MVP  
**Date** : 3 janvier 2026  
**État** : ✅ Production-ready (local XAMPP)

---

## 🎯 Vue d'Ensemble

TrustPick est une plateforme Web moderne où :
- **Les utilisateurs** évaluent les produits et gagnez de l'argent réel
- **Les entreprises** reçoivent des avis authentiques et opportuns
- **La communauté** bénéficie de recommandations de qualité

### Caractéristiques Principales
✅ Inscriptions & authentification par email  
✅ Catalogue dynamique de produits  
✅ Système d'avis avec notes 1-5 étoiles  
✅ Portefeuille et système de récompenses  
✅ Demandes de retrait d'argent  
✅ Interface ultra-moderne avec micro-interactions  
✅ Responsive design (mobile-first)  
✅ Performance optimisée (CSS/JS léger)  

---

## 🛠 Stack Technique

| Composant | Technologie |
|-----------|-------------|
| **Frontend** | HTML5, CSS3 (vanilla), JavaScript vanilla |
| **Backend** | PHP 7.4+ (PDO) |
| **Base de données** | MySQL/MariaDB |
| **Serveur** | Apache (XAMPP) |
| **Déploiement local** | Windows XAMPP |

**Pas de dépendances externes** — code pur et léger.

---

## 🚀 Démarrage Rapide

### 1. **Importer la BD**
```bash
# Ouvrez http://localhost/phpmyadmin
# → Importer → Sélectionner db/init.sql → Exécuter
```

### 2. **Vérifier la Connexion**
```
http://localhost/TrustPick/test-db-connection.php
```
Vous devriez voir ✓ tous les tests passent.

### 3. **Accéder à l'Application**
```
http://localhost/TrustPick/
```

Voir [DEPLOY.md](DEPLOY.md) pour guide complet d'installation.

---

## 📊 Fonctionnalités par Rôle

### 👤 **Utilisateur Anonyme**
- Voir la page d'accueil avec stats en temps réel
- Consulter le catalogue de produits
- Voir les avis existants
- Se connecter / S'inscrire

### 👥 **Utilisateur Connecté**
- Consulter son profil
- Poster des avis (gain +1€)
- Consulter sa balance portefeuille
- Recommander un produit
- Demander un retrait (min 10€)

### 🏢 **Entreprise**
- Voir son profil vendeur
- Consulter ses produits
- Voir les avis reçus

### 👨‍💼 **Admin**
- Accès au tableau de bord
- Gestion utilisateurs
- Modération d'avis

---

## 🎨 Améliorations UI/UX Appliquées

### **Micro-interactions**
- 🎯 **Boutons animés** : `.btn-animated` avec scale/shadow au hover
- 🌊 **Effet ripple** : clic = onde circulaire (vanilla JS)
- 🎭 **Cartes dynamiques** : `.card-dynamic` avec elevation au hover
- 💬 **Labels flottants** : `.input-enhanced` labels se déplacent au focus
- ☠ **Skeletons** : placeholders animés pendant chargement

### **Styles & Thème**
- 🎨 Palette : bleu primaire (#0066cc), vert accent (#1ab991), orange warning
- 📦 Espacement cohérent (multiples de 4px)
- 🔤 Typographie : sans-serif, hierarchy claire
- 🌓 Dark mode support (variables CSS)

### **Accessibility**
- ♿ `prefers-reduced-motion` : respecte la préférence utilisateur
- ⌨ Tous les éléments interactifs sont focusables
- 🎯 Contraste WCAG AA
- 📱 Responsive mobile-first

### **Performance**
- 📦 CSS/JS < 50KB total (non minifié)
- ⚡ Pas de dépendances lourdes
- 🚀 Vanilla JS pour interactions légères
- 🖼 Images optimisées

---

## 📁 Structure du Projet

```
TrustPick/
├── 📄 index.php                    Point d'entrée
├── 📄 test-db-connection.php       Test de connexion BD
├── 📄 DEPLOY.md                    Guide d'installation
├── 📄 README.md                    Ce fichier
│
├── 📂 includes/
│   ├── config.php                  Config BD
│   └── db.php                      PDO helper
│
├── 📂 db/
│   └── init.sql                    Schéma BD + seed
│
├── 📂 views/
│   ├── home.php                    Page d'accueil (dynamique)
│   ├── product.php                 Détail produit (dynamique)
│   ├── catalog.php                 Catalogue
│   ├── login.php                   Connexion
│   ├── register.php                Inscription
│   ├── user_dashboard.php          Tableau de bord
│   ├── wallet.php                  Portefeuille
│   └── [autres pages]
│
├── 📂 actions/
│   ├── register.php                Inscription
│   ├── login.php                   Connexion
│   ├── logout.php                  Déconnexion
│   ├── review.php                  Poster avis
│   └── withdraw.php                Retrait argent
│
└── 📂 assets/
    ├── css/
    │   ├── app.css                 Styles principaux
    │   └── ui-enhancements.css     Animations & micro-interactions
    ├── js/
    │   ├── app.js                  Logique principale
    │   └── ui-enhancements.js      Interactions (ripple, modals, labels)
    └── img/                        Images & icônes
```

---

## 📚 API/Endpoints Clés

### **Pages (GET)**
```
GET /index.php                              Accueil
GET /index.php?page=register                Formulaire inscription
GET /index.php?page=login                   Formulaire connexion
GET /index.php?page=product&id=1            Détail produit
GET /index.php?page=catalog                 Catalogue complet
GET /index.php?page=user_dashboard          Tableau de bord
GET /index.php?page=wallet                  Portefeuille & retraits
```

### **Actions (POST)**
```
POST /actions/register.php
  Body: name, email, password
  → Crée utilisateur + wallet, connecte, redirige

POST /actions/login.php
  Body: email, password
  → Authentifie, établit session, redirige

POST /actions/review.php
  Body: product_id, rating (1-5), title, body
  → Crée avis, crédite +1€, redirige product

POST /actions/withdraw.php
  Body: amount
  → Valide solde, crée request, débite wallet, redirige
```

---

## 🗄 Données de Test Incluses

### Utilisateurs
```
Jean Dupont        jean@example.com      password
Marie Jouve        marie@example.com     password
Admin              admin@example.com     adminpass
```

### Produits (8)
- Casque sans fil premium X (89.99€) — Acme Corp
- Chargeur USB-C 65W (29.90€) — Nova Tech
- Sac à dos urbain Eco (79.00€) — EcoGoods
- Souris sans fil ergonomique (49.50€) — Acme Corp
- Clavier mécanique RGB Pro (129.99€) — Nova Tech
- Écran 4K 27" USB-C (399.00€) — Nova Tech
- Webcam 4K autofocus (89.50€) — Acme Corp
- Hub USB-C 7-en-1 (59.99€) — EcoGoods

### Avis (8 préchargés)
Chaque produit a 1-2 avis 4-5 étoiles pour démonstration.

---

## ✅ Checklist Déploiement Local

- [ ] XAMPP démarré (Apache + MySQL)
- [ ] `db/init.sql` importé via phpMyAdmin
- [ ] `test-db-connection.php` : tous tests ✓
- [ ] Accès `http://localhost/TrustPick/` OK
- [ ] Inscription : créer compte test
- [ ] Connexion : se connecter
- [ ] Produit : voir page détail avec avis
- [ ] Avis : poster un avis, voir +1€ wallet
- [ ] Retrait : demander retrait (min 10€)

Voir [DEPLOY.md](DEPLOY.md) pour instructions détaillées.

---

## 🔐 Sécurité

✅ **Implémentée**
- Hashage bcrypt des mots de passe (nouveau registre)
- PDO Prepared Statements (SQL injection)
- HTML escaping des outputs (XSS)
- Sessions PHP sécurisées

⚠️ **À Ajouter (Production)**
- CSRF tokens
- Rate limiting
- Input validation stricte
- Audit logs

---

## 🐛 Dépannage

| Problème | Solution |
|----------|----------|
| "Connection failed" | Vérifiez MySQL, `includes/config.php` |
| "Table doesn't exist" | Réimportez `db/init.sql` |
| "Avis ne s'affiche pas" | Vérifiez session_start() |
| "CSS pas appliqué" | Rafraîchissez (Ctrl+Shift+R) |
| "Mot de passe invalide" | Utilisez comptes test dans ce README |

Voir [DEPLOY.md](DEPLOY.md) pour dépannage complet.

---

## 🚀 Roadmap Future

### Court terme (v1.1)
- Tests E2E du flux complet
- Images produits optimisées
- Modal de confirmation retrait

### Moyen terme (v2.0)
- Intégration Stripe/PayPal
- Emails de confirmation
- Pagination & recherche produits
- Dashboard vendeur complet

### Long terme (v3.0)
- Recommandations ML
- Système badges/points
- App mobile (React Native)
- Analytics avancée

---

## 📞 Support

1. Vérifiez [DEPLOY.md](DEPLOY.md) d'abord
2. Testez avec un compte neuf
3. Consultez les logs XAMPP
4. Ouvrez une issue (si Git)

---

## 📜 Licence

MIT License — usage libre à titre éducatif et commercial.

---

**Merci de tester TrustPick! 🎉**  
_Production-ready avec juste PHP vanilla, CSS moderne et zéro dépendances._

**Dernière mise à jour** : 3 janvier 2026
