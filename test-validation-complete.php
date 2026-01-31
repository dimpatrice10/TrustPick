<?php
/**
 * VALIDATION COMPLÈTE TRUSTPICK V2
 * Test de toutes les fonctionnalités critiques
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔍 VALIDATION COMPLÈTE - TRUSTPICK V2\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$passed = 0;
$failed = 0;
$warnings = 0;

function test($name, $condition, $message = '')
{
    global $passed, $failed;
    if ($condition) {
        echo "✅ $name\n";
        if ($message)
            echo "   └─ $message\n";
        $passed++;
        return true;
    } else {
        echo "❌ $name\n";
        if ($message)
            echo "   └─ ⚠️  $message\n";
        $failed++;
        return false;
    }
}

function warn($name, $message)
{
    global $warnings;
    echo "⚠️  $name\n";
    echo "   └─ $message\n";
    $warnings++;
}

// ========================================================================
// PHASE 1 : FICHIERS CRITIQUES
// ========================================================================
echo "▶ PHASE 1 : VÉRIFICATION FICHIERS CRITIQUES\n";
echo "────────────────────────────────────────\n";

$critical_files = [
    'includes/db.php' => 'Connexion database',
    'includes/config.php' => 'Configuration',
    'includes/auth.php' => 'Authentification CAU',
    'includes/url.php' => 'Helper URL',
    'views/login.php' => 'Page login',
    'views/register.php' => 'Page inscription',
    'views/home.php' => 'Page accueil',
    'views/user_dashboard.php' => 'Dashboard utilisateur',
    'views/wallet.php' => 'Portefeuille',
    'views/product.php' => 'Détail produit',
    'views/superadmin_dashboard.php' => 'Dashboard super admin',
    'actions/login.php' => 'Action login',
    'actions/register.php' => 'Action inscription',
    'actions/review.php' => 'Action avis',
    'actions/withdraw.php' => 'Action retrait',
    'public/index.php' => 'Routeur principal'
];

foreach ($critical_files as $file => $desc) {
    test("Fichier $desc", file_exists(__DIR__ . '/' . $file), $file);
}

// ========================================================================
// PHASE 2 : VÉRIFICATION INCLUDES
// ========================================================================
echo "\n▶ PHASE 2 : VÉRIFICATION INCLUDES\n";
echo "────────────────────────────────────────\n";

$files_need_url = [
    'views/user_dashboard.php',
    'views/wallet.php',
    'views/product.php',
    'views/admin_dashboard.php'
];

foreach ($files_need_url as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $content = file_get_contents(__DIR__ . '/' . $file);
        $has_url = strpos($content, "require_once __DIR__ . '/../includes/url.php'") !== false ||
            strpos($content, 'require_once __DIR__ . "/../includes/url.php"') !== false;
        test("$file inclut url.php", $has_url);
    }
}

// ========================================================================
// PHASE 3 : CONNEXION DATABASE
// ========================================================================
echo "\n▶ PHASE 3 : CONNEXION DATABASE\n";
echo "────────────────────────────────────────\n";

try {
    require_once __DIR__ . '/includes/db.php';
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    test("Connexion PDO établie", $pdo !== null);
} catch (Exception $e) {
    test("Connexion PDO établie", false, $e->getMessage());
    die("\n❌ Impossible de continuer sans connexion database\n");
}

// ========================================================================
// PHASE 4 : VÉRIFICATION SCHEMA
// ========================================================================
echo "\n▶ PHASE 4 : VÉRIFICATION SCHEMA DATABASE\n";
echo "────────────────────────────────────────\n";

$required_tables = [
    'users' => ['cau', 'balance', 'referral_code', 'role'],
    'products' => ['title', 'price', 'company_id'],
    'reviews' => ['rating', 'product_id', 'user_id'],
    'transactions' => ['amount', 'type', 'balance_after'],
    'withdrawals' => ['amount', 'status', 'phone_number'],
    'referrals' => ['referrer_id', 'referred_id', 'reward_amount'],
    'notifications' => ['user_id', 'type', 'title', 'message'],
    'tasks_definitions' => ['task_code', 'reward_amount'],
    'categories' => ['name', 'slug'],
    'companies' => ['name', 'slug']
];

foreach ($required_tables as $table => $columns) {
    try {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        test("Table `$table` existe", true, "$cnt lignes");

        // Vérifier colonnes critiques
        foreach ($columns as $col) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
            if (!$stmt->fetch()) {
                warn("Colonne `$table.$col` manquante", "Cette colonne est requise");
            }
        }
    } catch (Exception $e) {
        test("Table `$table` existe", false, $e->getMessage());
    }
}

// Vérifier que wallets n'existe PAS
try {
    $pdo->query("SELECT COUNT(*) FROM wallets")->fetchColumn();
    warn("Table wallets existe", "Cette table ne devrait pas exister (balance dans users)");
} catch (Exception $e) {
    test("Table wallets n'existe pas", true, "Correct - balance dans users");
}

// ========================================================================
// PHASE 5 : DONNÉES DE TEST
// ========================================================================
echo "\n▶ PHASE 5 : DONNÉES DE TEST\n";
echo "────────────────────────────────────────\n";

$test_users = ['USER001', 'USER002', 'TECH001', 'ADMIN001'];
foreach ($test_users as $cau) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE cau = ?");
    $stmt->execute([$cau]);
    $user = $stmt->fetch();
    if ($user) {
        test("Utilisateur $cau existe", true, "Role: {$user['role']}, Balance: {$user['balance']} FCFA");
    } else {
        warn("Utilisateur $cau manquant", "Utilisateur de test non trouvé");
    }
}

// ========================================================================
// PHASE 6 : ROUTAGE
// ========================================================================
echo "\n▶ PHASE 6 : ROUTAGE\n";
echo "────────────────────────────────────────\n";

$router_content = file_get_contents(__DIR__ . '/public/index.php');
$allowed_pages = [
    'home',
    'catalog',
    'product',
    'company',
    'login',
    'register',
    'user_dashboard',
    'company_dashboard',
    'admin_dashboard',
    'superadmin_dashboard',
    'wallet'
];

foreach ($allowed_pages as $page) {
    $has_page = strpos($router_content, "'$page'") !== false;
    test("Page '$page' routée", $has_page);

    // Vérifier que le fichier existe
    $view_file = __DIR__ . "/views/$page.php";
    if (!file_exists($view_file)) {
        warn("Vue manquante", "views/$page.php n'existe pas");
    }
}

// ========================================================================
// PHASE 7 : AUTHENTIFICATION CAU
// ========================================================================
echo "\n▶ PHASE 7 : AUTHENTIFICATION CAU\n";
echo "────────────────────────────────────────\n";

$login_content = file_get_contents(__DIR__ . '/views/login.php');
$has_cau_field = strpos($login_content, 'name="cau"') !== false;
test("Formulaire login utilise CAU", $has_cau_field);

$no_email = strpos($login_content, 'name="email"') === false;
test("Formulaire login n'utilise PAS email", $no_email);

$no_password = strpos($login_content, 'name="password"') === false ||
    strpos($login_content, 'type="password"') === false;
test("Formulaire login n'utilise PAS password", $no_password);

$login_action = file_get_contents(__DIR__ . '/actions/login.php');
$validates_cau = strpos($login_action, 'cau') !== false &&
    strpos($login_action, 'is_active') !== false;
test("Action login valide CAU", $validates_cau);

// ========================================================================
// PHASE 8 : INSCRIPTION
// ========================================================================
echo "\n▶ PHASE 8 : INSCRIPTION\n";
echo "────────────────────────────────────────\n";

$register_content = file_get_contents(__DIR__ . '/views/register.php');
$has_referral = strpos($register_content, 'name="referral_code"') !== false;
test("Formulaire inscription a champ parrainage", $has_referral);

$register_action = file_get_contents(__DIR__ . '/actions/register.php');
$generates_cau = strpos($register_action, 'generateCAU') !== false;
test("Action inscription génère CAU", $generates_cau);

$uses_referral = strpos($register_action, 'referral_code') !== false &&
    strpos($register_action, 'referrer_id') !== false;
test("Action inscription gère parrainage", $uses_referral);

// ========================================================================
// PHASE 9 : SYSTÈME FINANCIER
// ========================================================================
echo "\n▶ PHASE 9 : SYSTÈME FINANCIER\n";
echo "────────────────────────────────────────\n";

// Vérifier que balance est dans users
$stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'balance'");
test("users.balance existe", $stmt->fetch() !== false);

// Vérifier requêtes corrigées
$files_using_balance = [
    'views/wallet.php',
    'views/user_dashboard.php',
    'actions/withdraw.php',
    'actions/review.php'
];

foreach ($files_using_balance as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $content = file_get_contents(__DIR__ . '/' . $file);
        $uses_users_table = strpos($content, 'FROM users WHERE id') !== false ||
            strpos($content, 'UPDATE users SET balance') !== false;
        $no_wallets_table = strpos($content, 'FROM wallets') === false;
        test("$file utilise users.balance", $uses_users_table && $no_wallets_table);
    }
}

// ========================================================================
// PHASE 10 : PROTECTION PAGES
// ========================================================================
echo "\n▶ PHASE 10 : PROTECTION DES PAGES\n";
echo "────────────────────────────────────────\n";

$protected_pages = [
    'views/user_dashboard.php' => 'user_id',
    'views/wallet.php' => 'user_id',
    'views/admin_dashboard.php' => 'user_role'
];

foreach ($protected_pages as $file => $check) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $content = file_get_contents(__DIR__ . '/' . $file);
        $has_check = strpos($content, "empty(\$_SESSION['$check'])") !== false;
        $has_redirect = strpos($content, 'header(') !== false &&
            strpos($content, 'exit') !== false;
        test(basename($file) . " protégée", $has_check && $has_redirect);
    }
}

// ========================================================================
// RÉSUMÉ
// ========================================================================
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ DE LA VALIDATION\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Tests réussis    : $passed\n";
echo "❌ Tests échoués    : $failed\n";
echo "⚠️  Avertissements  : $warnings\n";
echo "────────────────────────────────────────────────────────────────\n";

if ($failed === 0 && $warnings === 0) {
    echo "🎉 PARFAIT ! Tous les tests sont passés sans avertissement.\n";
    echo "✅ TrustPick V2 est prêt pour les tests utilisateur.\n";
} elseif ($failed === 0) {
    echo "✅ Tous les tests critiques sont passés.\n";
    echo "⚠️  Il y a $warnings avertissement(s) à examiner.\n";
} else {
    echo "⚠️  $failed test(s) critique(s) échoué(s).\n";
    echo "🔧 Corrigez les erreurs avant de continuer.\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
?>