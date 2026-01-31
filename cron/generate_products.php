<?php
/**
 * CRON - Génération Automatique de Produits
 * À exécuter 3 fois par jour (8h, 14h, 20h)
 * 
 * CRON: 0 8,14,20 * * * php /path/to/TrustPick/cron/generate_products.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/product_generator.php';

// Vérifier que le script est exécuté en CLI
if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande\n");
}

$startTime = microtime(true);
$logFile = __DIR__ . '/logs/products_generation.log';

// Créer le dossier logs s'il n'existe pas
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

function writeLog($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "[$timestamp] $message\n";
}

writeLog("=== Début de la génération automatique de produits ===");

try {
    $generator = new ProductGenerator($pdo);

    // Récupérer le nombre de produits à générer depuis les paramètres
    $stmt = $pdo->prepare("
        SELECT setting_value 
        FROM system_settings 
        WHERE setting_key = 'products_generation_frequency'
    ");
    $stmt->execute();
    $frequency = $stmt->fetchColumn() ?: 3;

    // Générer 5 produits par génération
    $productsToGenerate = 5;

    writeLog("Génération de {$productsToGenerate} produits...");

    $result = $generator->generateMultipleProducts($productsToGenerate);

    if ($result['success']) {
        writeLog("✓ Succès: {$result['generated']} produits générés");
        writeLog("✗ Échecs: {$result['failed']} produits");

        // Logger chaque produit créé
        foreach ($result['details'] as $detail) {
            if ($detail['success']) {
                writeLog("  → {$detail['product_name']} ({$detail['price']} FCFA)");
            }
        }
    } else {
        writeLog("✗ Erreur lors de la génération");
    }

} catch (Exception $e) {
    writeLog("✗ Exception: " . $e->getMessage());
    writeLog("  Trace: " . $e->getTraceAsString());
}

$executionTime = round(microtime(true) - $startTime, 2);
writeLog("=== Fin de la génération (Durée: {$executionTime}s) ===\n");
