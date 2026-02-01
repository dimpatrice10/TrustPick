# 🚀 Liste des Fichiers à Uploader - PWA TrustPick

## PRIORITÉ CRITIQUE ⚠️

Ces fichiers DOIVENT être uploadés dans CET ORDRE:

### 1. .htaccess RACINE (PRIORITÉ MAXIMALE)

```
Source locale: C:\xampp2\htdocs\TrustPick\.htaccess
Destination FTP: /.htaccess
⚠️ À LA RACINE DU PROJET, PAS dans /public/
```

### 2. .htaccess PUBLIC

```
Source locale: C:\xampp2\htdocs\TrustPick\public\.htaccess
Destination FTP: /public/.htaccess
```

### 3. Manifest PWA (Sous-dossier)

```
Source locale: C:\xampp2\htdocs\TrustPick\public\pwa\manifest.json
Destination FTP: /public/pwa/manifest.json
```

### 4. Service Worker (Sous-dossier)

```
Source locale: C:\xampp2\htdocs\TrustPick\public\pwa\sw.js
Destination FTP: /public/pwa/sw.js
```

### 5. Manifest PWA (Fallback Racine)

```
Source locale: C:\xampp2\htdocs\TrustPick\public\pwa-manifest.json
Destination FTP: /public/pwa-manifest.json
```

### 6. Service Worker (Fallback Racine)

```
Source locale: C:\xampp2\htdocs\TrustPick\public\pwa-worker.js
Destination FTP: /public/pwa-worker.js
```

### 7. Page Offline

```
Source locale: C:\xampp2\htdocs\TrustPick\public\offline.html
Destination FTP: /public/offline.html
```

### 8. Page de Test (IMPORTANT)

```
Source locale: C:\xampp2\htdocs\TrustPick\public\test-pwa-final.php
Destination FTP: /public/test-pwa-final.php
```

## VÉRIFICATION POST-UPLOAD

### Test 1: Ouvrir la page de test

```
URL: https://trustpick.excellencebertoua.org/test-pwa-final.php
Résultat attendu: 7 tests exécutés automatiquement
Score attendu: 6-7 tests réussis sur 7
```

### Test 2: Vérifier les URLs directes

```
1. https://trustpick.excellencebertoua.org/pwa/manifest.json
   → Doit afficher du JSON, PAS de redirection

2. https://trustpick.excellencebertoua.org/pwa/sw.js
   → Doit afficher du JavaScript, PAS de redirection

3. https://trustpick.excellencebertoua.org/pwa-manifest.json
   → Doit afficher du JSON (fallback)

4. https://trustpick.excellencebertoua.org/pwa-worker.js
   → Doit afficher du JavaScript (fallback)
```

### Test 3: Console navigateur

```
1. Ouvrir https://trustpick.excellencebertoua.org/
2. Appuyer sur F12 (DevTools)
3. Onglet Console
4. Chercher: "Service Worker enregistré"
5. Vérifier: Aucune erreur de redirection
```

## PERMISSIONS FICHIERS

Après upload, vérifier les permissions:

```bash
chmod 644 .htaccess
chmod 644 public/.htaccess
chmod 644 public/pwa/manifest.json
chmod 644 public/pwa/sw.js
chmod 644 public/pwa-manifest.json
chmod 644 public/pwa-worker.js
chmod 644 public/offline.html
chmod 644 public/test-pwa-final.php
```

## SI PROBLÈME PERSISTE

### Diagnostic:

1. La page test-pwa-final.php affiche des ❌ "REDIRIGÉ"
2. Le .htaccess racine n'est peut-être pas au bon endroit

### Solution:

```
Vérifiez le chemin EXACT sur le serveur:
- Mauvais: /public_html/public/.htaccess
- Mauvais: /home/user/public_html/.htaccess
- Bon: /home/user/trustpick/.htaccess (RACINE DU PROJET)
```

### Vérification Hébergeur:

```
Contactez l'hébergeur et demandez:
1. mod_rewrite est-il activé ?
2. AllowOverride est-il "All" ?
3. Le .htaccess à la racine est-il lu ?
```

## CHECKLIST FINALE

- [ ] Fichier 1 uploadé: /.htaccess
- [ ] Fichier 2 uploadé: /public/.htaccess
- [ ] Fichier 3 uploadé: /public/pwa/manifest.json
- [ ] Fichier 4 uploadé: /public/pwa/sw.js
- [ ] Fichier 5 uploadé: /public/pwa-manifest.json
- [ ] Fichier 6 uploadé: /public/pwa-worker.js
- [ ] Fichier 7 uploadé: /public/offline.html
- [ ] Fichier 8 uploadé: /public/test-pwa-final.php
- [ ] Permissions vérifiées
- [ ] Test page ouverte: test-pwa-final.php
- [ ] Tests passent à 100%
- [ ] Site principal fonctionne
- [ ] Console sans erreurs
- [ ] Installation PWA testée

## 🎉 SUCCÈS

Quand tous les tests passent:

- ✅ Plus de redirections
- ✅ Service Worker enregistré
- ✅ PWA installable
- ✅ Mode offline fonctionne
- ✅ TrustPick disponible sur toutes les plateformes !
