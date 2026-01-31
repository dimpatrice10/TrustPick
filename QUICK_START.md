# 🚀 TrustPick V2 - Guide de Démarrage Rapide

## ⚡ Installation en 5 Minutes

### Étape 1: Importer la base de données

1. Ouvrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Cliquer sur "Importer"
3. Sélectionner le fichier: `db/schema_v2_trustpick.sql`
4. Cliquer sur "Exécuter"

✅ Résultat: Base `trustpick_v2` créée avec 12 tables

### Étape 2: Tester le backend

Ouvrir dans le navigateur:

```
http://localhost/TrustPick/test-v2-complete.php
```

✅ Résultat: Tous les tests doivent afficher ✅

### Étape 3: Tester l'API

Ouvrir Postman ou utiliser cURL:

**Connexion:**

```bash
curl -X POST http://localhost/TrustPick/api/v2/auth-login.php \
  -H "Content-Type: application/json" \
  -d '{"cau":"USER001"}'
```

**Liste des produits:**

```bash
curl http://localhost/TrustPick/api/v2/products-list.php?page=1
```

✅ Résultat: Réponses JSON valides

---

## 🔑 Comptes de Test

| Rôle             | CAU        | Nom                  | Utilisation           |
| ---------------- | ---------- | -------------------- | --------------------- |
| Super Admin      | `ADMIN001` | Super Administrateur | Gestion globale       |
| Admin Entreprise | `TECH001`  | Kouassi Admin        | Gestion TechnoPlus CI |
| Utilisateur      | `USER001`  | Ama Kouadio          | Tests utilisateur     |
| Utilisateur      | `USER002`  | Yao Koffi            | Tests utilisateur     |

---

## 🧪 Tests Rapides

### Test 1: Génération de produit

```bash
php -r "
require 'includes/db.php';
require 'includes/product_generator.php';
\$gen = new ProductGenerator(\$pdo);
\$result = \$gen->generateProduct(1);
echo json_encode(\$result, JSON_PRETTY_PRINT);
"
```

### Test 2: Tâches disponibles

```bash
php -r "
require 'includes/db.php';
require 'includes/tasks.php';
\$tasks = new TaskSystem(\$pdo);
\$result = \$tasks->getAvailableTasks(3);
echo json_encode(\$result, JSON_PRETTY_PRINT);
"
```

### Test 3: Lien de parrainage

```bash
php -r "
require 'includes/db.php';
require 'includes/referrals.php';
\$ref = new ReferralSystem(\$pdo);
\$result = \$ref->getReferralLink(3);
echo json_encode(\$result, JSON_PRETTY_PRINT);
"
```

---

## ⚙️ Configuration CRON (Optionnel)

### Windows - Planificateur de tâches

1. Ouvrir "Planificateur de tâches"
2. Créer une tâche de base
3. Déclencheur: Quotidien à 8h
4. Action:
   - Programme: `C:\xampp2\php\php.exe`
   - Arguments: `C:\xampp2\htdocs\TrustPick\cron\generate_products.php`

Répéter pour:

- `daily_notifications.php` (9h et 18h)
- `task_reminders.php` (10h et 16h)

### Linux/Mac - Crontab

```bash
crontab -e
```

Ajouter:

```bash
0 8,14,20 * * * php /path/to/TrustPick/cron/generate_products.php
0 9,18 * * * php /path/to/TrustPick/cron/daily_notifications.php
0 10,16 * * * php /path/to/TrustPick/cron/task_reminders.php
```

---

## 📁 Structure des Fichiers

```
TrustPick/
├── db/
│   └── schema_v2_trustpick.sql      # Base de données V2
├── includes/
│   ├── auth.php                     # Système CAU
│   ├── permissions.php              # Permissions
│   ├── product_generator.php        # Génération produits
│   ├── tasks.php                    # Système de tâches
│   ├── referrals.php                # Parrainage
│   ├── notifications.php            # Notifications
│   └── pagination.php               # Pagination
├── api/v2/
│   ├── auth-login.php               # API Login
│   ├── tasks-available.php          # API Tâches
│   ├── tasks-complete.php           # API Compléter tâche
│   ├── referrals-my-link.php        # API Parrainage
│   ├── referrals-stats.php          # API Stats
│   ├── products-list.php            # API Produits
│   └── notifications-list.php       # API Notifications
├── cron/
│   ├── generate_products.php        # CRON Produits
│   ├── daily_notifications.php      # CRON Notifications
│   ├── task_reminders.php           # CRON Rappels
│   └── CRON_SETUP.md                # Guide CRON
├── test-v2-complete.php             # Tests complets
├── IMPLEMENTATION_GUIDE_V2.md       # Guide complet
├── RECAP_V2.md                      # Récapitulatif
└── QUICK_START.md                   # Ce fichier
```

---

## 🎯 Prochaines Étapes

### 1. Développement Frontend

- [ ] Page de connexion CAU
- [ ] Dashboards (Admin, Entreprise, User)
- [ ] Catalogue de produits
- [ ] Interface de tâches
- [ ] Interface de parrainage

### 2. Intégration

- [ ] Connecter l'API au frontend
- [ ] Implémenter la pagination AJAX
- [ ] Système de notifications en temps réel

### 3. Production

- [ ] Configurer HTTPS
- [ ] Optimiser les images
- [ ] Mettre en place le monitoring
- [ ] Déployer les CRON

---

## 🆘 Dépannage

### Erreur: "Base de données introuvable"

Vérifier `includes/config.php`:

```php
'db_name' => 'trustpick_v2', // Pas trustpick
```

### Erreur: "Class not found"

Vérifier que tous les fichiers `includes/*.php` existent.

### Les CRON ne s'exécutent pas

**Windows**: Vérifier le Planificateur de tâches  
**Linux**: `crontab -l` pour lister les tâches

### Tests échouent

1. Vérifier la connexion BDD
2. Vérifier que la BDD est bien `trustpick_v2`
3. Réimporter `schema_v2_trustpick.sql`

---

## 📚 Documentation Complète

- **Guide complet**: [IMPLEMENTATION_GUIDE_V2.md](IMPLEMENTATION_GUIDE_V2.md)
- **Récapitulatif**: [RECAP_V2.md](RECAP_V2.md)
- **Configuration CRON**: [cron/CRON_SETUP.md](cron/CRON_SETUP.md)

---

## 🎉 C'est tout !

Votre backend TrustPick V2 est maintenant opérationnel.

**Temps estimé**: 5-10 minutes  
**Niveau**: Débutant à Intermédiaire  
**Support**: Voir les fichiers de documentation

---

**Créé le**: 24 janvier 2026  
**Version**: 2.0.0  
**Auteur**: GitHub Copilot
