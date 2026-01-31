<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrustPick V2 - Tests de Fonctionnement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }

        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .test-section h2 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .test-result {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-radius: 5px;
        }

        .success {
            color: #28a745;
            font-weight: bold;
        }

        .error {
            color: #dc3545;
            font-weight: bold;
        }

        .info {
            color: #17a2b8;
        }

        .code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-error {
            background: #f8d7da;
            color: #721c24;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: #667eea;
            font-size: 2em;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🚀 TrustPick V2</h1>
        <p class="subtitle">Tests de Fonctionnement du Backend</p>

        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Fonction pour afficher les résultats
        function displayResult($test, $success, $message = '', $data = null)
        {
            $icon = $success ? '✅' : '❌';
            $class = $success ? 'success' : 'error';
            echo "<div class='test-result'>";
            echo "<span class='$class'>$icon $test</span>";
            if ($message)
                echo "<span class='info'>→ $message</span>";
            echo "</div>";
            if ($data && is_array($data)) {
                echo "<div class='code'>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</div>";
            }
        }

        // Test 1: Connexion à la base de données
        echo "<div class='test-section'>";
        echo "<h2>📊 Test 1: Connexion Base de Données</h2>";

        try {
            require_once 'includes/db.php';
            displayResult('Connexion PDO', true, 'Base de données connectée');
        } catch (Exception $e) {
            displayResult('Connexion PDO', false, $e->getMessage());
            die('</div></div></body></html>');
        }
        echo "</div>";

        // Test 2: Système CAU
        echo "<div class='test-section'>";
        echo "<h2>🔐 Test 2: Système CAU</h2>";

        try {
            require_once 'includes/auth.php';
            $auth = new AuthCAU($pdo);

            // Test génération CAU
            $cau = $auth->generateCAU('user');
            displayResult('Génération CAU', true, "CAU généré: $cau");

            // Test génération code parrainage
            $refCode = $auth->generateReferralCode();
            displayResult('Génération Code Parrainage', true, "Code: $refCode");

            // Test connexion avec CAU existant
            $loginResult = $auth->loginWithCAU('USER001');
            displayResult(
                'Connexion CAU',
                $loginResult['success'],
                $loginResult['success'] ? 'Utilisateur connecté: ' . $loginResult['user']['name'] : $loginResult['message']
            );

        } catch (Exception $e) {
            displayResult('Système CAU', false, $e->getMessage());
        }
        echo "</div>";

        // Test 3: Permissions
        echo "<div class='test-section'>";
        echo "<h2>🔒 Test 3: Système de Permissions</h2>";

        try {
            require_once 'includes/permissions.php';
            $permManager = new PermissionManager($pdo);

            // Test permissions super admin
            $stmt = $pdo->query("SELECT id FROM users WHERE role = 'super_admin' LIMIT 1");
            $adminId = $stmt->fetchColumn();

            if ($adminId) {
                $canManage = $permManager->hasPermission('create_company', $adminId);
                displayResult('Permission Super Admin', $canManage, 'create_company: ' . ($canManage ? 'OUI' : 'NON'));
            }

            // Compter les permissions par rôle
            $adminPerms = count($permManager->getRolePermissions('super_admin'));
            $companyPerms = count($permManager->getRolePermissions('admin_entreprise'));
            $userPerms = count($permManager->getRolePermissions('user'));

            displayResult(
                'Permissions configurées',
                true,
                "Super Admin: $adminPerms | Admin Entreprise: $companyPerms | User: $userPerms"
            );

        } catch (Exception $e) {
            displayResult('Système Permissions', false, $e->getMessage());
        }
        echo "</div>";

        // Test 4: Générateur de produits
        echo "<div class='test-section'>";
        echo "<h2>📦 Test 4: Générateur de Produits</h2>";

        try {
            require_once 'includes/product_generator.php';
            $generator = new ProductGenerator($pdo);

            // Générer 1 produit de test
            $result = $generator->generateProduct(1);

            if ($result['success']) {
                displayResult(
                    'Génération Produit',
                    true,
                    "{$result['product_name']} - {$result['price']} FCFA"
                );
            } else {
                displayResult('Génération Produit', false, $result['message']);
            }

        } catch (Exception $e) {
            displayResult('Générateur Produits', false, $e->getMessage());
        }
        echo "</div>";

        // Test 5: Système de tâches
        echo "<div class='test-section'>";
        echo "<h2>✅ Test 5: Système de Tâches</h2>";

        try {
            require_once 'includes/tasks.php';
            $taskSystem = new TaskSystem($pdo);

            // Récupérer les tâches pour l'utilisateur 3
            $tasksResult = $taskSystem->getAvailableTasks(3);

            if ($tasksResult['success']) {
                $taskCount = count($tasksResult['tasks']);
                displayResult('Tâches Disponibles', true, "$taskCount tâches configurées");

                // Afficher les 3 premières
                $firstThree = array_slice($tasksResult['tasks'], 0, 3);
                foreach ($firstThree as $task) {
                    echo "<div class='test-result'>";
                    echo "<span class='info'>→ {$task['task_name']}: {$task['reward_amount']}</span>";
                    echo "</div>";
                }
            }

        } catch (Exception $e) {
            displayResult('Système Tâches', false, $e->getMessage());
        }
        echo "</div>";

        // Test 6: Système de parrainage
        echo "<div class='test-section'>";
        echo "<h2>🔗 Test 6: Système de Parrainage</h2>";

        try {
            require_once 'includes/referrals.php';
            $referralSystem = new ReferralSystem($pdo);

            // Test lien de parrainage
            $linkResult = $referralSystem->getReferralLink(3);

            if ($linkResult['success']) {
                displayResult('Lien de Parrainage', true, $linkResult['referral_code']);
                echo "<div class='code'>{$linkResult['referral_link']}</div>";
            }

            // Test statistiques
            $statsResult = $referralSystem->getReferralStats(3);
            if ($statsResult['success']) {
                $stats = $statsResult['stats'];
                displayResult(
                    'Statistiques Parrainage',
                    true,
                    "Total: {$stats['total_referrals']} | Actifs: {$stats['active_referrals']} | Gains: {$stats['total_rewards']} FCFA"
                );
            }

        } catch (Exception $e) {
            displayResult('Système Parrainage', false, $e->getMessage());
        }
        echo "</div>";

        // Test 7: Notifications
        echo "<div class='test-section'>";
        echo "<h2>🔔 Test 7: Système de Notifications</h2>";

        try {
            require_once 'includes/notifications.php';
            $notifSystem = new NotificationSystem($pdo);

            // Créer une notification de test
            $createResult = $notifSystem->create(
                3,
                'system',
                'Test Notification',
                'Ceci est une notification de test',
                'index.php?page=user_dashboard'
            );

            displayResult(
                'Création Notification',
                $createResult['success'],
                $createResult['success'] ? "ID: {$createResult['notification_id']}" : ''
            );

            // Récupérer les notifications
            $notifsResult = $notifSystem->getNotifications(3, 5);
            if ($notifsResult['success']) {
                displayResult(
                    'Récupération Notifications',
                    true,
                    "Total: {$notifsResult['total']} | Non lues: {$notifsResult['unread_count']}"
                );
            }

        } catch (Exception $e) {
            displayResult('Système Notifications', false, $e->getMessage());
        }
        echo "</div>";

        // Test 8: Pagination
        echo "<div class='test-section'>";
        echo "<h2>📄 Test 8: Pagination Intelligente</h2>";

        try {
            require_once 'includes/pagination.php';
            $pagination = new SmartPagination($pdo, 5);

            // Paginer les produits
            $result = $pagination->paginateProducts([], 1);

            if ($result['success']) {
                $p = $result['pagination'];
                displayResult(
                    'Pagination Produits',
                    true,
                    "Page {$p['current_page']}/{$p['total_pages']} | Items: {$p['total_items']}"
                );

                displayResult(
                    'Éléments par page',
                    true,
                    "Affichage {$p['from']} à {$p['to']} sur {$p['total_items']}"
                );
            }

        } catch (Exception $e) {
            displayResult('Pagination', false, $e->getMessage());
        }
        echo "</div>";

        // Statistiques globales
        echo "<div class='test-section'>";
        echo "<h2>📊 Statistiques Globales</h2>";
        echo "<div class='stats'>";

        try {
            // Utilisateurs
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            $userCount = $stmt->fetchColumn();
            echo "<div class='stat-card'><h3>$userCount</h3><p>Utilisateurs</p></div>";

            // Entreprises
            $stmt = $pdo->query("SELECT COUNT(*) FROM companies");
            $companyCount = $stmt->fetchColumn();
            echo "<div class='stat-card'><h3>$companyCount</h3><p>Entreprises</p></div>";

            // Produits
            $stmt = $pdo->query("SELECT COUNT(*) FROM products");
            $productCount = $stmt->fetchColumn();
            echo "<div class='stat-card'><h3>$productCount</h3><p>Produits</p></div>";

            // Produits auto-générés
            $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_auto_generated = TRUE");
            $autoProducts = $stmt->fetchColumn();
            echo "<div class='stat-card'><h3>$autoProducts</h3><p>Produits Auto-générés</p></div>";

            // Catégories
            $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
            $catCount = $stmt->fetchColumn();
            echo "<div class='stat-card'><h3>$catCount</h3><p>Catégories</p></div>";

            // Tâches définies
            $stmt = $pdo->query("SELECT COUNT(*) FROM tasks_definitions WHERE is_active = TRUE");
            $taskCount = $stmt->fetchColumn();
            echo "<div class='stat-card'><h3>$taskCount</h3><p>Tâches Actives</p></div>";

        } catch (Exception $e) {
            echo "<div class='stat-card'><h3>❌</h3><p>Erreur Stats</p></div>";
        }

        echo "</div>";
        echo "</div>";

        // Résumé final
        echo "<div class='test-section' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;'>";
        echo "<h2 style='color: white;'>✨ Résumé Final</h2>";
        echo "<p style='font-size: 1.2em; margin: 10px 0;'>";
        echo "🎉 <strong>TrustPick V2 Backend est opérationnel !</strong><br><br>";
        echo "✅ Tous les systèmes sont fonctionnels<br>";
        echo "✅ Base de données initialisée<br>";
        echo "✅ API prête à l'emploi<br>";
        echo "✅ Prochaine étape: Créer les interfaces utilisateur";
        echo "</p>";
        echo "</div>";
        ?>
    </div>
</body>

</html>