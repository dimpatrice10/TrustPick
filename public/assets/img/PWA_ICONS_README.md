# Icônes PWA TrustPick

## Icônes requises pour la PWA

Pour que TrustPick soit installable sur mobile (iOS/Android), les icônes suivantes doivent être créées :

### 1. icon-192.png

- **Taille** : 192x192 pixels
- **Format** : PNG avec transparence
- **Contenu** : Logo TrustPick centré

### 2. icon-512.png

- **Taille** : 512x512 pixels
- **Format** : PNG avec transparence
- **Contenu** : Logo TrustPick centré

### Recommandations de design

- Utilisez le logo TrustPick avec fond bleu (#0066cc)
- Assurez-vous que le logo est bien visible sur fond blanc et noir
- Ajoutez du padding (~20% de la taille totale) autour du logo

### Outils recommandés

1. **Canva** : Créez un design 512x512, puis redimensionnez
2. **Figma** : Export en PNG aux deux tailles
3. **RealFaviconGenerator.net** : Génère toutes les tailles automatiquement

### Emplacement des fichiers

Les icônes doivent être placées dans :

```
public/assets/img/icon-192.png
public/assets/img/icon-512.png
```

### Test PWA

Une fois les icônes créées :

1. Ouvrez Chrome DevTools (F12)
2. Allez dans l'onglet "Application"
3. Cliquez sur "Manifest" dans le menu
4. Vérifiez que les icônes sont détectées
5. Testez l'installation avec "Add to Home Screen"
