# Solution de Gestion des Images Produits - Documentation Complète

## Vue d'ensemble

Cette solution fournit une gestion centralisée et dynamique des images de produits sans modifier la base de données. Elle utilise des services d'images en ligne fiables pour générer des images cohérentes et déterministes basées sur les données des produits.

## Architecture

### Fichier Principal : `includes/image_helper.php`

Ce fichier contient toutes les fonctions utilitaires pour gérer les images des produits.

#### Fonctions disponibles

##### 1. `getProductImage($product, $width = 400, $height = 300)`

**Fonction principale recommandée** pour obtenir une URL d'image produit.

- **Paramètres:**
  - `$product` : tableau associatif contenant au minimum `id` et `title`
  - `$width` : largeur souhaitée en pixels (par défaut: 400)
  - `$height` : hauteur souhaitée en pixels (par défaut: 300)

- **Retour:** URL d'image valide (chaîne)

- **Caractéristiques:**
  - Génère un hash déterministe basé sur l'ID et le titre du produit
  - Utilise picsum.photos pour des images réalistes et variées
  - Fallback automatique si le produit est invalide
  - Cohérent : le même produit affiche toujours la même image

**Exemple d'utilisation:**
```php
$imageUrl = getProductImage($product);
$largeImageUrl = getProductImage($product, 500, 500);
```

##### 2. `getFallbackImage($width = 400, $height = 300)`

Image générique de secours utilisée quand aucune image ne peut être générée.

- **Retour:** URL d'une image grise avec texte "Produit"
- **Utilisation:** Fallback HTML automatique dans les balises img

**Exemple:**
```php
onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'"
```

##### 3. `getProductImageDummy($product, $width = 400, $height = 300)`

Alternative : Génère des images par couleur avec texte (plus rapide).

- **Avantage:** Plus rapide, générée localement
- **Inconvénient:** Moins réaliste, texte tronqué
- **Utilisation:** Pour les cas où la vitesse est critique

```php
$imageUrl = getProductImageDummy($product);
```

##### 4. `getProductImageUnsplash($product, $width = 400, $height = 300)`

Alternative : Utilise Unsplash API pour des images de haute qualité.

- **Avantage:** Images magnifiques et professionnelles
- **Inconvénient:** Plus lent, dépend de la disponibilité d'Unsplash
- **Utilisation:** Pour les cas où la qualité est prioritaire

```php
$imageUrl = getProductImageUnsplash($product);
```

##### 5. `renderProductImage($product, $attrs = [])`

Génère un élément HTML `<img>` complet avec fallback automatique.

- **Paramètres:**
  - `$product` : tableau du produit
  - `$attrs` : tableau d'attributs HTML supplémentaires

- **Retour:** Chaîne HTML prête à afficher

**Exemple:**
```php
echo renderProductImage($product, [
    'class' => 'card-img',
    'style' => 'border-radius: 12px;'
]);
```

## Intégration dans les Vues

### Pattern d'utilisation standard

#### Avant :
```php
<?php $img = $p['image'] ?: '/assets/img/elements/placeholder.jpg'; ?>
<img class="card-img" src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
```

#### Après :
```php
<img class="card-img" 
     src="<?= htmlspecialchars(getProductImage($p)) ?>" 
     alt="<?= htmlspecialchars($p['title']) ?>"
     onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
```

### Activation dans une vue

Ajouter en haut du fichier Vue :
```php
<?php
require __DIR__ . '/../includes/image_helper.php';
```

### Vues mises à jour

Les vues suivantes utilisent maintenant `getProductImage()` :

1. **views/catalog.php** - Grille de produits du catalogue
2. **views/product.php** - Détail produit + produits similaires
3. **views/home.php** - Vitrine d'accueil et top produits
4. **views/company.php** - Produits d'une entreprise

## Fonctionnalités principales

### 1. Cohérence déterministe

Chaque produit reçoit toujours la même image grâce à un hash basé sur son ID et titre :

```php
$seed = abs(crc32($productId . '_' . $title)) % 1000;
```

**Avantage:** L'image ne change pas en rechargement, cohérence UX.

### 2. Fallback automatique

Trois niveaux de fallback :

1. Image dynamique primaire (picsum.photos)
2. Fallback HTML avec `onerror` (générique)
3. Fonction `getFallbackImage()` pour les cas invalides

```php
onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'"
```

### 3. Performances optimisées

- **URLs déterministes** : pas de requête API inutile
- **Pas d'appel base de données** : tout fonctionne localement
- **URLs statiques** : peuvent être cachées par CDN

### 4. Pas de modification BDD

- La colonne `image` dans `products` reste inchangée
- Compatible avec les images locales existantes
- Migration non-destructive

## Services utilisés

### picsum.photos (par défaut)

```
https://picsum.photos/seed/{hash}/{width}/{height}?random={seed}
```

- ✅ Images de haute qualité
- ✅ Support du seed pour cohérence
- ✅ Gratuit, pas d'authentification
- ✅ Léger et rapide
- ✅ Variété de sujets

### dummyimage.com (alternatif léger)

```
https://dummyimage.com/{width}x{height}/{bgColor}/{fgColor}?text={text}
```

- ✅ Très rapide (générée localement)
- ✅ Minimaliste
- ✅ Bon pour prototypage
- ❌ Moins réaliste

### unsplash.com (alternatif premium)

```
https://source.unsplash.com/{width}x{height}/?{keywords}&sig={seed}
```

- ✅ Images magnifiques et professionnelles
- ✅ Recherche par thème/mot-clé
- ✅ Gratuit
- ❌ Légèrement plus lent
- ❌ Limite de requêtes

## Changement de service

Pour changer le service utilisé globalement, modifiez la fonction `getProductImage()` :

```php
// Pour utiliser Unsplash
function getProductImage($product, $width = 400, $height = 300) {
    return getProductImageUnsplash($product, $width, $height);
}

// Pour utiliser DummyImage
function getProductImage($product, $width = 400, $height = 300) {
    return getProductImageDummy($product, $width, $height);
}
```

## Exemples pratiques

### Exemple 1 : Carte produit standard

```php
<?php foreach ($products as $p): ?>
  <article class="card">
    <img class="card-img" 
         src="<?= htmlspecialchars(getProductImage($p)) ?>" 
         alt="<?= htmlspecialchars($p['title']) ?>"
         onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
    <h3><?= htmlspecialchars($p['title']) ?></h3>
    <p><?= number_format($p['price'], 2) ?> €</p>
  </article>
<?php endforeach; ?>
```

### Exemple 2 : Grande image détail

```php
<img src="<?= htmlspecialchars(getProductImage($product, 600, 600)) ?>" 
     alt="<?= htmlspecialchars($product['title']) ?>"
     onerror="this.src='<?= htmlspecialchars(getFallbackImage(600, 600)) ?>'"
     style="border-radius: 12px;">
```

### Exemple 3 : Utiliser renderProductImage

```php
<?= renderProductImage($product, [
    'class' => 'product-hero-image',
    'width' => 600,
    'height' => 600,
    'style' => 'border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.15);'
]) ?>
```

## Dépannage

### L'image ne s'affiche pas

1. **Vérifier la console navigateur** : Voir les erreurs réseau
2. **Vérifier que la fonction est importée** : `require __DIR__ . '/../includes/image_helper.php';`
3. **Tester avec un produit différent** : Vérifier si le problème est spécifique
4. **Vérifier la largeur/hauteur** : Doit être > 0

### Fallback activé constamment

1. **L'image externe ne charge pas** : Service externe indisponible
2. **Problème CORS** : Rare avec les services utilisés
3. **URL malformée** : Vérifier l'appel à `getProductImage()`

### Les images changent trop souvent

1. **N'utilisez pas `random=X` variable** : Utiliser uniquement le seed fixe
2. **Vérifier la fonction** : Doit utiliser le même seed pour le même produit

### Performance lente

1. **Basculer à `getProductImageDummy()`** : Génération locale
2. **Vérifier la connexion** : Latence réseau vers les services
3. **Mettre en cache** : Implémenter un cache côté client (sessionStorage)

## Maintenance et évolution

### Ajouter un nouveau service d'images

```php
function getProductImageCustom($product, $width = 400, $height = 300) {
    $seed = abs(crc32($product['id'] . '_' . $product['title'])) % 1000;
    
    return sprintf(
        'https://monservice.com/image?id=%d&seed=%d&w=%d&h=%d',
        intval($product['id']),
        $seed,
        $width,
        $height
    );
}
```

### Amélioration future : Cache Redis

```php
function getProductImage($product, $width = 400, $height = 300) {
    $cacheKey = "img:{$product['id']}:{$width}:{$height}";
    
    // Chercher en cache
    if ($cached = $redis->get($cacheKey)) {
        return $cached;
    }
    
    // Générer et cacher
    $url = generateImageUrl($product, $width, $height);
    $redis->setex($cacheKey, 86400, $url);
    
    return $url;
}
```

### Amélioration future : Images locales + dynamiques

```php
function getProductImage($product, $width = 400, $height = 300) {
    // Priorité 1 : Image locale si elle existe
    if (!empty($product['image']) && file_exists(__DIR__ . '/../' . $product['image'])) {
        return '/' . $product['image'];
    }
    
    // Priorité 2 : Image dynamique
    return getProductImageDynamic($product, $width, $height);
}
```

## Résumé des modifications

| Fichier | Changes |
|---------|---------|
| `includes/image_helper.php` | ✅ Créé (nouveau) |
| `views/catalog.php` | ✅ Importé helper, remplacé images |
| `views/product.php` | ✅ Importé helper, remplacé images (2 emplacements) |
| `views/home.php` | ✅ Importé helper, remplacé images |
| `views/company.php` | ✅ Importé helper, remplacé images |
| `db/init.sql` | ✅ Inchangé (BDD pas modifiée) |

## Checklist de vérification

- [x] Fonction `getProductImage()` crée et testée
- [x] Fallback automatique mis en place
- [x] Tous les fichiers qui affichent les produits mises à jour
- [x] Import du helper dans toutes les vues
- [x] Pas de modification de la base de données
- [x] Code commenté et documenté
- [x] Cohérence déterministe vérifiée
- [x] Performances optimisées (URLs statiques)

## Support et Questions

Pour ajouter une nouvelle vue utilisant les images de produits :

1. Importer le helper : `require __DIR__ . '/../includes/image_helper.php';`
2. Remplacer `$product['image']` par `getProductImage($product)`
3. Ajouter le fallback onerror
4. Tester dans le navigateur
