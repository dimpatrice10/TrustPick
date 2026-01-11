<?php
/**
 * QUICK REFERENCE - Image Helper Functions
 * 
 * Utilisation rapide des fonctions de gestion des images produits
 */

// ============================================================================
// IMPORT (à ajouter en haut de chaque vue)
// ============================================================================

require __DIR__ . '/../includes/image_helper.php';


// ============================================================================
// CAS D'USAGE COURANTS
// ============================================================================

// 1. Image standard (400x300)
$url = getProductImage($product);
// Retour: "https://picsum.photos/seed/abc123/400/300?random=42"

// 2. Image grande (600x600)
$url = getProductImage($product, 600, 600);

// 3. Image avec dimensions custom
$url = getProductImage($product, 800, 500);

// 4. Fallback générique
$fallbackUrl = getFallbackImage();

// 5. Fallback dimensionné
$fallbackUrl = getFallbackImage(600, 600);

// 6. Balise HTML complète avec fallback
echo renderProductImage($product, [
    'class' => 'card-img',
    'style' => 'border-radius: 12px;'
]);


// ============================================================================
// PATTERN STANDARD DANS LES VUES
// ============================================================================

?>
<img class="card-img" 
     src="<?= htmlspecialchars(getProductImage($product)) ?>" 
     alt="<?= htmlspecialchars($product['title']) ?>"
     onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
<?php


// ============================================================================
// ALTERNATIVES (différents services d'images)
// ============================================================================

// Option 1: Service standard (picsum.photos) - RECOMMANDÉ
$url = getProductImage($product);

// Option 2: Images générées légères (dummyimage.com)
$url = getProductImageDummy($product);
// Résultat: Blocs de couleur avec texte

// Option 3: Images de haute qualité (unsplash.com)
$url = getProductImageUnsplash($product);
// Résultat: Photos magnifiques par thème


// ============================================================================
// PROPRIÉTÉS DU PRODUIT UTILISÉES
// ============================================================================

// Minimum requis:
[
    'id' => 1,           // Utilisé pour le hash déterministe
    'title' => 'Casque' // Utilisé pour le hash déterministe
]

// Optionnels:
[
    'category' => '...',     // Utilisé par getProductImageUnsplash
    'company_name' => '...'  // Utilisé par getProductImageUnsplash
]


// ============================================================================
// BOUCLES COURANTES
// ============================================================================

// Boucle de produits avec images
foreach ($products as $p) {
    ?>
    <div class="card">
        <img src="<?= htmlspecialchars(getProductImage($p)) ?>" 
             alt="<?= htmlspecialchars($p['title']) ?>"
             onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
        <h3><?= htmlspecialchars($p['title']) ?></h3>
    </div>
    <?php
}


// ============================================================================
// DIMENSIONS RECOMMANDÉES
// ============================================================================

// Thumbnails / Cartes
getProductImage($product);           // 400x300 (défaut)
getProductImage($product, 280, 210); // Plus petit

// Détail produit
getProductImage($product, 500, 500); // 1:1 carré
getProductImage($product, 600, 400); // Horizontal

// Bannière / Hero
getProductImage($product, 1200, 600); // Très large


// ============================================================================
// NOTES DE PERFORMANCE
// ============================================================================

/**
 * Les URLs sont STATIQUES et DÉTERMINISTES:
 * - Chaque produit génère toujours la même URL
 * - Pas d'appels API supplémentaires
 * - Peut être mises en cache par CDN
 * 
 * Temps de chargement:
 * - picsum.photos: ~200-500ms (normal)
 * - dummyimage.com: ~50-100ms (très rapide)
 * - unsplash.com: ~300-800ms (variable)
 * 
 * Pour optimiser:
 * - Utiliser getProductImageDummy() pour liste longue
 * - Implémenter lazy loading (loading="lazy")
 * - Ajouter width/height pour éviter le layout shift
 */


// ============================================================================
// VÉRIFICATION / DEBUG
// ============================================================================

// Tester une image
$testProduct = ['id' => 1, 'title' => 'Test'];
$url = getProductImage($testProduct);
echo "URL testée: " . $url;
// Vérifier que l'URL s'affiche correctement

// Vérifier que le fallback fonctionne
$invalidProduct = [];
$url = getProductImage($invalidProduct); // Retournera fallback
// Doit afficher l'image générique

// Contrôler la cohérence (même URL à chaque appel)
$url1 = getProductImage($product);
$url2 = getProductImage($product);
// $url1 === $url2 (TOUJOURS)


// ============================================================================
// FICHIERS MODIFIÉS
// ============================================================================

/*
✅ includes/image_helper.php      - Créé (nouveau)
✅ views/catalog.php              - Mise à jour
✅ views/product.php              - Mise à jour  
✅ views/home.php                 - Mise à jour
✅ views/company.php              - Mise à jour
*/


// ============================================================================
// TROUBLESHOOTING
// ============================================================================

/**
 * Image ne s'affiche pas?
 * 1. Vérifier que le helper est importé
 * 2. Vérifier le produit a id et title
 * 3. Vérifier la console navigateur (erreur réseau)
 * 
 * Fallback activé?
 * 1. C'est normal, le fallback HTML aide au loading
 * 2. Vérifier onerror attribute dans le img
 * 
 * Image change à chaque rechargement?
 * 1. Ne pas utiliser random=X variable dans l'URL
 * 2. Utiliser le seed fixe basé sur le produit
 * 
 * Performance lente?
 * 1. Basculer à getProductImageDummy()
 * 2. Ajouter loading="lazy" sur les images
 * 3. Implémenter un CDN
 */
