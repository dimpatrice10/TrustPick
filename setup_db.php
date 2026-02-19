<?php
/**
 * TrustPick V2 - Script d'initialisation de la base de données PostgreSQL
 * À exécuter une seule fois après le déploiement sur Render
 * 
 * Utilisation: Ouvrir dans le navigateur: https://votre-app.onrender.com/setup_db.php
 * 
 * IMPORTANT: Supprimer ce fichier après utilisation en production !
 */

// Clé de sécurité simple pour éviter l'exécution accidentelle
$setupKey = $_GET['key'] ?? '';
if ($setupKey !== 'trustpick_setup_2026') {
    echo "<h1>TrustPick - Database Setup</h1>";
    echo "<p>Ajoutez <code>?key=trustpick_setup_2026</code> à l'URL pour initialiser la base de données.</p>";
    echo "<p><a href='?key=trustpick_setup_2026'>Cliquez ici pour initialiser</a></p>";
    exit;
}

echo "<html><head><title>TrustPick DB Setup</title><style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
.success { color: green; } .error { color: red; } .info { color: blue; }
pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔧 TrustPick V2 - Initialisation PostgreSQL</h1>";

// Vérifier les variables d'environnement
echo "<h2>1. Vérification de la configuration</h2>";

$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl) {
    echo "<p class='success'>✅ DATABASE_URL trouvée</p>";
    $parts = parse_url($databaseUrl);
    echo "<p class='info'>Host: {$parts['host']}, DB: " . ltrim($parts['path'], '/') . "</p>";
} else {
    $pgHost = getenv('PGHOST');
    if ($pgHost) {
        echo "<p class='success'>✅ Variables PG individuelles trouvées (PGHOST={$pgHost})</p>";
    } else {
        echo "<p class='error'>❌ Aucune variable de connexion trouvée !</p>";
        echo "<p>Configurez DATABASE_URL ou les variables PGHOST, PGDATABASE, etc.</p>";
        exit;
    }
}

// Connexion
echo "<h2>2. Connexion à PostgreSQL</h2>";

try {
    require_once __DIR__ . '/includes/config.php';
    $config = require __DIR__ . '/includes/config.php';

    $port = $config['db_port'] ?? 5432;
    $dsn = "pgsql:host={$config['db_host']};port={$port};dbname={$config['db_name']}";

    if (getenv('DATABASE_URL')) {
        $dsn .= ";sslmode=require";
    }

    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "<p class='success'>✅ Connexion réussie à PostgreSQL</p>";

    // Version PostgreSQL
    $version = $pdo->query("SELECT version()")->fetchColumn();
    echo "<p class='info'>Version: " . htmlspecialchars(substr($version, 0, 60)) . "</p>";

} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur de connexion: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Exécuter le schéma
echo "<h2>3. Création des tables</h2>";

$sqlFile = __DIR__ . '/db/schema_postgresql.sql';
if (!file_exists($sqlFile)) {
    echo "<p class='error'>❌ Fichier schema_postgresql.sql introuvable !</p>";
    exit;
}

$sql = file_get_contents($sqlFile);

// Séparer les instructions SQL (par points-virgules, en ignorant ceux dans des fonctions)
$statements = [];
$current = '';
$inDollarQuote = false;

foreach (explode("\n", $sql) as $line) {
    $trimmed = trim($line);

    // Ignorer les commentaires
    if (strpos($trimmed, '--') === 0)
        continue;
    if (empty($trimmed))
        continue;

    // Détecter les dollar quotes (DO $$)
    if (preg_match('/\$\$/', $trimmed)) {
        $inDollarQuote = !$inDollarQuote;
    }

    $current .= $line . "\n";

    // Si on trouve un ; et qu'on n'est pas dans un dollar quote
    if (!$inDollarQuote && substr($trimmed, -1) === ';') {
        $statements[] = trim($current);
        $current = '';
    }
}

if (!empty(trim($current))) {
    $statements[] = trim($current);
}

$successCount = 0;
$errorCount = 0;
$errors = [];

foreach ($statements as $stmt) {
    if (empty(trim($stmt)))
        continue;

    try {
        $pdo->exec($stmt);
        $successCount++;
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        // Ignorer les erreurs "already exists"
        if (strpos($errorMsg, 'already exists') !== false || strpos($errorMsg, 'duplicate key') !== false) {
            $successCount++;
            continue;
        }
        $errorCount++;
        $errors[] = [
            'sql' => substr($stmt, 0, 100) . '...',
            'error' => $errorMsg
        ];
    }
}

echo "<p class='success'>✅ {$successCount} instructions exécutées avec succès</p>";

if ($errorCount > 0) {
    echo "<p class='error'>⚠️ {$errorCount} erreurs:</p>";
    echo "<pre>";
    foreach ($errors as $err) {
        echo "SQL: " . htmlspecialchars($err['sql']) . "\n";
        echo "Erreur: " . htmlspecialchars($err['error']) . "\n\n";
    }
    echo "</pre>";
}

// Vérifier les tables créées
echo "<h2>4. Vérification des tables</h2>";

$tables = $pdo->query("
    SELECT tablename 
    FROM pg_tables 
    WHERE schemaname = 'public' 
    ORDER BY tablename
")->fetchAll(PDO::FETCH_COLUMN);

echo "<p><strong>" . count($tables) . " tables trouvées:</strong></p>";
echo "<ul>";
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM \"{$table}\"")->fetchColumn();
    echo "<li>{$table} ({$count} lignes)</li>";
}
echo "</ul>";

// Résumé
echo "<h2>5. Résumé</h2>";

$requiredTables = [
    'users',
    'companies',
    'products',
    'reviews',
    'categories',
    'tasks_definitions',
    'user_tasks',
    'notifications',
    'transactions',
    'withdrawals',
    'referrals',
    'payment_transactions',
    'system_settings'
];

$missingTables = array_diff($requiredTables, $tables);

if (empty($missingTables)) {
    echo "<p class='success'>✅ Toutes les tables requises sont présentes !</p>";
    echo "<p class='success'>🎉 <strong>Base de données initialisée avec succès !</strong></p>";
    echo "<p class='info'>⚠️ <strong>IMPORTANT:</strong> Supprimez ce fichier (setup_db.php) après utilisation !</p>";
    echo "<p><a href='index.php'>→ Accéder à TrustPick</a></p>";
} else {
    echo "<p class='error'>❌ Tables manquantes: " . implode(', ', $missingTables) . "</p>";
    echo "<p>Vérifiez le fichier db/schema_postgresql.sql</p>";
}

echo "</body></html>";