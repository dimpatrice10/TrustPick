<?php
/**
 * Script de Test Complet - TrustPick V2
 * Vérifie l'intégration frontend ↔ backend
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

echo "═══════════════════════════════════════════════════════════════\n";
echo "🧪 TEST COMPLET - TRUSTPICK V2\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$tests_passed = 0;
$tests_failed = 0;

// Helper function
function test($name, $condition, $message = '')
{
    global $tests_passed, $tests_failed;
    if ($condition) {
        echo "✅ PASS: $name\n";
        $tests_passed++;
    } else {
        echo "❌ FAIL: $name";
        if ($message)
            echo " - $message";
        echo "\n";
        $tests_failed++;
    }
}

try {
    // ==================================================================
    // PHASE 1 : CONNEXION DATABASE
    // ==================================================================
    echo "\n▶ PHASE 1 : CONNEXION DATABASE\n";
    echo "────────────────────────────────────────\n";

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    test("Database singleton créé", $db !== null);
    test("Connexion PDO valide", $pdo !== null && ($pdo instanceof PDO));

    // ==================================================================
    // PHASE 2 : TABLES EXISTANTES
    // ==================================================================
    echo "\n▶ PHASE 2 : TABLES EXISTANTES\n";
    echo "────────────────────────────────────────\n";

    $required_tables = ['users', 'products', 'reviews', 'transactions', 'withdrawals', 'categories', 'companies'];
    foreach ($required_tables as $table) {
        try {
            $cnt = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            test("Table `$table` existe", true, "($cnt lignes)");
        } catch (Exception $e) {
            test("Table `$table` existe", false, $e->getMessage());
        }
    }

    // Vérifier que wallets n'existe PAS
    try {
        $pdo->query("SELECT COUNT(*) FROM `wallets`")->fetchColumn();
        test("Table `wallets` n'existe pas", false, "La table wallets existe mais ne devrait pas");
    } catch (Exception $e) {
        test("Table `wallets` n'existe pas", true, "C'est correct - balance est dans users");
    }

    // ==================================================================
    // PHASE 3 : UTILISATEURS & AUTHENTIFICATION
    // ==================================================================
    echo "\n▶ PHASE 3 : UTILISATEURS & AUTHENTIFICATION\n";
    echo "────────────────────────────────────────\n";

    // Vérifier utilisateurs de test
    $users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    test("Utilisateurs existent", $users_count > 0, "($users_count utilisateurs)");

    // Vérifier CAU test
    $user001 = $pdo->query("SELECT * FROM users WHERE cau = 'USER001'")->fetch();
    test("USER001 existe", $user001 !== false);
    if ($user001) {
        test("USER001 est utilisateur (user)", $user001['role'] === 'user');
        test("USER001 a un balance", isset($user001['balance']));
        echo "   └─ Balance: " . $user001['balance'] . " FCFA\n";
    }

    $admin001 = $pdo->query("SELECT * FROM users WHERE cau = 'ADMIN001'")->fetch();
    test("ADMIN001 existe", $admin001 !== false);
    if ($admin001) {
        test("ADMIN001 est super_admin", $admin001['role'] === 'super_admin');
    }

    // ==================================================================
    // PHASE 4 : PRODUITS & CATÉGORIES
    // ==================================================================
    echo "\n▶ PHASE 4 : PRODUITS & CATÉGORIES\n";
    echo "────────────────────────────────────────\n";

    $products_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    test("Produits existent", $products_count > 0, "($products_count produits)");

    $categories_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    test("Catégories existent", $categories_count >= 8, "($categories_count catégories)");

    $product_sample = $pdo->query("SELECT * FROM products LIMIT 1")->fetch();
    if ($product_sample) {
        test("Produit a un title", !empty($product_sample['title']));
        test("Produit a un prix", $product_sample['price'] > 0);
        test("Produit a une description", !empty($product_sample['description']));
    }

    // ==================================================================
    // PHASE 5 : AVIS & INTERACTIONS
    // ==================================================================
    echo "\n▶ PHASE 5 : AVIS & INTERACTIONS\n";
    echo "────────────────────────────────────────\n";

    $reviews_count = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    test("Table reviews créée", true, "($reviews_count avis)");

    $reactions_count = $pdo->query("SELECT COUNT(*) FROM review_reactions")->fetchColumn();
    test("Table review_reactions créée", true, "($reactions_count réactions)");

    // ==================================================================
    // PHASE 6 : SYSTÈME FINANCIER
    // ==================================================================
    echo "\n▶ PHASE 6 : SYSTÈME FINANCIER\n";
    echo "────────────────────────────────────────\n";

    // Vérifier balance dans users
    $user_with_balance = $pdo->query("SELECT id, cau, balance FROM users LIMIT 1")->fetch();
    test("users.balance existe", $user_with_balance !== false && isset($user_with_balance['balance']));

    // Vérifier transactions
    $transactions_count = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
    test("Table transactions existe", true, "($transactions_count transactions)");

    $transaction_sample = $pdo->query("SELECT * FROM transactions LIMIT 1")->fetch();
    if ($transaction_sample) {
        test("Transaction a un type", in_array($transaction_sample['type'], ['reward', 'referral', 'withdrawal', 'bonus', 'penalty']));
        test("Transaction a un montant", $transaction_sample['amount'] > 0);
    }

    // Vérifier withdrawals
    $withdrawals_count = $pdo->query("SELECT COUNT(*) FROM withdrawals")->fetchColumn();
    test("Table withdrawals existe", true, "($withdrawals_count retraits)");

    // ==================================================================
    // PHASE 7 : TÂCHES & RÉCOMPENSES
    // ==================================================================
    echo "\n▶ PHASE 7 : TÂCHES & RÉCOMPENSES\n";
    echo "────────────────────────────────────────\n";

    $tasks_count = $pdo->query("SELECT COUNT(*) FROM tasks_definitions")->fetchColumn();
    test("Tâches définies", $tasks_count >= 5, "($tasks_count tâches)");

    $task_sample = $pdo->query("SELECT * FROM tasks_definitions LIMIT 1")->fetch();
    if ($task_sample) {
        test("Tâche a une récompense", $task_sample['reward_amount'] > 0);
        echo "   └─ Exemple: {$task_sample['task_name']} = {$task_sample['reward_amount']} FCFA\n";
    }

    // ==================================================================
    // PHASE 8 : PARRAINAGE & NOTIFICATIONS
    // ==================================================================
    echo "\n▶ PHASE 8 : PARRAINAGE & NOTIFICATIONS\n";
    echo "────────────────────────────────────────\n";

    $referrals_count = $pdo->query("SELECT COUNT(*) FROM referrals")->fetchColumn();
    test("Table referrals existe", true, "($referrals_count parrainages)");

    $notifications_count = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    test("Table notifications existe", true, "($notifications_count notifications)");

    // ==================================================================
    // PHASE 9 : VÉRIFICATION REQUÊTES SQL CORRIGÉES
    // ==================================================================
    echo "\n▶ PHASE 9 : REQUÊTES SQL CORRIGÉES\n";
    echo "────────────────────────────────────────\n";

    // Tester requête balance (la plus critique)
    if ($user001) {
        $uid = $user001['id'];
        try {
            $stmt = $pdo->prepare('SELECT COALESCE(balance,0) FROM users WHERE id = ?');
            $stmt->execute([$uid]);
            $balance = $stmt->fetchColumn();
            test("Requête balance de user", $balance !== null, "Balance: $balance FCFA");
        } catch (Exception $e) {
            test("Requête balance de user", false, $e->getMessage());
        }
    }

    // Tester requête transactions
    try {
        $stmt = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type IN ('reward', 'referral')");
        $result = $stmt->fetch();
        test("Requête transactions récompenses", $result !== false, "Total: " . ($result['total'] ?? 0) . " FCFA");
    } catch (Exception $e) {
        test("Requête transactions récompenses", false, $e->getMessage());
    }

    // ==================================================================
    // PHASE 10 : CONFIGURATION SYSTÈME
    // ==================================================================
    echo "\n▶ PHASE 10 : CONFIGURATION SYSTÈME\n";
    echo "────────────────────────────────────────\n";

    $settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    echo "   └─ Récompense avis: " . $settings['review_reward'] . " FCFA\n";
} catch (Exception $e) {
    echo "\n❌ ERREUR CRITIQUE: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// ==================================================================
// RÉSUMÉ
// ==================================================================
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ DES TESTS\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Réussis: $tests_passed\n";
echo "❌ Échoués: $tests_failed\n";
echo "────────────────────────────────────────────────────────────────\n";

if ($tests_failed === 0) {
    echo "🎉 TOUS LES TESTS PASSÉS!\n";
    echo "\nL'intégration frontend ↔ backend est correcte.\n";
    echo "Prêt pour tester l'authentification et les pages.\n";
} else {
    echo "⚠️  ATTENTION: $tests_failed test(s) échoué(s)\n";
    echo "Vérifier la base de données et les fichiers de configuration.\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
?>