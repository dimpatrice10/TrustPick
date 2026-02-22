<?php
/**
 * TrustPick V2 - Action: Générer des entreprises en masse (Super Admin)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

// Vérifier authentification super admin
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    addToast('error', 'Accès réservé aux super administrateurs.');
    redirect(url('index.php?page=login'));
}

$user_id = intval($_SESSION['user_id']);
$count = intval($_POST['count'] ?? 5);
$sector = trim($_POST['sector'] ?? '');
$generateProducts = !empty($_POST['generate_products']);

// Limiter à 20 entreprises max par génération
$count = min(20, max(1, $count));

try {
    $pdo = Database::getInstance()->getConnection();
    $auth = new AuthCAU($pdo);

    // Noms d'entreprises réalistes africains par secteur
    $prefixes = ['Afri', 'Ébène', 'Sahel', 'Kigali', 'Lagos', 'Dakar', 'Abidjan', 'Accra', 'Bamako', 'Ouaga'];
    $suffixes = ['Tech', 'Services', 'Commerce', 'Distribution', 'Market', 'Solutions', 'Plus', 'Express', 'Pro', 'Group'];

    $sectorMapping = [
        'tech' => 'Technologies',
        'commerce' => 'Commerce',
        'services' => 'Services',
        'industrie' => 'Industrie',
        'alimentation' => 'Alimentation',
        'mode' => 'Mode',
        'sante' => 'Santé'
    ];
    $sectors = $sector ? [$sectorMapping[$sector] ?? 'Services'] : ['Électronique', 'Mode', 'Alimentation', 'Beauté', 'Sport', 'Maison', 'Auto', 'Culture', 'Santé', 'Finance'];

    // Noms de produits par secteur
    $productNames = [
        'Technologies' => ['Smartphone Pro', 'Laptop Elite', 'Tablette HD', 'Écouteurs Sans Fil', 'Montre Connectée'],
        'Commerce' => ['Pack Business', 'Lot Premium', 'Coffret Cadeau', 'Box Découverte', 'Kit Complet'],
        'Services' => ['Formation Pro', 'Consultation Expert', 'Abonnement Premium', 'Pack Support', 'Service VIP'],
        'Alimentation' => ['Café Premium', 'Thé Bio', 'Chocolat Artisanal', 'Épices Exotiques', 'Miel Naturel'],
        'Mode' => ['Chemise Élégante', 'Jean Premium', 'Robe Designer', 'Sac Cuir', 'Montre Classic'],
        'Santé' => ['Complément Vitaminé', 'Huile Essentielle', 'Crème Bio', 'Thé Détox', 'Pack Bien-être']
    ];

    $pdo->beginTransaction();
    $created = 0;

    for ($i = 0; $i < $count; $i++) {
        // Générer nom d'entreprise
        $companyName = $prefixes[array_rand($prefixes)] . $sectors[array_rand($sectors)] . ' ' . $suffixes[array_rand($suffixes)];

        // Vérifier unicité du nom
        $checkStmt = $pdo->prepare('SELECT id FROM companies WHERE name = ?');
        $checkStmt->execute([$companyName]);
        if ($checkStmt->fetch()) {
            $companyName .= ' ' . rand(1, 99);
        }

        // Générer slug
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $companyName));

        // Description
        $description = "$companyName est une entreprise leader dans son secteur, offrant des produits et services de qualité pour la clientèle africaine.";

        // Créer l'entreprise
        $stmt = $pdo->prepare('
            INSERT INTO companies (name, slug, description, is_active, created_by, created_at)
            VALUES (?, ?, ?, TRUE, ?, NOW())
        ');
        $stmt->execute([$companyName, $slug, $description, $user_id]);
        $companyId = $pdo->lastInsertId();

        // Créer un admin pour cette entreprise
        $adminName = 'Admin ' . explode(' ', $companyName)[0];
        $adminPhone = '+225' . rand(10, 99) . rand(100, 999) . rand(1000, 9999);
        $adminCAU = $auth->generateCAU('admin_entreprise');
        $adminReferralCode = $auth->generateReferralCode();

        $stmt = $pdo->prepare("
            INSERT INTO users (cau, name, phone, role, company_id, balance, referral_code, is_active, created_by, created_at)
            VALUES (?, ?, ?, 'admin_entreprise', ?, 0, ?, TRUE, ?, NOW())
        ");
        $stmt->execute([$adminCAU, $adminName, $adminPhone, $companyId, $adminReferralCode, $user_id]);

        // Générer des produits si demandé
        if ($generateProducts) {
            $selectedSector = $sectors[array_rand($sectors)];
            $products = $productNames[$selectedSector] ?? ['Produit Premium', 'Article Qualité', 'Nouveauté', 'Best-seller', 'Exclusivité'];
            $numProducts = rand(5, 10);

            for ($p = 0; $p < $numProducts; $p++) {
                $productName = $products[array_rand($products)] . ' ' . explode(' ', $companyName)[0] . ' #' . rand(100, 999);
                $productSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $productName)) . '-' . uniqid();
                $productPrice = rand(5, 100) * 1000; // 5000 à 100000 FCFA
                $productDesc = "Découvrez $productName par $companyName. Produit de qualité supérieure.";

                $pdo->prepare('
                    INSERT INTO products (company_id, title, slug, description, price, is_auto_generated, is_active, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, TRUE, TRUE, ?, NOW())
                ')->execute([$companyId, $productName, $productSlug, $productDesc, $productPrice, $user_id]);
            }
        }

        $created++;
    }

    $pdo->commit();

    $message = "$created entreprises générées avec leurs administrateurs !";
    if ($generateProducts) {
        $message .= " Des produits ont également été créés pour chaque entreprise.";
    }
    addToast('success', $message);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur lors de la génération: ' . $e->getMessage());
}

redirect(url('index.php?page=superadmin_dashboard'));
