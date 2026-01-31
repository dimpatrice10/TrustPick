# 🚀 TrustPick V2 - Guide d'Implémentation Complet

**Date**: 24 janvier 2026  
**Architecte**: IA Senior + Product Owner  
**Objectif**: Plateforme d'avis et recommandations professionnelle adaptée au marché africain (FCFA)

---

## 📋 TABLE DES MATIÈRES

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture technique](#architecture-technique)
3. [Composants créés](#composants-créés)
4. [Guide d'installation](#guide-dinstallation)
5. [Utilisation des systèmes](#utilisation-des-systèmes)
6. [Tâches CRON à configurer](#tâches-cron-à-configurer)
7. [Endpoints API](#endpoints-api)
8. [Prochaines étapes](#prochaines-étapes)

---

## 🎯 VUE D'ENSEMBLE

TrustPick V2 est une refonte complète avec les fonctionnalités suivantes :

### ✅ Fonctionnalités Implémentées

- ✅ **Authentification CAU** - Code d'Accès Utilisateur unique (pas d'email/mot de passe)
- ✅ **Système de Rôles** - Super Admin, Admin Entreprise, Utilisateur
- ✅ **Permissions granulaires** - Contrôle d'accès détaillé par rôle
- ✅ **Génération automatique de produits** - 3 fois par jour minimum avec images
- ✅ **Système de tâches** - Tâches quotidiennes avec récompenses FCFA
- ✅ **Parrainage** - Liens d'invitation uniques avec bonus
- ✅ **Notifications** - Minimum 2 par jour par utilisateur
- ✅ **Pagination intelligente** - 5 éléments par défaut avec "Voir plus"
- ✅ **Monnaie FCFA** - Tous les montants en Francs CFA

---

## 🏗️ ARCHITECTURE TECHNIQUE

### Structure de la base de données

```
trustpick_v2/
├── users (avec CAU et referral_code)
├── companies
├── categories
├── products (auto-générés ou manuels)
├── reviews (1 avis max par utilisateur/produit)
├── review_reactions (likes/dislikes)
├── recommendations
├── tasks_definitions
├── user_tasks
├── referrals
├── transactions
├── withdrawals
├── notifications
├── activity_logs
└── system_settings
```

### Fichiers PHP créés

```
includes/
├── auth.php              # Système CAU + gestion utilisateurs
├── permissions.php       # Gestion des permissions
├── product_generator.php # Génération automatique de produits
├── tasks.php            # Système de tâches quotidiennes
├── referrals.php        # Système de parrainage
├── notifications.php    # Système de notifications
└── pagination.php       # Pagination intelligente universelle
```

---

## 📦 COMPOSANTS CRÉÉS

### 1️⃣ Système d'Authentification CAU

**Fichier**: `includes/auth.php`

**Classes principales**:

- `AuthCAU` - Gestion de l'authentification

**Fonctionnalités**:

- ✅ Génération de CAU unique (format: PREFIX + 6 chiffres)
- ✅ Connexion sans mot de passe
- ✅ Génération de codes de parrainage
- ✅ Création d'utilisateurs par admin
- ✅ Gestion des sessions
- ✅ Historique des connexions
- ✅ Logs d'activité

**Exemples CAU**:

- Super Admin: `ADMIN000001`
- Admin Entreprise: `TECH001234`
- Utilisateur: `USER005678`

**Utilisation**:

```php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new AuthCAU($pdo);

// Connexion
$result = $auth->loginWithCAU('USER001234');

// Créer un utilisateur
$result = $auth->createUser([
    'name' => 'Konan Yao',
    'phone' => '+22501020304',
    'role' => 'user',
    'company_id' => null,
    'referred_by' => 5 // ID du parrain (optionnel)
]);
```

---

### 2️⃣ Système de Permissions

**Fichier**: `includes/permissions.php`

**Classes principales**:

- `PermissionManager` - Gestion des permissions

**Permissions par rôle**:

**Super Admin** (29 permissions):

- Gestion totale: entreprises, utilisateurs, produits, avis
- Accès statistiques globales
- Gestion système et paramètres

**Admin Entreprise** (12 permissions):

- Gestion utilisateurs de son entreprise
- Gestion produits de son entreprise
- Consultation avis de son entreprise
- Statistiques de son entreprise

**Utilisateur** (14 permissions):

- Gestion profil personnel
- Création/modification/suppression de ses avis
- Interactions (likes, recommandations)
- Tâches et portefeuille
- Parrainage

**Utilisation**:

```php
$permManager = new PermissionManager($pdo);

// Vérifier une permission
if ($permManager->hasPermission('create_product')) {
    // L'utilisateur peut créer un produit
}

// Vérifier la gestion d'une ressource
if ($permManager->canManageResource('product', $productId)) {
    // L'utilisateur peut modifier ce produit
}

// Forcer une permission (middleware)
$permManager->requirePermission('manage_settings', 'index.php?page=admin_dashboard');
```

---

### 3️⃣ Générateur Automatique de Produits

**Fichier**: `includes/product_generator.php`

**Classes principales**:

- `ProductGenerator` - Génération de produits réalistes

**Fonctionnalités**:

- ✅ 8 catégories de produits (Électronique, Mode, Maison, etc.)
- ✅ Templates de produits par catégorie
- ✅ Noms et descriptions réalistes
- ✅ Prix en FCFA cohérents
- ✅ Images depuis Unsplash
- ✅ Génération automatique planifiable

**Templates disponibles**: 40+ modèles de produits

**Utilisation**:

```php
$generator = new ProductGenerator($pdo);

// Générer 1 produit
$result = $generator->generateProduct($companyId);

// Générer 10 produits
$result = $generator->generateMultipleProducts(10);

// Génération planifiée (CRON)
$result = $generator->scheduledGeneration();
```

---

### 4️⃣ Système de Tâches Quotidiennes

**Fichier**: `includes/tasks.php`

**Classes principales**:

- `TaskSystem` - Gestion des tâches

**Règle stricte**:
❌ Un utilisateur ne peut PAS faire plusieurs fois la même tâche (sauf si `is_daily = TRUE`)

**Tâches par défaut**:

1. **Laisser un avis** - 500 FCFA
2. **Recommander un produit** - 200 FCFA
3. **Aimer un avis** - 50 FCFA
4. **Inviter un utilisateur** - 1000 FCFA (unique)
5. **Connexion quotidienne** - 100 FCFA

**Utilisation**:

```php
$taskSystem = new TaskSystem($pdo);

// Obtenir les tâches disponibles
$result = $taskSystem->getAvailableTasks($userId);

// Compléter une tâche
$result = $taskSystem->completeTask(
    $userId,
    'leave_review',
    $reviewId,
    'review'
);

// Statistiques
$stats = $taskSystem->getUserTasksStats($userId);
```

---

### 5️⃣ Système de Parrainage

**Fichier**: `includes/referrals.php`

**Classes principales**:

- `ReferralSystem` - Gestion du parrainage

**Fonctionnalités**:

- ✅ Code de parrainage unique par utilisateur
- ✅ Lien d'invitation personnalisé
- ✅ Récompense automatique (5000 FCFA par défaut)
- ✅ Statistiques de parrainage
- ✅ Partage sur réseaux sociaux

**Utilisation**:

```php
$referralSystem = new ReferralSystem($pdo);

// Obtenir le lien d'invitation
$result = $referralSystem->getReferralLink($userId);
// Retourne: https://trustpick.com/register?ref=AMA2024REF

// Valider un code
$result = $referralSystem->validateReferralCode('AMA2024REF');

// Créer le parrainage
$result = $referralSystem->createReferral($referrerId, $referredId);

// Liens de partage sociaux
$links = $referralSystem->getSocialShareLinks($userId);
// WhatsApp, Facebook, Twitter, Telegram, Email
```

---

### 6️⃣ Système de Notifications

**Fichier**: `includes/notifications.php`

**Classes principales**:

- `NotificationSystem` - Gestion des notifications

**Types de notifications**:

- `task_reminder` - Rappel de tâches
- `new_product` - Nouveaux produits
- `new_review` - Nouveaux avis
- `reward` - Récompenses
- `referral` - Parrainage
- `withdrawal` - Retraits
- `system` - Messages système

**Règle**: Minimum 2 notifications par jour par utilisateur

**Utilisation**:

```php
$notifSystem = new NotificationSystem($pdo);

// Créer une notification
$notifSystem->create(
    $userId,
    'reward',
    'Tâche complétée !',
    'Vous avez gagné 500 FCFA',
    '/tasks'
);

// Obtenir les notifications
$result = $notifSystem->getNotifications($userId, 20, 0);

// Marquer comme lu
$notifSystem->markAsRead($notificationId, $userId);

// Génération automatique quotidienne (CRON)
$notifSystem->generateDailyNotifications();
```

---

### 7️⃣ Pagination Intelligente Universelle

**Fichier**: `includes/pagination.php`

**Classes principales**:

- `SmartPagination` - Pagination universelle

**Fonctionnalités**:

- ✅ 5 éléments par défaut
- ✅ Bouton "Voir plus" (AJAX)
- ✅ Fonctionne pour TOUT (produits, avis, notifications, etc.)
- ✅ Pas de bugs de duplication
- ✅ Gère 10 à 10 000+ éléments

**Méthodes pré-configurées**:

- `paginateProducts()`
- `paginateReviews()`
- `paginateNotifications()`
- `paginateCompanies()`
- `paginateUsers()`
- `paginateTransactions()`

**Utilisation**:

```php
$pagination = new SmartPagination($pdo, 5);

// Paginer des produits
$result = $pagination->paginateProducts([
    'category_id' => 1,
    'search' => 'smartphone'
], $page);

// Paginer n'importe quoi
$result = $pagination->paginate(
    "SELECT * FROM products WHERE is_active = TRUE ORDER BY created_at DESC",
    [],
    $page
);

// Générer le HTML
echo $pagination->renderPaginationHTML(
    $result['pagination'],
    '/catalog',
    true // Mode AJAX
);

// Inclure le JavaScript
echo $pagination->renderAjaxScript();
```

---

## 🚀 GUIDE D'INSTALLATION

### Étape 1: Importer la nouvelle base de données

```bash
# Dans phpMyAdmin ou en ligne de commande
mysql -u root -p < db/schema_v2_trustpick.sql
```

Cela créera:

- Base de données `trustpick_v2`
- 12 tables
- Catégories de produits
- Configuration système
- Super Admin par défaut (CAU: `ADMIN001`)
- 3 entreprises de démo
- Utilisateurs de test

### Étape 2: Configurer la connexion

Modifier `includes/config.php`:

```php
<?php
return [
    'db_host' => '127.0.0.1',
    'db_name' => 'trustpick_v2', // ⚠️ Nouvelle BDD
    'db_user' => 'root',
    'db_pass' => ''
];
```

### Étape 3: Créer le fichier de connexion PDO

`includes/db.php`:

```php
<?php
$config = require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}
```

### Étape 4: Tester la connexion

Créer `test-v2-connection.php`:

```php
<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$auth = new AuthCAU($pdo);

echo "<h1>Test TrustPick V2</h1>";

// Test 1: Connexion BDD
echo "<p>✅ Connexion BDD réussie</p>";

// Test 2: Compter les utilisateurs
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$userCount = $stmt->fetchColumn();
echo "<p>✅ Utilisateurs: {$userCount}</p>";

// Test 3: Générer un CAU
$cau = $auth->generateCAU('user');
echo "<p>✅ CAU généré: {$cau}</p>";

echo "<h2>Comptes par défaut:</h2>";
$stmt = $pdo->query("SELECT cau, name, role FROM users");
while ($user = $stmt->fetch()) {
    echo "<p><strong>{$user['role']}</strong>: {$user['name']} (CAU: {$user['cau']})</p>";
}
```

Accéder à: `http://localhost/TrustPick/test-v2-connection.php`

---

## ⚙️ TÂCHES CRON À CONFIGURER

### 1. Génération automatique de produits (3 fois par jour)

**Fichier**: `cron/generate_products.php`

```php
<?php
require_once '../includes/db.php';
require_once '../includes/product_generator.php';

$generator = new ProductGenerator($pdo);
$result = $generator->scheduledGeneration();

echo date('Y-m-d H:i:s') . " - Produits générés: {$result['generated']}\n";
```

**CRON**:

```
# À 8h, 14h, 20h chaque jour
0 8,14,20 * * * php /path/to/TrustPick/cron/generate_products.php
```

### 2. Notifications quotidiennes (2 fois par jour)

**Fichier**: `cron/daily_notifications.php`

```php
<?php
require_once '../includes/db.php';
require_once '../includes/notifications.php';

$notifSystem = new NotificationSystem($pdo);
$result = $notifSystem->generateDailyNotifications();

echo date('Y-m-d H:i:s') . " - Notifications envoyées: {$result['notifications_generated']}\n";
```

**CRON**:

```
# À 9h et 18h chaque jour
0 9,18 * * * php /path/to/TrustPick/cron/daily_notifications.php
```

### 3. Rappels de tâches

**Fichier**: `cron/task_reminders.php`

```php
<?php
require_once '../includes/db.php';
require_once '../includes/tasks.php';
require_once '../includes/notifications.php';

$taskSystem = new TaskSystem($pdo);
$notifSystem = new NotificationSystem($pdo);

// Récupérer tous les utilisateurs actifs
$stmt = $pdo->query("SELECT id FROM users WHERE is_active = TRUE AND role = 'user'");
$users = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($users as $userId) {
    $notifSystem->sendTaskReminder($userId);
}

echo date('Y-m-d H:i:s') . " - Rappels envoyés à " . count($users) . " utilisateurs\n";
```

**CRON**:

```
# À 10h et 16h chaque jour
0 10,16 * * * php /path/to/TrustPick/cron/task_reminders.php
```

---

## 📡 ENDPOINTS API

### Authentification

#### POST `/api/auth/login.php`

```json
{
  "cau": "USER001234"
}
```

Réponse:

```json
{
  "success": true,
  "user": {
    "id": 1,
    "cau": "USER001234",
    "name": "Ama Kouadio",
    "role": "user",
    "balance": 2500
  }
}
```

#### POST `/api/auth/logout.php`

Pas de paramètres requis

---

### Produits

#### GET `/api/products/list.php?page=1&category_id=1`

Réponse:

```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 5,
    "total_items": 50,
    "has_more": true,
    "next_page": 2
  }
}
```

---

### Tâches

#### GET `/api/tasks/available.php`

Réponse:

```json
{
  "success": true,
  "tasks": [
    {
      "id": 1,
      "task_code": "leave_review",
      "task_name": "Laisser un avis",
      "reward_amount": "500 FCFA",
      "is_completed": false,
      "can_complete": true
    }
  ]
}
```

#### POST `/api/tasks/complete.php`

```json
{
  "task_code": "leave_review",
  "reference_id": 123,
  "reference_type": "review"
}
```

---

### Parrainage

#### GET `/api/referrals/my-link.php`

Réponse:

```json
{
  "success": true,
  "referral_code": "AMA2024REF",
  "referral_link": "https://trustpick.com/register?ref=AMA2024REF"
}
```

#### GET `/api/referrals/stats.php`

Réponse:

```json
{
  "success": true,
  "stats": {
    "total_referrals": 5,
    "active_referrals": 3,
    "total_rewards": 25000
  }
}
```

---

## 📊 PROCHAINES ÉTAPES

### Phase 1: Intégration Frontend ✅

- [ ] Créer les vues pour le système CAU
- [ ] Dashboard Super Admin
- [ ] Dashboard Admin Entreprise
- [ ] Dashboard Utilisateur
- [ ] Interface de parrainage
- [ ] Interface de tâches

### Phase 2: API REST Complète

- [ ] Documentation Swagger/OpenAPI
- [ ] Authentification JWT pour API mobile
- [ ] Rate limiting
- [ ] Versioning API

### Phase 3: Mobile

- [ ] Application React Native ou Flutter
- [ ] Push notifications natives
- [ ] Partage natif pour parrainage

### Phase 4: Optimisations

- [ ] Cache Redis pour statistiques
- [ ] CDN pour images
- [ ] Compression images automatique
- [ ] Search indexation (Elasticsearch)

---

## 🎯 DIFFÉRENCES AVEC LA VERSION 1

| Fonctionnalité       | V1                   | V2                             |
| -------------------- | -------------------- | ------------------------------ |
| **Authentification** | Email + Mot de passe | CAU uniquement                 |
| **Rôles**            | 3 basiques           | 3 avec permissions granulaires |
| **Produits**         | Manuels uniquement   | Auto-générés + manuels         |
| **Tâches**           | Non implémentées     | Système complet                |
| **Parrainage**       | Non implémenté       | Complet avec récompenses       |
| **Notifications**    | Basiques             | 2 min/jour automatiques        |
| **Pagination**       | Simple               | Intelligente universelle       |
| **Monnaie**          | Euro (€)             | FCFA                           |
| **Permissions**      | Basiques             | Granulaires par ressource      |

---

## 🔒 SÉCURITÉ

### Points d'attention

1. **CAU**: Génération sécurisée avec vérification d'unicité
2. **Permissions**: Vérification à chaque action sensible
3. **Transactions**: Atomiques avec BEGIN/COMMIT/ROLLBACK
4. **Logs**: Toutes les actions critiques sont loggées
5. **SQL Injection**: Protection via PDO prepared statements
6. **XSS**: Échapper toutes les sorties utilisateur

### Recommandations production

```php
// Activer HTTPS uniquement
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

// Session sécurisée
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
```

---

## 📞 SUPPORT

Pour toute question sur l'implémentation:

- Documentation technique: Ce fichier
- Exemples de code: Voir les fichiers `includes/*.php`
- Tests: Voir les fichiers `test-*.php`

---

**Créé par**: Architecte Logiciel Senior + Product Owner IA  
**Version**: 2.0.0  
**Date**: 24 janvier 2026
