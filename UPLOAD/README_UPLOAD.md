# 📦 FICHIERS À UPLOADER - PWA TrustPick

Ce dossier contient TOUS les fichiers à uploader sur le serveur de production.

## ⚠️ IMPORTANT

**Respectez EXACTEMENT la structure des dossiers !**

## 📁 STRUCTURE À UPLOADER

```
Votre Serveur/
├── .htaccess                    ← RACINE DU PROJET (PAS dans public/)
├── (autres fichiers existants...)
└── public/
    ├── .htaccess
    ├── pwa/
    │   ├── manifest.json
    │   └── sw.js
    ├── pwa-manifest.json        ← Fallback racine
    ├── pwa-worker.js            ← Fallback racine
    ├── offline.html
    └── test-pwa-final.php       ← Page de test
```

## 🚀 PROCÉDURE D'UPLOAD

### Méthode 1: Upload Manuel (FTP)

1. **Connectez-vous à votre serveur FTP**
   - Ouvrez FileZilla, WinSCP, ou votre client FTP
   - Connectez-vous à trustpick.excellencebertoua.org

2. **Naviguez vers la RACINE du projet**
   - Chemin probable: `/home/votre_user/trustpick/` ou `/home/votre_user/public_html/trustpick/`
   - **PAS** dans `/public/` pour le premier fichier !

3. **Uploadez dans cet ordre:**

   **a) .htaccess (PRIORITÉ MAXIMALE)**

   ```
   Source: UPLOAD/.htaccess
   Destination: /.htaccess (RACINE DU PROJET)
   ```

   **b) public/.htaccess**

   ```
   Source: UPLOAD/public/.htaccess
   Destination: /public/.htaccess
   ```

   **c) Dossier pwa/**

   ```
   Source: UPLOAD/public/pwa/manifest.json
   Destination: /public/pwa/manifest.json

   Source: UPLOAD/public/pwa/sw.js
   Destination: /public/pwa/sw.js
   ```

   **d) Fichiers fallback**

   ```
   Source: UPLOAD/public/pwa-manifest.json
   Destination: /public/pwa-manifest.json

   Source: UPLOAD/public/pwa-worker.js
   Destination: /public/pwa-worker.js
   ```

   **e) Autres fichiers**

   ```
   Source: UPLOAD/public/offline.html
   Destination: /public/offline.html

   Source: UPLOAD/public/test-pwa-final.php
   Destination: /public/test-pwa-final.php
   ```

### Méthode 2: Upload par Glisser-Déposer

Si votre client FTP supporte le glisser-déposer:

1. Ouvrez le dossier `UPLOAD/` en local
2. Naviguez vers la RACINE de votre projet sur le serveur
3. Glissez `.htaccess` (seul) vers la racine
4. Naviguez dans `/public/` sur le serveur
5. Glissez TOUT le contenu de `UPLOAD/public/` dans `/public/`

⚠️ **ATTENTION:** Ne glissez PAS tout d'un coup ! Le fichier `.htaccess` de la racine doit aller HORS de `/public/`

## ✅ VÉRIFICATION POST-UPLOAD

### 1. Vérifier la structure

Connectez-vous au serveur et vérifiez:

```
/.htaccess                         ← Doit exister HORS de public/
/public/.htaccess                  ← Dans public/
/public/pwa/manifest.json          ← Dans pwa/
/public/pwa/sw.js                  ← Dans pwa/
/public/pwa-manifest.json          ← Dans public/
/public/pwa-worker.js              ← Dans public/
/public/offline.html               ← Dans public/
/public/test-pwa-final.php         ← Dans public/
```

### 2. Ouvrir la page de test

```
https://trustpick.excellencebertoua.org/test-pwa-final.php
```

### 3. Interpréter les résultats

**✅ SUCCÈS (6-7 tests sur 7):**

```
✅ Test Manifest Racine: OK
✅ Test Manifest Pwa: OK
✅ Test SW Racine: OK
✅ Test SW Pwa: OK
✅ Test Enregistrement SW: OK
⚠️ Test Installation: Prompt disponible
✅ Test Offline: OK
```

**❌ ÉCHEC (erreurs REDIRIGÉ):**

```
❌ REDIRIGÉ ! URL: .../public/index.php
```

→ Le `.htaccess` racine n'est pas au bon endroit ou pas activé

## 🔧 DÉPANNAGE

### Problème: "REDIRIGÉ" dans les tests

**Cause:** Le `.htaccess` racine n'est pas au bon endroit

**Solution:**

1. Vérifiez que `/.htaccess` existe HORS de `/public/`
2. Vérifiez les chemins sur votre serveur
3. Contactez l'hébergeur pour confirmer où est la racine du projet

### Problème: "Permission denied"

**Cause:** Permissions fichiers incorrectes

**Solution:**

```bash
chmod 644 .htaccess
chmod 644 public/.htaccess
chmod 644 public/pwa/manifest.json
chmod 644 public/pwa/sw.js
```

### Problème: Tests échouent toujours

**Cause possible:** mod_rewrite non activé

**Solution:**

1. Contactez votre hébergeur
2. Demandez d'activer `mod_rewrite` pour Apache
3. Demandez de mettre `AllowOverride All`

## 📊 RÉSULTAT ATTENDU

Après un upload réussi:

- ✅ Page de test affiche 6-7/7 réussis
- ✅ Site principal fonctionne normalement
- ✅ Console (F12) sans erreurs PWA
- ✅ Bouton "Installer TrustPick" visible dans le footer
- ✅ Installation fonctionne sur mobile

## 📞 SUPPORT

Si vous rencontrez des problèmes:

1. **Capture d'écran** de la page test-pwa-final.php
2. **Console du navigateur** (F12 > Console)
3. **Vérification hébergeur:**
   - mod_rewrite activé ?
   - AllowOverride All ?
   - Emplacement exact de la racine du projet ?

## ✨ APRÈS LE DÉPLOIEMENT

Une fois que tous les tests passent:

1. **Testez l'installation:**
   - Mobile Android: Ouvrez le site, attendez le popup
   - Mobile iOS: Bouton "Installer TrustPick" → Instructions
   - Desktop: Icône + dans la barre d'adresse

2. **Vérifiez le mode offline:**
   - Installez l'app
   - Activez le mode Avion
   - Ouvrez l'app → Page "Vous êtes hors ligne"
   - Désactivez le mode Avion → Reconnexion auto

3. **Partagez avec vos utilisateurs !** 🎉

## 📚 DOCUMENTATION

Pour plus de détails, consultez:

- `START_HERE.md` - Guide rapide
- `DEPLOIEMENT_PWA_FINAL.md` - Guide complet
- `FICHIERS_A_UPLOADER.md` - Checklist détaillée

---

**Bonne chance avec le déploiement ! 🚀**
