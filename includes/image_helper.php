<?php
/**
 * Image Helper - Gestion centralisée des images produits
 * Fournit une URL d'image pertinente basée sur le nom/catégorie du produit
 * Utilise des images Unsplash permanentes (URLs stables)
 */

/**
 * Normalise un texte pour la recherche de mots-clés
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
        'û' => 'u',
        'ç' => 'c',
    ];

    $text = strtr($text, $replacements);
    $text = preg_replace('/[^a-z0-9 ]/', ' ', $text);

    return trim($text);
}

/**
 * Base d'images permanentes par mot-clé de produit.
 * Chaque entrée a plusieurs images pour varier l'affichage.
 * Les URLs sont des images Unsplash directes et stables.
 */
function getImageDatabase(): array
{
    return [
        // === ÉLECTRONIQUE ===
        'smartphone' => [
            'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=600&h=450&fit=crop',
        ],
        'galaxy' => [
            'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1585060544812-6b45742d762f?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1610792516307-ea5acd9c3b00?w=600&h=450&fit=crop',
        ],
        'laptop' => [
            'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&h=450&fit=crop',
        ],
        'ordinateur' => [
            'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&h=450&fit=crop',
        ],
        'ecouteur' => [
            'https://images.unsplash.com/photo-1590658268037-6bf12f032f55?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1572536147248-ac59a8abfa4b?w=600&h=450&fit=crop',
        ],
        'wireless' => [
            'https://images.unsplash.com/photo-1590658268037-6bf12f032f55?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?w=600&h=450&fit=crop',
        ],
        'casque' => [
            'https://images.unsplash.com/photo-1580894908361-967195033215?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&h=450&fit=crop',
        ],
        'montre connectee' => [
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1546868871-af0de0ae72be?w=600&h=450&fit=crop',
        ],
        'smartwatch' => [
            'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&h=450&fit=crop',
        ],
        'tablette' => [
            'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1585790050230-5dd28404ccb9?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1561154464-82e9aab73b87?w=600&h=450&fit=crop',
        ],
        'enceinte' => [
            'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1589003077984-894e133dabab?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1507646227500-4d389b0012be?w=600&h=450&fit=crop',
        ],
        'bluetooth' => [
            'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1590658268037-6bf12f032f55?w=600&h=450&fit=crop',
        ],
        'powerbank' => [
            'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1585338107529-13afc25806f9?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&h=450&fit=crop',
        ],
        'batterie' => [
            'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&h=450&fit=crop',
        ],
        'camera' => [
            'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?w=600&h=450&fit=crop',
        ],
        'securite' => [
            'https://images.unsplash.com/photo-1558002038-1055907df827?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1557597774-9d273605dfa9?w=600&h=450&fit=crop',
        ],
        'ecran' => [
            'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=600&h=450&fit=crop',
        ],
        'chargeur' => [
            'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=600&h=450&fit=crop',
        ],
        'souris' => [
            'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600&h=450&fit=crop',
        ],
        'clavier' => [
            'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1595225476474-87563907a212?w=600&h=450&fit=crop',
        ],
        'hub' => [
            'https://images.unsplash.com/photo-1612198188060-c7c2a3b66eae?w=600&h=450&fit=crop',
        ],
        'webcam' => [
            'https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?w=600&h=450&fit=crop',
        ],

        // === MODE & ACCESSOIRES ===
        'sac' => [
            'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=600&h=450&fit=crop',
        ],
        'sac a main' => [
            'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&h=450&fit=crop',
        ],
        'lunettes' => [
            'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1473496169904-658ba7c44d8a?w=600&h=450&fit=crop',
        ],
        'montre' => [
            'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&h=450&fit=crop',
        ],
        'ceinture' => [
            'https://images.unsplash.com/photo-1624222247344-550fb60583dc?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&h=450&fit=crop',
        ],
        'echarpe' => [
            'https://images.unsplash.com/photo-1520903920243-00d872a2d1c9?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1601924921557-45e8e0e4f105?w=600&h=450&fit=crop',
        ],
        'portefeuille' => [
            'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1559526324-c1f275fbfa32?w=600&h=450&fit=crop',
        ],

        // === MAISON & JARDIN ===
        'aspirateur' => [
            'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=600&h=450&fit=crop',
        ],
        'ventilateur' => [
            'https://images.unsplash.com/photo-1628863353691-0071c8c1874c?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1581783898382-80983a3e4b1b?w=600&h=450&fit=crop',
        ],
        'cafetiere' => [
            'https://images.unsplash.com/photo-1517668808822-9ebb02f2a0e6?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=600&h=450&fit=crop',
        ],
        'cafe' => [
            'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600&h=450&fit=crop',
        ],
        'mixeur' => [
            'https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1585515320310-259814833e62?w=600&h=450&fit=crop',
        ],
        'lampe' => [
            'https://images.unsplash.com/photo-1507473885765-e6ed057ab6fe?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1543198126-413ecc2b1054?w=600&h=450&fit=crop',
        ],
        'led' => [
            'https://images.unsplash.com/photo-1507473885765-e6ed057ab6fe?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1543198126-413ecc2b1054?w=600&h=450&fit=crop',
        ],

        // === ALIMENTATION ===
        'huile' => [
            'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1476649616092-3a4e94ed57a1?w=600&h=450&fit=crop',
        ],
        'olive' => [
            'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&h=450&fit=crop',
        ],
        'miel' => [
            'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?w=600&h=450&fit=crop',
        ],
        'riz' => [
            'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1536304993881-460e32f50669?w=600&h=450&fit=crop',
        ],
        'grains' => [
            'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1514432324607-a09d9b4aefda?w=600&h=450&fit=crop',
        ],

        // === SANTÉ & BEAUTÉ ===
        'creme' => [
            'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1570194065650-d99fb4b38b17?w=600&h=450&fit=crop',
        ],
        'hydratante' => [
            'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=600&h=450&fit=crop',
        ],
        'parfum' => [
            'https://images.unsplash.com/photo-1541643600914-78b084683601?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?w=600&h=450&fit=crop',
        ],
        'shampoing' => [
            'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&h=450&fit=crop',
        ],
        'brosse a dents' => [
            'https://images.unsplash.com/photo-1559667331-3185847d2781?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=600&h=450&fit=crop',
        ],
        'brosse' => [
            'https://images.unsplash.com/photo-1559667331-3185847d2781?w=600&h=450&fit=crop',
        ],

        // === SPORTS & LOISIRS ===
        'ballon' => [
            'https://images.unsplash.com/photo-1614632537190-23e4146777db?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=600&h=450&fit=crop',
        ],
        'football' => [
            'https://images.unsplash.com/photo-1614632537190-23e4146777db?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1552318965-6e6be7484ada?w=600&h=450&fit=crop',
        ],
        'basketball' => [
            'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1575361204480-aadea25e6e68?w=600&h=450&fit=crop',
        ],
        'volleyball' => [
            'https://images.unsplash.com/photo-1612872087720-bb876e2e67d1?w=600&h=450&fit=crop',
        ],
        'tapis' => [
            'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&h=450&fit=crop',
        ],
        'yoga' => [
            'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&h=450&fit=crop',
        ],
        'gourde' => [
            'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1523362628745-0c100150b504?w=600&h=450&fit=crop',
        ],
        'corde' => [
            'https://images.unsplash.com/photo-1434596922112-19cb4f9e2e3f?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1517963879433-6ad2b056d712?w=600&h=450&fit=crop',
        ],
        'sauter' => [
            'https://images.unsplash.com/photo-1434596922112-19cb4f9e2e3f?w=600&h=450&fit=crop',
        ],

        // === LIVRES & CULTURE ===
        'livre' => [
            'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&h=450&fit=crop',
        ],

        // === AUTOMOBILE ===
        'voiture' => [
            'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=600&h=450&fit=crop',
            'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=600&h=450&fit=crop',
        ],
        'auto' => [
            'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=600&h=450&fit=crop',
        ],
    ];
}

/**
 * Images de fallback par catégorie — utilisées quand aucun mot-clé ne match
 */
function getCategoryFallbackImages(): array
{
    return [
        'electronique' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&h=450&fit=crop',
        'mode' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=600&h=450&fit=crop',
        'accessoire' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=600&h=450&fit=crop',
        'maison' => 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=600&h=450&fit=crop',
        'jardin' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&h=450&fit=crop',
        'alimentation' => 'https://images.unsplash.com/photo-1506354666786-959d6d497f1a?w=600&h=450&fit=crop',
        'sante' => 'https://images.unsplash.com/photo-1576426863848-c21f53c60b19?w=600&h=450&fit=crop',
        'beaute' => 'https://images.unsplash.com/photo-1576426863848-c21f53c60b19?w=600&h=450&fit=crop',
        'sport' => 'https://images.unsplash.com/photo-1461896836934-bd45ba8306c7?w=600&h=450&fit=crop',
        'loisir' => 'https://images.unsplash.com/photo-1461896836934-bd45ba8306c7?w=600&h=450&fit=crop',
        'livre' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&h=450&fit=crop',
        'culture' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&h=450&fit=crop',
        'automobile' => 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=600&h=450&fit=crop',
    ];
}

/**
 * Génère une URL d'image produit basée sur le titre et la catégorie.
 * Utilise un mapping déterministe : même produit = même image.
 *
 * @param array $product Tableau contenant au minimum 'id' et 'title'
 * @param int $width Largeur souhaitée (par défaut 400)
 * @param int $height Hauteur souhaitée (par défaut 300)
 * @return string URL d'image valide
 */
function getProductImage($product, $width = 400, $height = 300)
{
    // 1. Image en base qui est une vraie URL Unsplash permanente
    if (!empty($product['image'])) {
        $img = $product['image'];
        // Vérifier que c'est une URL valide et pas une ancienne source.unsplash.com cassée
        if (str_contains($img, 'images.unsplash.com/photo-') || file_exists($img)) {
            return $img;
        }
    }

    $title = normalizeText($product['title'] ?? '');
    $category = normalizeText($product['category_name'] ?? '');
    $imageDb = getImageDatabase();

    // 2. Chercher par mots-clés dans le titre (du plus spécifique au plus général)
    // Trier les clés par longueur décroissante pour matcher les clés multi-mots en premier
    $keys = array_keys($imageDb);
    usort($keys, function ($a, $b) {
        return strlen($b) - strlen($a);
    });

    foreach ($keys as $keyword) {
        if (str_contains($title, $keyword)) {
            $images = $imageDb[$keyword];
            // Choix déterministe basé sur l'ID produit (même produit = même image)
            $productId = intval($product['id'] ?? crc32($title));
            $index = abs($productId) % count($images);
            return $images[$index];
        }
    }

    // 3. Chercher par catégorie
    if ($category) {
        $catFallbacks = getCategoryFallbackImages();
        foreach ($catFallbacks as $catKey => $catImg) {
            if (str_contains($category, $catKey)) {
                return $catImg;
            }
        }
    }

    // 4. Fallback final - image produit générique
    return getFallbackImage($width, $height);
}

/**
 * Image de fallback générique
 */
function getFallbackImage($width = 400, $height = 300)
{
    return "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w={$width}&h={$height}&fit=crop";
}

/**
 * Alternative - DummyImage avec texte personnalisé
 */
function getProductImageDummy($product, $width = 400, $height = 300)
{
    $title = isset($product['title']) ? substr($product['title'], 0, 25) : 'Produit';
    $encoded = urlencode($title);
    return sprintf('https://dummyimage.com/%dx%d/0066cc/ffffff?text=%s', $width, $height, $encoded);
}

/**
 * Obtenir HTML img complet avec fallback sur erreur
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
        '<img src="%s" alt="%s" onerror="this.src=\'%s\'" %s>',
        htmlspecialchars($src),
        $alt,
        htmlspecialchars(getFallbackImage()),
        $attrStr
    );
}
