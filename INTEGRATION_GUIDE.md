# Guide d'Intégration - Ajouter les images à une nouvelle vue

Ce guide explique comment ajouter automatiquement les images de produits à une nouvelle vue ou un composant.

## Étape 1 : Importer la fonction helper

Au début de votre fichier Vue PHP, après `require __DIR__ . '/../includes/db.php';` :

```php
<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/image_helper.php';  // ← AJOUTER CETTE LIGNE
```

## Étape 2 : Remplacer les références d'images

### Pattern simple (image seule)

**Avant :**
```php
<img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
```

**Après :**
```php
<img src="<?= htmlspecialchars(getProductImage($product)) ?>" 
     alt="<?= htmlspecialchars($product['title']) ?>"
     onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
```

### Pattern avec fallback local existant

**Avant :**
```php
<?php $img = $product['image'] ?: '/assets/img/elements/placeholder.jpg'; ?>
<img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
```

**Après :**
```php
<img src="<?= htmlspecialchars(getProductImage($product)) ?>" 
     alt="<?= htmlspecialchars($product['title']) ?>"
     onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
```

### Pattern avec dimensions custom

**Avant :**
```php
<img src="<?= htmlspecialchars($product['image'] ?: '/assets/img/default.jpg') ?>" 
     alt="<?= htmlspecialchars($product['title']) ?>"
     width="600" height="600">
```

**Après :**
```php
<img src="<?= htmlspecialchars(getProductImage($product, 600, 600)) ?>" 
     alt="<?= htmlspecialchars($product['title']) ?>"
     width="600" height="600"
     onerror="this.src='<?= htmlspecialchars(getFallbackImage(600, 600)) ?>'">
```

## Étape 3 : Tester

1. Ouvrir votre vue dans le navigateur
2. Vérifier que les images s'affichent correctement
3. Ouvrir la console DevTools (F12 → Network)
4. Vérifier que les URLs commencent par `https://picsum.photos/`
5. Vérifier que chaque produit a la même image à chaque rechargement

## Checklist de validation

- [ ] Helper importé (`require ... image_helper.php`)
- [ ] `getProductImage($product)` utilisée à la place de `$product['image']`
- [ ] Attribut `onerror` présent pour le fallback
- [ ] Images s'affichent dans le navigateur
- [ ] Console du navigateur : pas d'erreur
- [ ] Images cohérentes lors du rechargement (pas d'aléatoire)

## Cas particuliers

### Boucle sur multiple produits

```php
<?php foreach ($products as $p): ?>
  <div class="product-card">
    <img src="<?= htmlspecialchars(getProductImage($p)) ?>" 
         alt="<?= htmlspecialchars($p['title']) ?>"
         onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
    <h3><?= htmlspecialchars($p['title']) ?></h3>
  </div>
<?php endforeach; ?>
```

### Modal ou composant dynamique

Si vous chargez l'image dynamiquement en JavaScript :

```javascript
// Au lieu de :
// imageUrl = product.image || '/assets/img/placeholder.jpg';

// Faire un appel AJAX pour obtenir l'URL :
fetch('/api/product-image.php?id=' + productId)
  .then(r => r.json())
  .then(data => {
    img.src = data.url;
    img.onerror = () => {
      img.src = 'https://dummyimage.com/400x300/e0e4e8/6c757d?text=Produit';
    };
  });
```

### Produits sans structure standard

Si votre tableau produit a une structure différente :

```php
// Au lieu de $product avec id et title
// Vous avez $item avec item_id et item_name

$adaptedProduct = [
    'id' => $item['item_id'],
    'title' => $item['item_name']
];

$imageUrl = getProductImage($adaptedProduct);
```

### Performance - Images multiples

Si vous affichez 50+ produits à la fois :

```php
<?php foreach ($products as $p): ?>
  <img src="<?= htmlspecialchars(getProductImage($p)) ?>" 
       alt="<?= htmlspecialchars($p['title']) ?>"
       loading="lazy"  <!-- ← AJOUTER lazy loading -->
       onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
<?php endforeach; ?>
```

## Dépannage

### Les images ne s'affichent pas

**Solution 1:** Vérifier l'import du helper
```php
<?php
// Ajouter en haut du fichier
require __DIR__ . '/../includes/image_helper.php';
// Chemin peut varier selon la profondeur du fichier
```

**Solution 2:** Vérifier la structure du produit
```php
<?php
// Debug : afficher la structure
echo '<pre>';
var_dump($product);
echo '</pre>';
// Vérifier que 'id' et 'title' existent
?>
```

**Solution 3:** Vérifier la console navigateur
```javascript
// F12 → Console
// Chercher des erreurs réseau ou CORS
// Les images doivent être en 200 OK
```

### Fallback s'affiche toujours

C'est normal ! Le fallback onerror s'affiche le temps que l'image externe charge. C'est une fonctionnalité, pas un bug.

Si c'est problématique :
1. Vérifier la connexion internet
2. Essayer avec une autre fonction : `getProductImageDummy($product)`
3. Vérifier que picsum.photos est accessible

### Les images changent à chaque rechargement

N'utilisez jamais d'aléatoire dans l'URL :

```php
// ❌ MAUVAIS - Image aléatoire à chaque fois
function getProductImage($product) {
    return "https://picsum.photos/" . rand(100, 500) . "/" . rand(100, 500);
}

// ✅ BON - Image déterministe
function getProductImage($product) {
    $seed = abs(crc32($product['id'] . $product['title'])) % 1000;
    return "https://picsum.photos/seed/abc{$seed}/400/300";
}
```

## Fichiers concernés

Voici les fichiers qui ont déjà été mis à jour avec les images :

- `views/catalog.php` - ✅
- `views/product.php` - ✅
- `views/home.php` - ✅
- `views/company.php` - ✅

Utilisez-les comme référence pour intégrer dans d'autres vues.

## Support

Pour toute question :

1. Consulter `IMAGE_HELPER_DOCS.md` pour la documentation complète
2. Consulter `QUICK_REFERENCE.php` pour les exemples rapides
3. Vérifier les vues déjà mises à jour (catalog.php, product.php, etc.)

## Exemples complets

### Exemple 1 : Grille de produits

```php
<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/image_helper.php';

$products = $pdo->query('SELECT * FROM products LIMIT 12')->fetchAll();
?>

<div class="grid">
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
</div>
```

### Exemple 2 : Détail avec grande image

```php
<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/image_helper.php';

$product = $pdo->query('SELECT * FROM products WHERE id = ?')->fetch();
?>

<div class="detail">
  <img src="<?= htmlspecialchars(getProductImage($product, 600, 600)) ?>"
       alt="<?= htmlspecialchars($product['title']) ?>"
       style="width: 100%; max-width: 600px; border-radius: 12px;"
       onerror="this.src='<?= htmlspecialchars(getFallbackImage(600, 600)) ?>'">
  <h1><?= htmlspecialchars($product['title']) ?></h1>
  <p><?= number_format($product['price'], 2) ?> €</p>
</div>
```

### Exemple 3 : Carrousel de produits

```php
<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/image_helper.php';

$featured = $pdo->query('SELECT * FROM products LIMIT 5')->fetchAll();
?>

<div class="carousel">
  <?php foreach ($featured as $p): ?>
    <div class="carousel-item">
      <img src="<?= htmlspecialchars(getProductImage($p, 400, 300)) ?>"
           alt="<?= htmlspecialchars($p['title']) ?>"
           loading="lazy"
           onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
    </div>
  <?php endforeach; ?>
</div>
```

## Architecture globale

```
includes/
  └─ image_helper.php          ← Logique centrale
     ├─ getProductImage()       ← Fonction principale
     ├─ getFallbackImage()      ← Fallback générique
     ├─ getProductImageDummy()  ← Alternative léger
     ├─ getProductImageUnsplash() ← Alternative premium
     └─ renderProductImage()    ← Helper HTML

views/
  ├─ catalog.php               ✅ Utilise getProductImage
  ├─ product.php               ✅ Utilise getProductImage
  ├─ home.php                  ✅ Utilise getProductImage
  └─ company.php               ✅ Utilise getProductImage
```

Cette architecture centralise toute la logique, facilitant les modifications futures et les optimisations.
