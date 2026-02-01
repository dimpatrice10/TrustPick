# 🚀 Instructions de Déploiement PWA Anti-Redirect

## ❌ Problème

Les fichiers `manifest.json` et `service-worker.js` sont redirigés vers `index.php` en production.

## ✅ Solution

Utilisation du dossier `/pwa/` avec des règles .htaccess spécifiques.

## 📁 Fichiers à Uploader

### 1. Dossier PWA complet

```
public/pwa/
├── manifest.json
├── sw.js
└── .htaccess
```

### 2. Fichiers de sauvegarde à la racine

```
public/
├── pwa-manifest.json
├── pwa-worker.js
└── test-pwa-fixed.html
```

### 3. Fichiers modifiés

```
views/layouts/header.php (manifest → pwa/manifest.json)
views/layouts/footer.php (service worker → pwa/sw.js)
```

## 🧪 Test en Production

1. **Upload tous les fichiers** sur le serveur
2. **Ouvrir** `https://trustpick.excellencebertoua.org/test-pwa-fixed.html`
3. **Vérifier** que tous les tests sont ✅
4. **Si ça ne marche pas**, tester les liens directs :
   - `/pwa/manifest.json`
   - `/pwa/sw.js`
   - `/pwa-manifest.json` (fallback)
   - `/pwa-worker.js` (fallback)

## 🔧 Dépannage

### Si les fichiers sont ENCORE redirigés

Utiliser les fichiers de fallback en modifiant :

**header.php:**

```php
<link rel="manifest" href="<?= url('pwa-manifest.json') ?>">
```

**footer.php:**

```javascript
navigator.serviceWorker.register('<?= url('pwa-worker.js') ?>')
```

### Alternative ultime

Si RIEN ne marche, on peut :

1. Intégrer le manifest directement dans le HTML
2. Utiliser un data URI pour le service worker
3. Servir les fichiers via PHP avec les bons headers

## ✨ Résultat Attendu

- ✅ Aucune erreur de redirection
- ✅ Manifest valide
- ✅ Service Worker enregistré
- ✅ Application installable sur toutes plateformes
