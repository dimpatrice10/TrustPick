<?php
/**
 * TRUSTPICK V2 - Helper Images Produits
 * Système automatique d'attribution d'images avec fallback
 */

class ProductImageHelper
{
    /**
     * Génère une image automatique pour un produit basé sur son nom et description
     */
    public static function generateProductImage(string $name, string $description = '', string $category = ''): string
    {
    /**
     * Génère une image automatique pour un produit basé sur son nom et description
     */
    public static function generateProductImage(string $name, string $description = '', string $category = ''): string
    {
        // Pour l'instant, utiliser l'image générique pour tous les produits
        // Dans le futur, on peut créer des images spécifiques pour chaque catégorie
        return 'assets/img/products/default_generic.svg';
    }
    
    /**
     * Vérifie si une image existe physiquement
     */
    public static function imageExists(string $imagePath): bool
    {
        if (empty($imagePath))
            return false;

        // Chemin absolu
        $fullPath = __DIR__ . '/../public/' . ltrim($imagePath, '/');
        return file_exists($fullPath) && is_file($fullPath);
    }

    /**
     * Obtient l'URL d'image avec fallback automatique
     */
    public static function getProductImageUrl(array $product): string
    {
        // Si une image est définie et existe
        if (!empty($product['image']) && self::imageExists($product['image'])) {
            return $product['image'];
        }

        // Générer une image automatique
        return self::generateProductImage(
            $product['title'] ?? $product['name'] ?? '',
            $product['description'] ?? '',
            $product['category'] ?? ''
        );
    }

    /**
     * Crée les répertoires nécessaires pour les images
     */
    public static function ensureDirectoriesExist(): void
    {
        $dirs = [
            __DIR__ . '/../public/assets',
            __DIR__ . '/../public/assets/img',
            __DIR__ . '/../public/assets/img/products'
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Met à jour l'image d'un produit s'il n'en a pas
     */
    public static function updateProductImage(int $productId, $pdo): bool
    {
        $stmt = $pdo->prepare('SELECT id, title, description, image FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product)
            return false;

        // Si déjà une image valide, ne rien faire
        if (!empty($product['image']) && self::imageExists($product['image'])) {
            return true;
        }

        // Générer nouvelle image
        $newImage = self::generateProductImage($product['title'], $product['description']);

        // Mettre à jour en base
        $updateStmt = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
        return $updateStmt->execute([$newImage, $productId]);
    }
}

/**
 * Fonction helper pour compatibilité avec l'ancien système
 * Génère une URL d'image produit avec fallback automatique
 */
function getProductImage(array $product, int $width = 400, int $height = 300): string
{
    return ProductImageHelper::getProductImageUrl($product);
}