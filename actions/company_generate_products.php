<?php
/**
 * TrustPick V2 - Action: Générer des produits en masse (Admin Entreprise)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

// Vérifier authentification admin entreprise
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin_entreprise', 'super_admin'])) {
    addToast('error', 'Accès non autorisé.');
    redirect(url('index.php?page=login'));
}

$user_id = intval($_SESSION['user_id']);
$count = intval($_POST['count'] ?? 5);
$category_id = intval($_POST['category_id'] ?? 0);
$price_min = intval($_POST['price_min'] ?? 1000);
$price_max = intval($_POST['price_max'] ?? 50000);

// Limiter à 50 produits max par génération
$count = min(50, max(1, $count));

// Valider les prix
$price_min = max(100, $price_min);
$price_max = max($price_min + 100, $price_max);

try {
    $pdo = Database::getInstance()->getConnection();

    // Récupérer l'entreprise de l'admin
    $stmt = $pdo->prepare('SELECT company_id FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $company_id = $stmt->fetchColumn();

    if (!$company_id && $_SESSION['user_role'] !== 'super_admin') {
        addToast('error', 'Vous n\'êtes pas associé à une entreprise.');
        redirect(url('index.php?page=admin_dashboard'));
    }

    // Si super_admin sans entreprise, prendre la première
    if (!$company_id) {
        $company_id = $pdo->query('SELECT id FROM companies LIMIT 1')->fetchColumn();
    }

    // Récupérer le nom de l'entreprise
    $companyStmt = $pdo->prepare('SELECT name FROM companies WHERE id = ?');
    $companyStmt->execute([$company_id]);
    $companyName = $companyStmt->fetchColumn() ?: 'Entreprise';

    // Récupérer les catégories
    $categories = $pdo->query('SELECT id, name FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);
    if (empty($categories)) {
        $categories = [0 => 'Général'];
    }

    // Noms de produits réalistes par catégorie
    $productNames = [
        'Électronique' => ['Smartphone Pro', 'Écouteurs Bluetooth', 'Tablette HD', 'Montre Connectée', 'Enceinte Portable', 'Câble USB-C', 'Chargeur Rapide', 'Batterie Externe', 'Casque Audio', 'TV LED'],
        'Mode & Accessoires' => ['Chemise Slim', 'Jean Premium', 'Robe Élégante', 'Sac à Main', 'Montre Classic', 'Ceinture Cuir', 'Lunettes Soleil', 'Chapeau Panama', 'Écharpe Soie', 'Bracelet Or'],
        'Maison & Jardin' => ['Canapé 3 Places', 'Table Basse', 'Lampe Design', 'Tapis Berbère', 'Cadre Photo', 'Plante Verte', 'Coussin Déco', 'Miroir Mural', 'Vase Céramique', 'Horloge Murale'],
        'Alimentation' => ['Café Premium', 'Thé Vert Bio', 'Chocolat Artisanal', 'Huile d\'Olive', 'Miel Naturel', 'Épices Exotiques', 'Riz Basmati', 'Pâtes Italiennes', 'Confiture Maison', 'Sauce Piquante'],
        'Santé & Beauté' => ['Crème Hydratante', 'Parfum Élégance', 'Shampooing Bio', 'Maquillage Kit', 'Brosse à Dents', 'Savon Naturel', 'Huile Essentielle', 'Masque Visage', 'Déodorant Fresh', 'Crème Solaire'],
        'Sports & Loisirs' => ['Ballon Football', 'Raquette Tennis', 'Tapis Yoga', 'Haltères 5kg', 'Vélo Ville', 'Tente Camping', 'Sac Sport', 'Gourde Inox', 'Montre GPS', 'Corde à Sauter'],
        'Livres & Culture' => ['Roman Bestseller', 'Livre Cuisine', 'BD Collector', 'Magazine Mode', 'Album Photo', 'Jeu Société', 'Puzzle 1000p', 'Carnet Notes', 'Stylo Plume', 'Globe Terrestre'],
        'Automobile' => ['Housse Siège', 'Tapis Auto', 'Désodorisant', 'Chargeur Voiture', 'Support Téléphone', 'Kit Nettoyage', 'Huile Moteur', 'Pneu Premium', 'Batterie 12V', 'GPS Navigation']
    ];

    $defaultNames = ['Produit Premium', 'Article de Qualité', 'Nouveauté', 'Best-seller', 'Exclusivité', 'Offre Spéciale', 'Collection', 'Édition Limitée', 'Top Vente', 'Coup de Cœur'];

    $pdo->beginTransaction();
    $created = 0;

    for ($i = 0; $i < $count; $i++) {
        // Sélectionner catégorie
        $catId = $category_id ?: array_rand($categories);
        $catName = $categories[$catId] ?? 'Général';

        // Générer nom de produit
        $names = $productNames[$catName] ?? $defaultNames;
        $baseName = $names[array_rand($names)];
        $productName = $baseName . ' ' . $companyName . ' #' . rand(100, 999);

        // Générer slug unique
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $productName)) . '-' . uniqid();

        // Prix aléatoire dans la fourchette
        $price = rand($price_min, $price_max);
        // Arrondir à 100 FCFA
        $price = round($price / 100) * 100;

        // Description
        $description = "Découvrez le $baseName de $companyName. Un produit de qualité supérieure conçu pour répondre à vos besoins quotidiens. Livraison rapide et garantie satisfait ou remboursé.";

        // Insérer le produit
        $stmt = $pdo->prepare('
            INSERT INTO products (company_id, category_id, title, slug, description, price, is_auto_generated, is_active, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, TRUE, TRUE, ?, NOW())
        ');
        $stmt->execute([$company_id, $catId ?: null, $productName, $slug, $description, $price, $user_id]);
        $created++;
    }

    $pdo->commit();

    addToast('success', "$created produits générés avec succès !");

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur lors de la génération: ' . $e->getMessage());
}

redirect(url('index.php?page=admin_dashboard'));
