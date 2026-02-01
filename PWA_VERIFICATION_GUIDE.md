# TrustPick PWA - Guide de Vérification et Installation

## ✅ Fichiers PWA Créés/Modifiés

| Fichier           | Emplacement                        | Status             |
| ----------------- | ---------------------------------- | ------------------ |
| manifest.json     | `/public/manifest.json`            | ✅ Complet         |
| service-worker.js | `/public/service-worker.js`        | ✅ v2.1            |
| pwa-install.js    | `/public/assets/js/pwa-install.js` | ✅ Complet         |
| header.php        | `/views/layouts/header.php`        | ✅ Meta tags PWA   |
| footer.php        | `/views/layouts/footer.php`        | ✅ SW registration |
| app.css           | `/public/assets/css/app.css`       | ✅ Styles PWA      |
| icon-192.png      | `/public/assets/img/icon-192.png`  | ✅ Existant        |
| icon-512.png      | `/public/assets/img/icon-512.png`  | ✅ Existant        |

---

## 🔍 Tests de Vérification

### 1. Vérifier l'accessibilité des fichiers

Ouvrez ces URLs dans votre navigateur :

```
http://localhost/TrustPick/public/manifest.json
http://localhost/TrustPick/public/service-worker.js
http://localhost/TrustPick/public/assets/img/icon-192.png
http://localhost/TrustPick/public/assets/img/icon-512.png
```

Chaque fichier doit s'afficher correctement (pas d'erreur 404).

### 2. Vérifier dans Chrome DevTools

1. Ouvrez `http://localhost/TrustPick/public/`
2. F12 → Onglet **Application**
3. Section **Manifest** : Vérifiez que le manifest est détecté
4. Section **Service Workers** : Vérifiez l'enregistrement

### 3. Lighthouse PWA Audit

1. Chrome DevTools → **Lighthouse**
2. Cochez "Progressive Web App"
3. Cliquez "Analyze page load"
4. Score PWA cible : > 90%

---

## 📱 Installation sur Appareils

### Android (Chrome)

1. Ouvrir `http://localhost/TrustPick/public/` (ou votre domaine)
2. Un bouton **"Installer"** apparaîtra dans la navbar
3. Cliquez dessus → Popup Chrome d'installation
4. OU : Menu Chrome ⋮ → "Installer l'application"

### iOS (Safari)

1. Ouvrir le site dans Safari
2. Une bannière apparaît en bas : "Installer TrustPick"
3. Appuyer sur le bouton Partager (carré avec flèche)
4. Choisir "Sur l'écran d'accueil"
5. Nommer l'app et confirmer

### Desktop (Chrome/Edge)

1. Ouvrir le site
2. Icône d'installation dans la barre d'adresse (ou bouton dans navbar)
3. Cliquer pour installer

---

## 🛠️ Résolution de Problèmes

### Le bouton d'installation n'apparaît pas

1. **Vérifier HTTPS** : PWA nécessite HTTPS (ou localhost)
2. **Manifest valide** : Vérifier dans DevTools → Application → Manifest
3. **Service Worker** : Vérifier l'enregistrement dans DevTools
4. **Déjà installé** : Si déjà installé, le bouton est masqué

### Service Worker ne s'enregistre pas

1. Vérifier la console pour erreurs
2. Vérifier le chemin : `/TrustPick/public/service-worker.js`
3. Vérifier le scope : `/TrustPick/public/`

### Icônes manquantes

Les icônes doivent être :

- Format PNG
- Tailles exactes : 192x192 et 512x512
- Accessibles via URL absolue

---

## 📋 Checklist Finale

- [ ] manifest.json accessible
- [ ] service-worker.js accessible
- [ ] Icônes 192x192 et 512x512 présentes
- [ ] Meta tags dans header.php
- [ ] SW enregistré dans footer.php
- [ ] Bouton install dans navbar
- [ ] Bannière iOS fonctionnelle
- [ ] Chrome affiche option d'installation
- [ ] Lighthouse PWA score > 90%

---

## 🎉 Fonctionnalités Implémentées

1. **Installation Android** : Bouton automatique + beforeinstallprompt
2. **Installation iOS** : Bannière avec instructions Safari
3. **Installation Desktop** : Bouton dans navbar
4. **Cache Offline** : Pages principales et assets
5. **Mode Standalone** : Interface sans barres navigateur
6. **Shortcuts** : Accès rapide Catalogue et Mon compte
7. **Theme Color** : #0d6efd (bleu Bootstrap)
8. **Safe Area** : Support iPhone X+ (notch)

---

_Document généré le 1er février 2026_
_TrustPick V2 - PWA Ready_
