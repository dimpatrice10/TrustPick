<?php
/**
 * TrustPick - Générateur Automatique de Produits
 * Génère des produits réalistes avec images 3 fois par jour minimum
 */

class ProductGenerator
{
    private $db;

    // Base de données de produits réalistes par catégorie
    private const PRODUCT_TEMPLATES = [
        'Électronique' => [
            ['name' => 'Smartphone Galaxy {model}', 'price_range' => [150000, 800000], 'desc' => 'Smartphone dernière génération avec écran {screen}" et {ram}GB RAM'],
            ['name' => 'Laptop {brand} {series}', 'price_range' => [300000, 1200000], 'desc' => 'Ordinateur portable {processor}, {ram}GB RAM, {storage}GB SSD'],
            ['name' => 'Écouteurs Wireless {model}', 'price_range' => [15000, 120000], 'desc' => 'Écouteurs Bluetooth avec réduction de bruit et {hours}h d\'autonomie'],
            ['name' => 'Montre Connectée {brand}', 'price_range' => [45000, 350000], 'desc' => 'Smartwatch avec GPS, suivi santé et {days} jours d\'autonomie'],
            ['name' => 'Tablette {model} {size}"', 'price_range' => [120000, 650000], 'desc' => 'Tablette avec écran {size}" {resolution} et {storage}GB'],
            ['name' => 'Enceinte Bluetooth {brand}', 'price_range' => [25000, 180000], 'desc' => 'Enceinte portable étanche avec {hours}h d\'autonomie'],
            ['name' => 'Powerbank {capacity}mAh', 'price_range' => [8000, 45000], 'desc' => 'Batterie externe {capacity}mAh avec charge rapide {watt}W'],
            ['name' => 'Caméra de Sécurité {type}', 'price_range' => [35000, 180000], 'desc' => 'Caméra {resolution} avec vision nocturne et détection de mouvement'],
        ],

        'Mode & Accessoires' => [
            ['name' => 'Sac à Main {brand} {style}', 'price_range' => [25000, 150000], 'desc' => 'Sac élégant en {material} avec multiples compartiments'],
            ['name' => 'Lunettes de Soleil {model}', 'price_range' => [15000, 85000], 'desc' => 'Lunettes UV400 avec monture {material} et design {style}'],
            ['name' => 'Montre {brand} {collection}', 'price_range' => [35000, 450000], 'desc' => 'Montre {type} avec bracelet {material} et étanchéité {atm}ATM'],
            ['name' => 'Ceinture Cuir {brand}', 'price_range' => [12000, 65000], 'desc' => 'Ceinture en cuir véritable avec boucle {finish}'],
            ['name' => 'Écharpe {material} {pattern}', 'price_range' => [8000, 45000], 'desc' => 'Écharpe douce {size}cm en {material} {origin}'],
            ['name' => 'Portefeuille {brand} RFID', 'price_range' => [15000, 75000], 'desc' => 'Portefeuille en cuir avec protection RFID et {slots} emplacements cartes'],
        ],

        'Maison & Jardin' => [
            ['name' => 'Aspirateur Robot {brand}', 'price_range' => [125000, 450000], 'desc' => 'Aspirateur intelligent avec cartographie et {runtime}min d\'autonomie'],
            ['name' => 'Ventilateur {type} {size}"', 'price_range' => [18000, 95000], 'desc' => 'Ventilateur {size}" avec {speeds} vitesses et télécommande'],
            ['name' => 'Cafetière {brand} {type}', 'price_range' => [35000, 180000], 'desc' => 'Cafetière {capacity}L programmable avec {features}'],
            ['name' => 'Mixeur {brand} {power}W', 'price_range' => [22000, 95000], 'desc' => 'Mixeur professionnel {power}W avec {accessories} accessoires'],
            ['name' => 'Lampe LED {style} {watt}W', 'price_range' => [8000, 55000], 'desc' => 'Lampe LED {watt}W avec variateur d\'intensité et {lifetime}h de durée'],
        ],

        'Alimentation' => [
            ['name' => 'Huile d\'Olive Bio {volume}L', 'price_range' => [8000, 35000], 'desc' => 'Huile d\'olive extra vierge {origin} première pression à froid'],
            ['name' => 'Miel Naturel {type} {size}g', 'price_range' => [4000, 18000], 'desc' => 'Miel pur {type} récolté artisanalement, {size}g'],
            ['name' => 'Riz Parfumé {variety} {kg}kg', 'price_range' => [6000, 25000], 'desc' => 'Riz {variety} de qualité premium, cuisson {time}min'],
            ['name' => 'Café en Grains {origin} {weight}g', 'price_range' => [12000, 45000], 'desc' => 'Café arabica {origin} torréfié artisanalement, {weight}g'],
        ],

        'Santé & Beauté' => [
            ['name' => 'Crème Hydratante {brand} {volume}ml', 'price_range' => [12000, 55000], 'desc' => 'Crème hydratante {skin_type} avec {ingredients}'],
            ['name' => 'Parfum {brand} {collection} {volume}ml', 'price_range' => [25000, 180000], 'desc' => 'Eau de parfum {notes} longue tenue {volume}ml'],
            ['name' => 'Shampoing {brand} {type} {volume}ml', 'price_range' => [5000, 28000], 'desc' => 'Shampoing {type} sans {excludes}, {volume}ml'],
            ['name' => 'Brosse à Dents Électrique {brand}', 'price_range' => [15000, 85000], 'desc' => 'Brosse électrique avec {modes} modes et {battery} jours d\'autonomie'],
        ],

        'Sports & Loisirs' => [
            ['name' => 'Ballon de {sport} {brand} Taille {size}', 'price_range' => [8000, 35000], 'desc' => 'Ballon officiel en {material} haute résistance'],
            ['name' => 'Tapis de Yoga {brand} {thickness}mm', 'price_range' => [12000, 45000], 'desc' => 'Tapis antidérapant {thickness}mm écologique {size}cm'],
            ['name' => 'Gourde Sport {capacity}L {brand}', 'price_range' => [6000, 25000], 'desc' => 'Gourde isotherme {capacity}L sans BPA, garde {temp}h'],
            ['name' => 'Corde à Sauter {type} {brand}', 'price_range' => [4000, 18000], 'desc' => 'Corde à sauter {type} ajustable avec compteur {features}'],
        ],
    ];

    // Variables pour la personnalisation
    private const VARIABLES = [
        'model' => ['A50', 'S21', 'Pro Max', 'Ultra', 'Plus', 'Z Fold', 'Note 20'],
        'brand' => ['Samsung', 'Apple', 'Huawei', 'Xiaomi', 'Oppo', 'Realme', 'Sony'],
        'series' => ['Elite', 'ProBook', 'IdeaPad', 'VivoBook', 'Pavilion', 'Inspiron'],
        'processor' => ['Intel i5', 'Intel i7', 'AMD Ryzen 5', 'AMD Ryzen 7', 'M1', 'M2'],
        'ram' => ['4', '6', '8', '12', '16', '32'],
        'storage' => ['128', '256', '512', '1024'],
        'screen' => ['5.5', '6.1', '6.5', '6.7', '6.9'],
        'resolution' => ['Full HD', '2K', '4K', 'Retina'],
        'hours' => ['10', '15', '20', '24', '30', '48'],
        'days' => ['3', '5', '7', '10', '14'],
        'size' => ['10', '11', '12.9', '13', '15', '17'],
        'capacity' => ['10000', '20000', '30000', '50000'],
        'watt' => ['18', '22', '30', '45', '65'],
        'type' => ['Intérieure', 'Extérieure', '360°', 'PTZ'],
        'material' => ['cuir', 'toile', 'nylon', 'aluminium', 'acier'],
        'style' => ['moderne', 'classique', 'vintage', 'sport'],
        'finish' => ['argentée', 'dorée', 'noire', 'bronze'],
        'pattern' => ['uni', 'rayé', 'à carreaux', 'imprimé'],
        'origin' => ['d\'Italie', 'd\'Espagne', 'du Maroc', 'de France'],
        'slots' => ['6', '8', '12', '15'],
        'runtime' => ['90', '120', '150', '180'],
        'speeds' => ['3', '5', '7', '9'],
        'power' => ['600', '800', '1000', '1200'],
        'volume' => ['50', '100', '250', '500', '750', '1000'],
        'weight' => ['250', '500', '1000'],
        'kg' => ['1', '2', '5', '10'],
        'variety' => ['Basmati', 'Jasmin', 'Complet', 'Thaï'],
        'time' => ['10', '15', '20', '25'],
        'skin_type' => ['peaux sèches', 'peaux grasses', 'peaux mixtes', 'tous types'],
        'ingredients' => ['acide hyaluronique', 'vitamine C', 'rétinol', 'aloe vera'],
        'notes' => ['florales', 'boisées', 'fruitées', 'orientales'],
        'excludes' => ['sulfates', 'parabènes', 'silicones'],
        'modes' => ['2', '3', '5'],
        'battery' => ['7', '14', '21', '30'],
        'sport' => ['Football', 'Basketball', 'Volleyball', 'Handball'],
        'thickness' => ['4', '6', '8', '10'],
        'temp' => ['12', '18', '24'],
        'features' => ['digital', 'sans fil', 'avec poignées ergonomiques'],
        'lifetime' => ['25000', '30000', '50000'],
        'accessories' => ['3', '5', '7'],
        'atm' => ['3', '5', '10', '20'],
    ];

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Générer un produit aléatoire réaliste
     */
    public function generateProduct($companyId = null)
    {
        try {
            // Si pas de companyId, prendre une entreprise au hasard
            if (!$companyId) {
                $stmt = $this->db->query("SELECT id FROM companies WHERE is_active = TRUE ORDER BY RAND() LIMIT 1");
                $companyId = $stmt->fetchColumn();
            }

            if (!$companyId) {
                return ['success' => false, 'message' => 'Aucune entreprise active'];
            }

            // Choisir une catégorie au hasard
            $categoryStmt = $this->db->query("SELECT id, name FROM categories ORDER BY RAND() LIMIT 1");
            $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                return ['success' => false, 'message' => 'Aucune catégorie disponible'];
            }

            // Obtenir les templates de cette catégorie
            $templates = self::PRODUCT_TEMPLATES[$category['name']] ?? self::PRODUCT_TEMPLATES['Électronique'];
            $template = $templates[array_rand($templates)];

            // Générer le nom du produit
            $productName = $this->replaceVariables($template['name']);

            // Générer la description
            $description = $this->replaceVariables($template['desc']);

            // Générer le prix
            $price = random_int($template['price_range'][0], $template['price_range'][1]);

            // Générer le slug
            $slug = $this->generateSlug($productName);

            // Télécharger une image depuis Unsplash ou utiliser placeholder
            $image = $this->getProductImage($category['name'], $productName);

            // Insérer le produit
            $stmt = $this->db->prepare("
                INSERT INTO products 
                (company_id, category_id, title, slug, description, price, image, is_auto_generated)
                VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)
            ");

            $stmt->execute([
                $companyId,
                $category['id'],
                $productName,
                $slug,
                $description,
                $price,
                $image
            ]);

            $productId = $this->db->lastInsertId();

            return [
                'success' => true,
                'product_id' => $productId,
                'product_name' => $productName,
                'price' => $price
            ];

        } catch (Exception $e) {
            error_log("Erreur génération produit: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Générer plusieurs produits
     */
    public function generateMultipleProducts($count = 10, $companyId = null)
    {
        $results = [];
        $successCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $result = $this->generateProduct($companyId);
            if ($result['success']) {
                $successCount++;
            }
            $results[] = $result;

            // Petite pause pour éviter de surcharger
            usleep(100000); // 0.1 seconde
        }

        return [
            'success' => true,
            'total' => $count,
            'generated' => $successCount,
            'failed' => $count - $successCount,
            'details' => $results
        ];
    }

    /**
     * Remplacer les variables dans un texte
     */
    private function replaceVariables($text)
    {
        preg_match_all('/{(\w+)}/', $text, $matches);

        foreach ($matches[1] as $variable) {
            if (isset(self::VARIABLES[$variable])) {
                $value = self::VARIABLES[$variable][array_rand(self::VARIABLES[$variable])];
                $text = str_replace('{' . $variable . '}', $value, $text);
            }
        }

        return $text;
    }

    /**
     * Générer un slug unique
     */
    private function generateSlug($text)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));

        // Vérifier si le slug existe déjà
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE slug LIKE ?");
        $stmt->execute([$slug . '%']);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        return $slug;
    }

    /**
     * Obtenir une image pour le produit
     */
    private function getProductImage($category, $productName)
    {
        // Mapping des catégories vers les mots-clés Unsplash
        $categoryKeywords = [
            'Électronique' => ['technology', 'gadget', 'electronics', 'smartphone', 'laptop'],
            'Mode & Accessoires' => ['fashion', 'accessories', 'bag', 'watch', 'sunglasses'],
            'Maison & Jardin' => ['home', 'furniture', 'appliance', 'decor'],
            'Alimentation' => ['food', 'cooking', 'ingredient', 'organic'],
            'Santé & Beauté' => ['beauty', 'cosmetics', 'skincare', 'health'],
            'Sports & Loisirs' => ['sports', 'fitness', 'outdoor', 'exercise'],
            'Livres & Culture' => ['books', 'reading', 'culture', 'art'],
            'Automobile' => ['car', 'automotive', 'vehicle', 'transport'],
        ];

        $keywords = $categoryKeywords[$category] ?? ['product'];
        $keyword = $keywords[array_rand($keywords)];

        // Option 1: Utiliser Unsplash (nécessite une clé API)
        // return $this->fetchUnsplashImage($keyword);

        // Option 2: Utiliser un placeholder avec le bon mot-clé
        $randomId = random_int(1, 1000);
        return "https://source.unsplash.com/800x600/?" . urlencode($keyword) . "&sig=" . $randomId;

        // Option 3: Utiliser un placeholder local
        // return "assets/img/products/placeholder-" . ($randomId % 10) . ".jpg";
    }

    /**
     * Planifier la génération automatique (à appeler via CRON)
     */
    public function scheduledGeneration()
    {
        // Vérifier la dernière génération
        $stmt = $this->db->query("
            SELECT MAX(created_at) as last_gen 
            FROM products 
            WHERE is_auto_generated = TRUE
        ");
        $lastGen = $stmt->fetchColumn();

        // Vérifier les paramètres système
        $freqStmt = $this->db->prepare("
            SELECT setting_value 
            FROM system_settings 
            WHERE setting_key = 'products_generation_frequency'
        ");
        $freqStmt->execute();
        $frequency = $freqStmt->fetchColumn() ?: 3;

        // Calculer le nombre de produits à générer
        $productsPerGeneration = 5; // 5 produits par génération

        $result = $this->generateMultipleProducts($productsPerGeneration);

        // Logger l'activité
        error_log("Génération automatique de produits: {$result['generated']} produits créés");

        return $result;
    }
}
