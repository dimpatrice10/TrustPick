<?php
/**
 * Image Helper - Gestion centralisée des images produits
 * Fournit une URL d'image dynamique en utilisant des services en ligne
 * sans modifier la base de données
 */

/**
 * Génère une URL d'image produit
 * 
 * Utilise un service d'images en ligne avec un hash déterministe
 * basé sur les données du produit pour un résultat cohérent.
 *
 * @param array $product Tableau associatif contenant au minimum 'id' et 'title'
 * @param int $width Largeur souhaitée (par défaut 400)
 * @param int $height Hauteur souhaitée (par défaut 300)
 * @return string URL d'image valide
 */

function normalizeText($text)
{
    $text = mb_strtolower($text, 'UTF-8');

    $replacements = [
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'à' => 'a',
        'â' => 'a',
        'î' => 'i',
        'ï' => 'i',
        'ô' => 'o',
        'ù' => 'u',
        'ç' => 'c'
    ];

    $text = strtr($text, $replacements);
    $text = preg_replace('/[^a-z0-9 ]/', ' ', $text);

    return trim($text);
}

function getProductImage($product, $width = 400, $height = 300)
{
    // 1. Image locale si elle existe vraiment
    if (!empty($product['image']) && file_exists($product['image'])) {
        return $product['image'];
    }

    $title = normalizeText($product['title'] ?? '');

    $map = [
        // Écran / Monitor
        ['keys' => ['ecran', 'screen', 'monitor'], 'img' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8'],

        // Casque
        ['keys' => ['casque', 'headphone'], 'img' => 'https://images.unsplash.com/photo-1580894908361-967195033215'],

        // Chargeur
        ['keys' => ['chargeur', 'charger'], 'img' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0'],

        // Sac
        ['keys' => ['sac', 'backpack'], 'img' => 'https://images.unsplash.com/photo-1622560480654-d96214fdc887'],

        // Souris
        ['keys' => ['souris', 'mouse'], 'img' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7'],

        // Clavier
        ['keys' => ['clavier', 'keyboard'], 'img' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3'],

        // Webcam
        ['keys' => ['webcam', 'camera'], 'img' => 'https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04'],

        // Hub
        ['keys' => ['hub', 'usb'], 'img' => 'https://images.unsplash.com/photo-1612198188060-c7c2a3b66eae'],
    ];

    foreach ($map as $item) {
        foreach ($item['keys'] as $key) {
            if (str_contains($title, $key)) {
                return $item['img'] . "?w=$width&h=$height&fit=crop";
            }
        }
    }

    return getFallbackImage($width, $height);
}

function getFallbackImage($width = 400, $height = 300)
{
    return "https://dummyimage.com/{$width}x{$height}/e0e4e8/6c757d&text=Produit";
}

/**
 * Image de fallback générique
 * Utilisée si aucune image ne peut être générée
 *
 * @param int $width Largeur
 * @param int $height Hauteur
 * @return string URL d'image générique
 */

/**
 * Alternative - Utilise DummyImage avec texte personnalisé
 * (Plus rapide que picsum mais moins réaliste)
 *
 * @param array $product Tableau du produit
 * @param int $width Largeur
 * @param int $height Hauteur
 * @return string URL d'image
 */
function getProductImageDummy($product, $width = 400, $height = 300)
{
    $title = isset($product['title']) ? substr($product['title'], 0, 25) : 'Produit';
    $encoded = urlencode($title);

    return sprintf(
        'https://dummyimage.com/%dx%d/0066cc/ffffff?text=%s',
        $width,
        $height,
        $encoded
    );
}

/**
 * Alternative - Utilise Unsplash (API free) avec recherche par catégorie
 * Plus belles images mais peut être plus lent
 *
 * @param array $product Tableau du produit
 * @param int $width Largeur
 * @param int $height Hauteur
 * @return string URL d'image
 */
function getProductImageUnsplash($product, $width = 400, $height = 300)
{
    $keywords = [
        'phone' => 'smartphone',
        'casque' => 'headphones',
        'chargeur' => 'charger',
        'clavier' => 'keyboard',
        'souris' => 'mouse',
        'webcam' => 'webcam',
        'écran' => 'monitor',
        'sac' => 'backpack',
        'cable' => 'cable',
        'adaptateur' => 'adapter',
        'hub' => 'hub'
    ];

    $title = strtolower($product['title'] ?? 'product');
    $searchTerm = 'product';

    // Chercher un mot-clé correspondant
    foreach ($keywords as $key => $value) {
        if (strpos($title, $key) !== false) {
            $searchTerm = $value;
            break;
        }
    }

    // Unsplash API (gratuit, pas d'authentification requise)
    $seed = abs(crc32($product['id'] . $title)) % 100;

    return sprintf(
        'https://source.unsplash.com/%dx%d/?%s&sig=%d',
        $width,
        $height,
        urlencode($searchTerm),
        $seed
    );
}

/**
 * Obtenir HTML img complet avec fallback sur erreur
 * Idéal pour les templates
 *
 * @param array $product Tableau du produit
 * @param array $attrs Attributs HTML supplémentaires (class, alt, etc.)
 * @return string HTML <img> complet
 */
function renderProductImage($product, $attrs = [])
{
    $src = getProductImage($product, $attrs['width'] ?? 400, $attrs['height'] ?? 300);
    $alt = htmlspecialchars($product['title'] ?? 'Produit');

    $attrStr = '';
    foreach ($attrs as $key => $value) {
        if ($key !== 'width' && $key !== 'height') {
            $attrStr .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
    }

    return sprintf(
        '<img src="%s" alt="%s" onerror="this.src=\'%s\'"  %s>',
        htmlspecialchars($src),
        $alt,
        htmlspecialchars(getFallbackImage()),
        $attrStr
    );
}
