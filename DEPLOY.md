# 📋 Guide d'Import & Déploiement Local TrustPick

## ✅ Prérequis
- XAMPP (Apache + MySQL)
- `db/init.sql` présent dans le dossier du projet
- Accès à phpMyAdmin (généralement via `http://localhost/phpmyadmin`)

---

## 🚀 Étapes d'Installation

### 1. **Démarrer les services XAMPP**
   - Lancez le **XAMPP Control Panel**
   - Démarrez **Apache** (normalement déjà actif)
   - Démarrez **MySQL** 

### 2. **Importer la Base de Données**

#### **Option A : Via phpMyAdmin (recommandé)**
1. Ouvrez `http://localhost/phpmyadmin`
2. Cliquez sur l'onglet **"Importer"** (en haut)
3. Cliquez sur **"Sélectionner un fichier"**
4. Naviguez vers `c:\xampp2\htdocs\TrustPick\db\init.sql`
5. Cliquez sur **"Exécuter"**
6. Attendez le message `Importer successful`

#### **Option B : Via ligne de commande**
```bash
cd c:\xampp2\bin
mysql -u root -p trustpick < c:\xampp2\htdocs\TrustPick\db\init.sql
# (laisser vide le mot de passe par défaut, appuyer sur Entrée)
```

---

### 3. **Vérifier l'Installation**

Ouvrez dans votre navigateur :
```
http://localhost/TrustPick/test-db-connection.php
```

**Vous devriez voir :**
- ✓ Config loaded
- ✓ Database connection successful
- ✓ Tables check (6 tables avec données)
- ✓ All tests passed!

Si vous voyez une erreur de connexion :
- Vérifiez que MySQL est démarré
- Vérifiez que `includes/config.php` correspond à votre setup XAMPP
  - Par défaut : `host = 127.0.0.1`, `user = root`, `pass = ''` (vide)

---

### 4. **Lancer l'Application**

```
http://localhost/TrustPick/index.php
```

Vous devriez voir la page d'accueil avec les stats en temps réel :
- Nombre d'utilisateurs
- Nombre de produits
- Nombre d'avis
- Montant redistribué

---

## 🧪 Tester les Fonctionnalités Principales

### **Test 1 : Inscription (Register)**
1. Cliquez sur **"Commencer gratuitement"**
2. Remplissez le formulaire d'inscription
   - Nom : `Test User`
   - Email : `test@example.com`
   - Mot de passe : `password123`
3. Cliquez sur **"S'inscrire"**
4. Vous devriez être redirigé vers la page d'accueil ET connecté (voir le menu)

### **Test 2 : Connexion (Login)**
1. Si déjà connecté, cliquez sur **"Se déconnecter"** d'abord
2. Cliquez sur **"Se connecter"**
3. Entrez un compte existant :
   - Email : `jean@example.com`
   - Mot de passe : `password` (comptes test)
4. Vous devriez être connecté

### **Test 3 : Consulter un Produit & Laisser un Avis (Review)**
1. Cliquez sur **"Parcourir le catalogue"** ou accédez directement :
   ```
   http://localhost/TrustPick/index.php?page=product&id=1
   ```
2. Si connecté, vous verrez un formulaire **"Laisser un avis"**
3. Remplissez :
   - Note : 5 étoiles
   - Titre (optionnel) : `Excellent produit!`
   - Avis : `C'est vraiment un très bon produit...`
4. Cliquez sur **"Publier l'avis"**
5. Votre avis apparaît immédiatement et vous gagnez **+1€** dans votre portefeuille

### **Test 4 : Retrait (Withdrawal)**
1. Accédez au tableau de bord utilisateur :
   ```
   http://localhost/TrustPick/index.php?page=user_dashboard
   ```
2. Vérifiez votre solde (devrait être ≥ 1€)
3. Cliquez sur **"Demander un retrait"**
4. Entrez un montant (ex: 2€)
5. Cliquez sur **"Envoyer la demande"**
6. Vous devriez voir un message de succès et votre solde diminue

---

## 📊 État de la Base de Données Initiale

| Table | Contenu |
|-------|---------|
| `users` | 3 comptes test (Jean, Marie, Admin) |
| `companies` | 3 entreprises (Acme Corp, Nova Tech, EcoGoods) |
| `products` | 8 produits variés |
| `reviews` | 8 avis préexistants |
| `wallets` | Portefeuilles créés automatiquement |
| `withdrawals` | Vide (à remplir lors des tests) |

### **Comptes de Test Prédéfinis**

```
Email: jean@example.com
Mot de passe: password

Email: marie@example.com
Mot de passe: password

Email: admin@example.com
Mot de passe: adminpass (admin)
```

---

## 🔧 Dépannage

### **Erreur : "Database connection failed"**
- Vérifiez que MySQL est démarré (`php -r "echo 'PHP OK';"` fonctionne)
- Vérifiez `includes/config.php` : ajustez `db_user` et `db_pass` si nécessaire
- Vérifiez que la base `trustpick` existe : `http://localhost/phpmyadmin`

### **Erreur : "Table 'trustpick.users' doesn't exist"**
- Re-importez `db/init.sql` via phpMyAdmin
- Vérifiez que l'import s'est terminé sans erreur

### **Les avis ne s'affichent pas**
- Vérifiez que vous êtes connecté (session PHP valide)
- Ouvrez le formulaire `index.php?page=product&id=1`
- Assurez-vous que la page `actions/review.php` reçoit bien la requête POST

---

## 📁 Structure des Fichiers Critiques

```
TrustPick/
├── db/
│   └── init.sql              ← À importer dans phpMyAdmin
├── includes/
│   ├── config.php            ← Config DB (ajustez si besoin)
│   └── db.php                ← Connexion PDO
├── actions/
│   ├── register.php          ← Inscription
│   ├── login.php             ← Connexion
│   ├── logout.php            ← Déconnexion
│   ├── review.php            ← Poster un avis
│   └── withdraw.php          ← Demander un retrait
├── views/
│   ├── home.php              ← Page d'accueil (dynamique)
│   ├── product.php           ← Détail produit (dynamique)
│   ├── catalog.php           ← Catalogue
│   ├── login.php             ← Formulaire de connexion
│   ├── register.php          ← Formulaire d'inscription
│   └── [autres pages]
├── assets/
│   ├── css/
│   │   ├── app.css           ← Styles principaux
│   │   └── ui-enhancements.css ← Animations & micro-interactions
│   ├── js/
│   │   ├── app.js            ← JS principal
│   │   └── ui-enhancements.js ← Interactions JS
│   └── img/                  ← Images & icônes
├── index.php                 ← Point d'entrée
├── test-db-connection.php    ← Script de test (important!)
└── README.md                 ← Ce fichier
```

---

## 🎯 Résumé des Améliorations UI/UX Appliquées

✅ **Micro-interactions** : boutons animés, effet ripple, cartes dynamiques  
✅ **Inputs améliorés** : étiquettes flottantes, focus styles  
✅ **Skeletons** : chargement progressif des éléments  
✅ **Modals** : pop-ups pour actions critiques  
✅ **FAB (Floating Action Button)** : accès rapide  
✅ **Accessibilité** : préférence `prefers-reduced-motion`, contraste WCAG  
✅ **Responsive** : grid auto-fit pour tous les écrans  
✅ **Animations douces** : transitions CSS, fade-in progressif  

---

## 🚀 Prochaines Étapes (Production)

1. **Sécurité** : ajouter CSRF tokens, sanitiser les inputs
2. **Authentification** : JWT ou sessions plus robustes
3. **Validation** : côté serveur stricte pour tous les formulaires
4. **Emails** : envoyer confirmation d'inscription, rappels de retrait
5. **Analytics** : tracker les avis, taux de conversion
6. **Monitoring** : logs applicatifs, alertes
7. **Performance** : cache Redis, CDN pour images, minification

---

## 📞 Support

Pour tout problème :
1. Vérifiez `test-db-connection.php`
2. Vérifiez les logs Apache/MySQL dans XAMPP
3. Ouvrez la console du navigateur (F12) pour erreurs JS
4. Vérifiez les permissions des fichiers dans `c:\xampp2\htdocs\TrustPick\`

---

**Dernière mise à jour** : 3 janvier 2026  
**Version** : 1.0 (MVP complet)
