# PWA TrustPick - Guide de Déploiement

## 📱 Application Web Progressive

TrustPick est maintenant une PWA complète, installable sur toutes les plateformes :

- **iOS** (Safari) - Instructions guidées
- **Android** (Chrome, Edge, Samsung Internet) - Installation native
- **Windows** (Chrome, Edge) - Installation native
- **macOS** (Chrome, Safari) - Installation native
- **Linux** (Chrome, Firefox) - Support navigateur

## 🚀 Déploiement

### Fichiers PWA

| Fichier             | Description                                  |
| ------------------- | -------------------------------------------- |
| `manifest.json`     | Manifest PWA (généré automatiquement)        |
| `service-worker.js` | Service Worker (généré automatiquement)      |
| `offline.html`      | Page hors ligne élégante                     |
| `pwa-install.js`    | Gestionnaire d'installation multi-plateforme |

### Scripts de Génération

| Script                     | Usage                                             |
| -------------------------- | ------------------------------------------------- |
| `build-pwa.php`            | Génère les fichiers pour environnement LOCAL      |
| `build-pwa-production.php` | Génère les fichiers pour environnement PRODUCTION |
| `deploy-pwa.php`           | Script de déploiement automatique                 |

### Utilisation

#### Environnement Local

```bash
cd public/
php build-pwa.php
```

#### Environnement de Production

```bash
cd public/
php deploy-pwa.php production
```

#### Déploiement Automatique

```bash
cd public/
php deploy-pwa.php auto  # Détecte automatiquement l'environnement
```

## 🔧 Configuration

### Différences d'Environnement

**Local** (`localhost/TrustPick/public/`)

- Scope: `/TrustPick/public/`
- URLs: `/TrustPick/public/...`

**Production** (`trustpick.excellencebertoua.org`)

- Scope: `/` (racine)
- URLs: `/...`

### Fichiers Générés

Les scripts créent automatiquement :

- `manifest.json` - Manifest avec les bonnes URLs
- `service-worker.js` - Service Worker avec les bons chemins
- `manifest-production.json` - Version production
- `service-worker-production.js` - Version production

## 🛠️ Dépannage

### Erreurs Communes

1. **"The script resource is behind a redirect"**
   - ✅ **CORRIGÉ** : Utilise maintenant des fichiers JS statiques

2. **"Manifest: Syntax error"**
   - ✅ **CORRIGÉ** : Génère du JSON valide

3. **Chemins incorrects en production**
   - ✅ **CORRIGÉ** : Détection automatique d'environnement

### Vérification

1. Ouvrir DevTools → Application → Manifest
2. Vérifier que les icônes se chargent
3. Vérifier le Service Worker dans DevTools → Application → Service Workers
4. Tester l'installation sur mobile/desktop

## 📋 Fonctionnalités PWA

- ✅ Installation sur écran d'accueil
- ✅ Mode hors ligne avec page dédiée
- ✅ Cache intelligent des ressources
- ✅ Notifications push (préparé)
- ✅ Raccourcis d'application
- ✅ Thème et icônes adaptés
- ✅ Détection automatique de reconnexion

## 🎯 Pour les Développeurs

### Ajouter de Nouveaux Assets au Cache

Modifier dans `build-pwa.php` et `build-pwa-production.php` :

```javascript
const ASSETS_TO_CACHE = [
  // ... assets existants
  SCOPE_PATH + 'nouveau-fichier.css',
  SCOPE_PATH + 'nouveau-script.js'
];
```

### Changer la Version

Modifier `CACHE_NAME` dans les scripts de build :

```javascript
const CACHE_NAME = 'trustpick-v2.3'; // Nouvelle version
```

### Tester l'Installation

1. Chrome DevTools → Application → Manifest → "Add to homescreen"
2. Ou utiliser le bouton d'installation dans l'interface

---

✨ **TrustPick est maintenant installable sur toutes les plateformes !**
