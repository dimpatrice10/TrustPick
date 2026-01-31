# 🧪 GUIDE DE TEST END-TO-END - TRUSTPICK V2

**Date**: 25 janvier 2026  
**Objectif**: Valider l'intégration complète frontend ↔ backend

---

## 🚀 AVANT DE COMMENCER

### 1️⃣ Initialiser la base de données

Exécutez le script SQL pour créer toutes les tables et données de test :

```bash
# Via MySQL CLI
mysql -u root -p < db/schema_v2_trustpick.sql

# OU via phpMyAdmin
# 1. Créer base: trustpick_v2
# 2. Importer: db/schema_v2_trustpick.sql
```

### 2️⃣ Vérifier la connexion

```bash
# Ouvrir navigateur
http://localhost/trustpick/test-db-connection.php
```

Vous devriez voir ✓ pour toutes les tables (SAUF wallets qui n'existe pas).

### 3️⃣ Valider l'intégration

```bash
http://localhost/trustpick/test-integration.php
```

Vous devriez voir **"🎉 TOUS LES TESTS PASSÉS!"**

---

## 📋 COMPTES DE TEST

| CAU        | Rôle             | Balance    | Lien             |
| ---------- | ---------------- | ---------- | ---------------- |
| `USER001`  | Utilisateur      | 2,500 FCFA | 👤 utilisateur   |
| `USER002`  | Utilisateur      | 1,000 FCFA | 👤 utilisateur   |
| `TECH001`  | Admin Entreprise | 0 FCFA     | 🏢 TechnoPlus CI |
| `ADMIN001` | Super Admin      | 0 FCFA     | 🔐 Full access   |

**Format de connexion** : Entrer le CAU dans le formulaire login (sans mot de passe)

---

## 🧪 TEST 1 : AUTHENTIFICATION CAU

### Scénario 1A : Connexion valide (USER001)

1. Accédez à : `http://localhost/trustpick/public/index.php?page=login`
2. Entrez : `USER001`
3. Cliquez : "Se connecter"

**Résultat attendu** :

```
✅ Redirection vers index.php?page=home
✅ Session créée
✅ Message "Connecté en tant que Ama Kouadio"
✅ Menu affiche "Mon compte" et "Portefeuille"
```

### Scénario 1B : Connexion invalide

1. Sur la page login
2. Entrez : `INVALID999`
3. Cliquez : "Se connecter"

**Résultat attendu** :

```
❌ Message d'erreur : "CAU invalide ou utilisateur inactif"
❌ Page reste login (redirection vers index.php?page=login)
```

### Scénario 1C : Brute force protection

1. Essayez 6 connexions invalides rapidement
2. À la 6ème tentative

**Résultat attendu** :

```
🔒 Message : "Compte verrouillé pendant 15 minutes"
🔒 Session suspendue
```

---

## 📱 TEST 2 : NAVIGATION & PAGES

### Scénario 2A : Accès pages publiques

| URL              | Résultat                                 |
| ---------------- | ---------------------------------------- |
| `?page=home`     | ✅ Page affichée (hero, stats, produits) |
| `?page=catalog`  | ✅ Catalogue avec filtres                |
| `?page=login`    | ✅ Formulaire login                      |
| `?page=register` | ✅ Formulaire inscription                |

### Scénario 2B : Accès pages protégées (NON authentifié)

| URL                    | Résultat                  |
| ---------------------- | ------------------------- |
| `?page=user_dashboard` | ❌ Redirection vers login |
| `?page=wallet`         | ❌ Redirection vers login |

### Scénario 2C : Accès pages protégées (authentifié USER001)

1. Connectez-vous avec `USER001`
2. Visitez : `?page=user_dashboard`

**Résultat attendu** :

```
✅ Tableau de bord utilisateur affiché
✅ Nom : "Ama Kouadio"
✅ Balance : "2,500 FCFA"
✅ Menu latéral avec 7 options
```

3. Cliquez : "💰 Mon portefeuille"

**Résultat attendu** :

```
✅ Page wallet affichée
✅ Balance : "2,500 FCFA"
✅ Historique vide (aucune transaction encore)
```

### Scénario 2D : Accès superadmin_dashboard (ADMIN001)

1. Déconnectez USER001 : cliquez ≡ → Se déconnecter
2. Connectez-vous avec `ADMIN001`
3. Système redirigedevrait vers `?page=superadmin_dashboard`

**Résultat attendu** :

```
✅ Dashboard super admin affiché
✅ KPIs visibles :
   - Utilisateurs total
   - Entreprises
   - Produits
   - Avis
   - Parrainages
   - Retraits en attente
   - Récompenses distribuées
```

---

## 🛒 TEST 3 : CATALOGUE & PRODUITS

### Scénario 3A : Affichage catalogue

1. Accédez : `?page=catalog`
2. Observez : Grille de produits

**Résultat attendu** :

```
✅ 5 produits affichés par défaut
✅ Chaque produit a :
   - Titre
   - Prix (ex: "450,000 FCFA")
   - Image
   - Étoiles (rating)
✅ Bouton "Voir plus" présent si plus de 5 produits
```

### Scénario 3B : Filtrer par catégorie

1. Cliquez sur filtre (ex: "Électronique")
2. Observez : Produits filtrés

**Résultat attendu** :

```
✅ Produits filtrés par catégorie
✅ URL change (paramètre GET category)
✅ Aucun rechargement page (AJAX)
```

### Scénario 3C : Voir plus de produits

1. Cliquez : "Voir plus"
2. Observez : Charge 5 produits supplémentaires

**Résultat attendu** :

```
✅ 10 produits visibles (5 + 5)
✅ Bouton "Voir plus" change de position
✅ Pas de duplication de produits
```

### Scénario 3D : Accéder au détail produit

1. Cliquez sur un produit
2. Système redirige vers : `?page=product&id=1`

**Résultat attendu** :

```
✅ Page détail produit affichée
✅ Titre, description, prix, image visibles
✅ Section avis visible
✅ Formulaire "Laisser un avis" visible SI authentifié
```

---

## ⭐ TEST 4 : SYSTÈME D'AVIS

### Scénario 4A : Poster un avis (authentifié)

1. Connectez-vous avec `USER001`
2. Allez sur `?page=product&id=1`
3. Remplissez :
   - Note : 5 étoiles
   - Titre : "Excellent produit!"
   - Avis : "Très satisfait de cet achat"
4. Cliquez : "Publier mon avis"

**Résultat attendu** :

```
✅ Succès : "Avis publié! +500 FCFA gagnés"
✅ Balance passe de 2,500 à 3,000 FCFA
✅ Avis apparaît dans la liste
```

### Scénario 4B : Interdiction double avis

1. Essayez de poster un 2e avis sur le MÊME produit

**Résultat attendu** :

```
❌ Erreur : "Vous avez déjà laissé un avis sur ce produit"
❌ Avis non créé
❌ Pas de crédit double
```

### Scénario 4C : Like/Dislike sur avis

1. Trouvez un avis d'un autre utilisateur
2. Cliquez : ❤️ Like

**Résultat attendu** :

```
✅ Compteur like +1
✅ Bouton devient rouge (aimé)
```

3. Cliquez à nouveau : Like

**Résultat attendu** :

```
✅ Compteur like -1 (unlike)
✅ Bouton redevient gris
```

---

## 💰 TEST 5 : WALLET & TRANSACTIONS

### Scénario 5A : Vérifier balance après avis

1. Connectez USER001 (si pas connecté)
2. Allez : `?page=wallet`

**Résultat attendu** :

```
✅ Balance affichée : 3,000 FCFA (ou plus si autres tâches)
✅ Historique transactions visible
✅ Montants en FCFA
```

### Scénario 5B : Demander un retrait

1. Sur page wallet
2. Cliquez : "Demander un retrait"
3. Remplissez :
   - Montant : 1,000 FCFA
   - Numéro Mobile Money : +22500000001
4. Cliquez : "Demander le retrait"

**Résultat attendu** :

```
✅ Retrait créé (status: pending)
✅ Balance débité : 3,000 - 1,000 = 2,000 FCFA
✅ Transaction visible dans historique
```

### Scénario 5C : Montant minimum non respecté

1. Tentez retrait de 100 FCFA

**Résultat attendu** :

```
❌ Erreur : "Montant minimum: 5,000 FCFA"
❌ Solde inchangé
```

### Scénario 5D : Solde insuffisant

1. Tentez retrait de 10,000 FCFA (balance = 2,000 FCFA)

**Résultat attendu** :

```
❌ Erreur : "Solde insuffisant"
❌ Solde inchangé
```

---

## 🏢 TEST 6 : ADMIN ENTREPRISE

### Scénario 6A : Connexion admin entreprise

1. Connectez-vous avec `TECH001`
2. Système redirige vers : `?page=admin_dashboard`

**Résultat attendu** :

```
✅ Dashboard admin affiché
✅ Titre : "Tableau de bord Admin Entreprise"
✅ Stats pour entreprise TechnoPlus CI uniquement
```

### Scénario 6B : Gérer produits

1. Sur admin dashboard
2. Cliquez : "📦 Gérer mes produits"

**Résultat attendu** :

```
✅ Liste produits de l'entreprise
✅ Options : Ajouter, Éditer, Supprimer
✅ Filtres par catégorie
```

---

## 🔐 TEST 7 : SUPER ADMIN

### Scénario 7A : Tableau de bord super admin

1. Connectez-vous avec `ADMIN001`

**Résultat attendu** :

```
✅ Dashboard super admin affiché
✅ Statistiques globales :
   - Total utilisateurs
   - Total entreprises
   - Total produits
   - Total transactions
```

### Scénario 7B : Gestion utilisateurs

1. Cliquez : "👥 Gérer utilisateurs"

**Résultat attendu** :

```
✅ Liste tous les utilisateurs
✅ Options : Créer, Éditer, Désactiver
✅ Affiche CAU, nom, rôle, balance
```

### Scénario 7C : Créer nouvel utilisateur

1. Cliquez : "+ Créer utilisateur"
2. Remplissez :
   - Nom : "Test User"
   - Rôle : "user"
   - Phone : "+22509999999"
3. Cliquez : "Créer"

**Résultat attendu** :

```
✅ Utilisateur créé
✅ CAU auto-généré (ex: USER000003)
✅ Code de parrainage auto-généré
✅ Affichage du CAU pour transmission
```

---

## 🔔 TEST 8 : NOTIFICATIONS

### Scénario 8A : Notifications auto-générées

1. Connectez-vous avec `USER001`
2. Allez : `?page=home` ou `?page=user_dashboard`

**Résultat attendu** :

```
✅ Badge de notification (nombre)
✅ Cliquez : cloche 🔔
✅ Panneau notifications ouvre
```

### Scénario 8B : Types de notifications

Vous devriez voir des notifications pour :

```
✅ Tâches quotidiennes ("Connexion quotidienne +100 FCFA")
✅ Avis reçus ("Votre avis a reçu un like")
✅ Retraits traités ("Votre retrait a été approuvé")
✅ Parrainage ("Votre filleul a rejoint TrustPick")
```

---

## 🔗 TEST 9 : PARRAINAGE

### Scénario 9A : Afficher lien de parrainage

1. Allez : `?page=user_dashboard`
2. Cliquez : "🔗 Mes parrainages"

**Résultat attendu** :

```
✅ Lien unique affiché (ex: ?ref=AMA2024REF)
✅ Boutons partage : WhatsApp, Facebook, Twitter, Telegram
✅ Copier lien au presse-papiers
```

### Scénario 9B : Partager via WhatsApp

1. Cliquez : "📱 WhatsApp"
2. Nouvel onglet ouvre : WhatsApp Web

**Résultat attendu** :

```
✅ Message pré-rempli avec lien
✅ Pourcentage crédité au parrain
```

---

## ❌ TEST 10 : GESTION ERREURS

### Scénario 10A : Page inexistante

1. Accédez : `?page=inexistant`

**Résultat attendu** :

```
❌ HTTP 404
✅ Page 404 TrustPick affichée
✅ Bouton "Retour à l'accueil"
```

### Scénario 10B : Produit inexistant

1. Accédez : `?page=product&id=99999`

**Résultat attendu** :

```
❌ Message : "Produit introuvable"
✅ Bouton "Retour au catalogue"
```

### Scénario 10C : Accès interdit (permission)

1. Connectez USER001
2. Essayez accès admin : `?page=admin_dashboard`

**Résultat attendu** :

```
❌ Accès refusé ou redirection vers home
✅ Message : "Vous n'avez pas les permissions"
```

---

## 📊 CHECKLIST DE VALIDATION

Cochez les tests réussis :

```
PHASE 1 - AUTHENTIFICATION
[ ] Connexion valide (USER001)
[ ] Connexion invalide
[ ] Protection brute force
[ ] Redirection rôle super admin
[ ] Redirection rôle admin
[ ] Redirection rôle user

PHASE 2 - PAGES
[ ] Accès pages publiques (home, catalog)
[ ] Redirection si pas authentifié (wallet, dashboard)
[ ] superadmin_dashboard accessible
[ ] Aucune erreur 404 sur pages routées

PHASE 3 - PRODUITS
[ ] Affichage liste (5 produits)
[ ] Pagination "voir plus"
[ ] Filtres catégorie
[ ] Détail produit
[ ] Pas de duplication

PHASE 4 - AVIS
[ ] Poster avis (+500 FCFA)
[ ] Interdiction double avis
[ ] Like/Dislike sur avis
[ ] Affichage ratings

PHASE 5 - WALLET
[ ] Balance affichée en FCFA
[ ] Historique transactions
[ ] Demande retrait
[ ] Validation montant min
[ ] Validation solde suffisant

PHASE 6 - ADMIN
[ ] Dashboard admin entreprise
[ ] Dashboard super admin
[ ] Gérer utilisateurs
[ ] Créer utilisateur + CAU auto

PHASE 7 - NOTIFICATIONS
[ ] Notifications apparaissent
[ ] Types corrects
[ ] Mark as read
[ ] Auto-refresh

PHASE 8 - PARRAINAGE
[ ] Lien unique généré
[ ] Partage WhatsApp/Facebook/Twitter/Telegram
[ ] Copier lien

PHASE 9 - ERREURS
[ ] 404 page inexistante
[ ] Erreur produit introuvable
[ ] Erreur permission insuffisante

PHASE 10 - ABSENCE ERREURS
[ ] Zéro fatal error PHP
[ ] Zéro erreur SQL
[ ] Console JS clean (F12)
[ ] Network tab pas d'erreur 500
```

---

## 🐛 DÉPANNAGE

### Erreur : "Fatal error: Class Database not found"

**Cause** : db.php chargé plusieurs fois  
**Solution** : Vérifier que tous les `require` sont remplacés par `require_once`

### Erreur : "Table 'wallets' doesn't exist"

**Cause** : Code cherche table inexistante  
**Solution** : Utiliser `users.balance` au lieu de `wallets`

### Erreur : "Database connexion failed"

**Cause** : Identifiants MySQL incorrects  
**Solution** : Vérifier `includes/config.php` avec config XAMPP

### Erreur : 404 sur superadmin_dashboard

**Cause** : Page non routée  
**Solution** : ✅ Déjà corrigée (ajoutée dans index.php)

### Balance affichée en € au lieu FCFA

**Cause** : Format obsolète  
**Solution** : ✅ Déjà corrigée (header.php, review.php)

---

## 🎯 RÉSULTAT FINAL

Tous les tests devraient être ✅ :

```
✅ Base de données fonctionnelle
✅ Authentification CAU sécurisée
✅ Autorisation par rôle
✅ Pages routées correctement
✅ Produits et avis fonctionnels
✅ Wallet et transactions FCFA
✅ Notifications actives
✅ Parrainage opérationnel
✅ Zéro fatal error
✅ Prêt pour production
```

**Prochaines étapes**:

1. Mettre en place CRON jobs pour notifications/produits
2. Configurer SMTP pour emails (optionnel)
3. Déployer sur serveur production
4. Configurer certificat SSL HTTPS

---

**Document créé**: 25 janvier 2026  
**Version**: 1.0 - Version stable  
**Statut**: En test
