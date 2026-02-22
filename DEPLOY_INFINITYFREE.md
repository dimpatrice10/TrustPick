# 🚀 Guide de Déploiement TrustPick V2 sur InfinityFree

## Prérequis

- Compte InfinityFree gratuit : https://www.infinityfree.com/
- FileZilla (client FTP) : https://filezilla-project.org/
- Les fichiers du projet TrustPick V2

---

## Étape 1 : Créer un compte InfinityFree

1. Allez sur https://www.infinityfree.com/
2. Cliquez **Sign Up** et créez votre compte
3. Vérifiez votre email

## Étape 2 : Créer un hébergement

1. Dans le tableau de bord InfinityFree, cliquez **Create Account**
2. Choisissez un sous-domaine (ex: `trustpick.infinityfreeapp.com`) ou ajoutez votre propre domaine
3. Notez les informations affichées :
   - **FTP Hostname** (ex: `ftpupload.net`)
   - **FTP Username** (ex: `if0_12345678`)
   - **FTP Password** (votre mot de passe)
   - **MySQL Host** (ex: `sql123.infinityfree.com`)

## Étape 3 : Créer la base de données MySQL

1. Dans le panneau de contrôle InfinityFree, allez dans **MySQL Databases**
2. Créez une nouvelle base de données
   - Notez le **nom de la base** (ex: `if0_12345678_trustpick`)
   - Notez le **nom d'utilisateur MySQL** (ex: `if0_12345678`)
   - Notez le **mot de passe MySQL**
3. Cliquez sur **phpMyAdmin** pour ouvrir l'interface de gestion de la BDD
4. Sélectionnez votre base de données dans le menu de gauche
5. Cliquez l'onglet **Importer**
6. Choisissez le fichier `db/schema_infinityfree_import.sql`
7. Cliquez **Exécuter** — toutes les tables et données seront créées

## Étape 4 : Configurer le fichier .env

1. Copiez `.env.example` en `.env` à la racine du projet
2. Remplissez avec vos identifiants InfinityFree :

```
# Base de données MySQL InfinityFree
DB_HOST=sql123.infinityfree.com
DB_PORT=3306
DB_NAME=if0_12345678_trustpick
DB_USER=if0_12345678
DB_PASS=votre_mot_de_passe_mysql
```

> ⚠️ Remplacez les valeurs par celles de votre panneau InfinityFree !

## Étape 5 : Installer les dépendances (vendor/)

Si vous avez Composer localement :

```bash
composer install --no-dev
```

Le dossier `vendor/` doit exister avec le SDK MeSomb.

## Étape 6 : Uploader les fichiers via FTP

1. Ouvrez **FileZilla**
2. Connectez-vous avec les identifiants FTP InfinityFree :
   - **Hôte** : `ftpupload.net`
   - **Identifiant** : `if0_12345678`
   - **Mot de passe** : votre mot de passe
   - **Port** : `21`
3. Naviguez dans le dossier **htdocs/** sur le serveur distant
4. Uploadez **TOUS les fichiers** du projet TrustPick, y compris :
   - `.env` (votre configuration)
   - `.htaccess`
   - `vendor/` (le dossier de dépendances)
   - `actions/`, `includes/`, `views/`, `assets/`, `js/`, `db/`, `api/`
   - `index.php`, `composer.json`

> ⚠️ **Important** : Uploadez TOUT dans `htdocs/`, pas dans un sous-dossier !

## Étape 7 : Vérifier le site

1. Ouvrez votre navigateur
2. Allez sur `https://votredomaine.infinityfreeapp.com/`
3. Vous devriez voir la page d'accueil TrustPick
4. Testez la connexion avec le super admin :
   - **CAU** : `ADMIN001`
   - **Mot de passe** : `ADMIN001` (ou le mot de passe défini)

---

## Dépannage

### Erreur "Error establishing a database connection"

- Vérifiez les valeurs dans `.env` (host, user, pass, name)
- Testez dans phpMyAdmin que vous pouvez vous connecter

### Page blanche ou erreur 500

- Vérifiez que PHP est en version 8.x dans le panneau InfinityFree
- Vérifiez que `.htaccess` est bien uploadé
- Vérifiez les logs d'erreur dans le panneau de contrôle

### Le SDK MeSomb ne fonctionne pas

- Vérifiez que `vendor/` est bien uploadé avec tout son contenu
- Vérifiez que `vendor/autoload.php` existe sur le serveur

### Les images ne s'affichent pas

- Les images sont chargées depuis des URLs externes (Unsplash/Picsum)
- InfinityFree bloque parfois les requêtes sortantes — c'est normal
- Les images de fallback fonctionneront toujours

---

## Limitations InfinityFree (gratuit)

- **5 Go** de stockage
- **Pas de SSH** — uniquement FTP
- **Pas de cron jobs** — les tâches automatiques ne fonctionneront pas
- **50 000 hits/jour** — suffisant pour un site de démonstration
- **Mise en veille** si inactif 24h — le premier accès sera lent
- **CNAME obligatoire** pour domaines personnalisés

---

## Structure des fichiers sur le serveur

```
htdocs/
├── .env                  ← Votre configuration
├── .htaccess             ← Routing URL
├── index.php             ← Point d'entrée
├── composer.json
├── actions/              ← Actions (login, review, etc.)
├── api/                  ← Points d'API
├── assets/               ← CSS, JS, images
├── db/                   ← Schéma SQL
├── includes/             ← Config, DB, helpers
├── js/                   ← JavaScript
├── public/               ← Assets publics
├── vendor/               ← Dépendances (MeSomb SDK)
└── views/                ← Pages (home, product, etc.)
```
