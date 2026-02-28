<?php
/**
 * TrustPick V2 - Action: Générer un écosystème complet (Super Admin)
 * 
 * Génère: entreprises + admins + produits + 50+ utilisateurs + avis + likes + tâches + soldes
 * Les utilisateurs générés interagissent aussi avec les produits existants.
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
$generateUsers = !empty($_POST['generate_users']);
$userCount = intval($_POST['user_count'] ?? 50);

// Limites
$count = min(20, max(1, $count));
$userCount = min(200, max(10, $userCount));

try {
    $pdo = Database::getInstance()->getConnection();
    $auth = new AuthCAU($pdo);

    // ============================================================
    // POOLS DE DONNÉES RÉALISTES AFRICAINS
    // ============================================================

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

    $productNames = [
        'Technologies' => ['Smartphone Pro', 'Laptop Elite', 'Tablette HD', 'Écouteurs Sans Fil', 'Montre Connectée', 'Chargeur Rapide', 'Caméra HD'],
        'Commerce' => ['Pack Business', 'Lot Premium', 'Coffret Cadeau', 'Box Découverte', 'Kit Complet', 'Assortiment Pro', 'Bundle Luxe'],
        'Services' => ['Formation Pro', 'Consultation Expert', 'Abonnement Premium', 'Pack Support', 'Service VIP', 'Coaching Elite', 'Audit Express'],
        'Alimentation' => ['Café Premium', 'Thé Bio', 'Chocolat Artisanal', 'Épices Exotiques', 'Miel Naturel', 'Huile Olive Extra', 'Grains Torréfiés'],
        'Mode' => ['Chemise Élégante', 'Jean Premium', 'Robe Designer', 'Sac Cuir', 'Montre Classic', 'Basket Tendance', 'Polo Sport'],
        'Santé' => ['Complément Vitaminé', 'Huile Essentielle', 'Crème Bio', 'Thé Détox', 'Pack Bien-être', 'Protéines Végétales', 'Baume Naturel'],
        'Électronique' => ['Enceinte Bluetooth', 'Drone Compact', 'Imprimante 3D', 'Power Bank XXL', 'Clavier Mécanique'],
        'Beauté' => ['Sérum Visage', 'Crème Hydratante', 'Palette Maquillage', 'Parfum Élégant', 'Masque Capillaire'],
        'Sport' => ['Tapis Yoga', 'Haltères Pro', 'Montre Cardio', 'Sac Sport', 'Gourde Isotherme'],
        'Maison' => ['Lampe Design', 'Coussin Déco', 'Bougie Parfumée', 'Cadre Photo', 'Vase Artisanal'],
        'Auto' => ['Dashcam HD', 'Support Téléphone', 'Aspirateur Auto', 'Kit Nettoyage', 'GPS Portable'],
        'Culture' => ['Livre Best-seller', 'Album Vinyle', 'Jeu Éducatif', 'Kit Peinture', 'Puzzle Artistique'],
        'Finance' => ['Guide Investissement', 'Formation Crypto', 'Pack Gestion', 'Logiciel Compta', 'Conseil Fiscal']
    ];

    // Prénoms africains réalistes (hommes et femmes)
    $firstNamesMale = [
        'Amadou',
        'Ibrahim',
        'Moussa',
        'Youssouf',
        'Kofi',
        'Ousmane',
        'Sékou',
        'Abdoulaye',
        'Mamadou',
        'Kwame',
        'Diallo',
        'Boubacar',
        'Souleymane',
        'Cheick',
        'Tidiane',
        'Bakary',
        'Ismaël',
        'Lamine',
        'Modibo',
        'Sidiki',
        'Dramane',
        'Fode',
        'Adama',
        'Salif',
        'Kader',
        'Bamba',
        'Djibril',
        'Fabrice',
        'Jean-Baptiste',
        'Patrick',
        'Thierry',
        'Arnaud',
        'Christian',
        'Emmanuel',
        'David',
        'Samuel',
        'Olivier',
        'Kevin',
        'Yves',
        'Franck'
    ];
    $firstNamesFemale = [
        'Aminata',
        'Fatou',
        'Awa',
        'Mariama',
        'Aïssatou',
        'Kadiatou',
        'Fatoumata',
        'Diabou',
        'Rokia',
        'Nafi',
        'Oumou',
        'Binta',
        'Hawa',
        'Sira',
        'Djénéba',
        'Maimouna',
        'Salimata',
        'Ramata',
        'Fanta',
        'Coumba',
        'Nassira',
        'Kadia',
        'Mariam',
        'Aoua',
        'Safiatou',
        'Grâce',
        'Marie',
        'Cécile',
        'Chantal',
        'Patricia',
        'Viviane',
        'Nicole',
        'Sandrine',
        'Estelle',
        'Diane',
        'Félicité',
        'Ruth',
        'Noëlle',
        'Prisca',
        'Solange'
    ];
    $lastNames = [
        'Traoré',
        'Diallo',
        'Keita',
        'Coulibaly',
        'Koné',
        'Diarra',
        'Sanogo',
        'Sidibé',
        'Touré',
        'Camara',
        'Ouédraogo',
        'Sawadogo',
        'Compaoré',
        'Zongo',
        'Kaboré',
        'Bamba',
        'Cissé',
        'Konaté',
        'Sissoko',
        'Dembélé',
        'Sylla',
        'Fofana',
        'Diabaté',
        'Sanou',
        'Tall',
        'Sow',
        'Barry',
        'Ndiaye',
        'Mbaye',
        'Fall',
        'Yao',
        'Kouamé',
        'Akossou',
        'Gnangnan',
        'Koffi',
        'Ouattara',
        'Doumbia',
        'Bakayoko',
        'Kouyaté',
        'Condé'
    ];

    // Titres et corps d'avis réalistes
    $reviewTitles = [
        5 => ['Excellent produit !', 'Vraiment top !', 'Je recommande fortement', 'Qualité supérieure', 'Parfait !', 'Au-delà de mes attentes', 'Superbe achat', 'Meilleur du marché'],
        4 => ['Très bon produit', 'Satisfait de mon achat', 'Bon rapport qualité-prix', 'Belle qualité', 'Recommandable', 'Agréablement surpris'],
        3 => ['Correct sans plus', 'Produit convenable', 'Moyen mais acceptable', 'Peut mieux faire', 'Passable'],
        2 => ['Décevant', 'Pas à la hauteur', 'Qualité insuffisante', 'Insatisfait'],
        1 => ['Très mauvais', 'À éviter', 'Arnaque totale']
    ];
    $reviewBodies = [
        5 => [
            'J\'ai acheté ce produit et je suis vraiment impressionné par la qualité. Livraison rapide et emballage soigné. Je le recommande vivement !',
            'Un produit d\'exception ! La finition est impeccable et il correspond parfaitement à la description. Bravo à cette entreprise.',
            'Après plusieurs semaines d\'utilisation, je confirme que c\'est un excellent choix. Rien à redire, produit fiable et performant.',
            'Meilleur achat de l\'année ! La qualité est au rendez-vous et le service client très réactif. Je suis conquis.',
            'Ce produit a dépassé toutes mes attentes. Design élégant, fonctionnalités complètes. Mon entourage veut le même !',
        ],
        4 => [
            'Bon produit dans l\'ensemble. Quelques détails pourraient être améliorés mais rien de grave. Satisfait de mon achat.',
            'Je suis content de cet achat. Le produit est conforme à la description. Un petit bémol sur l\'emballage mais le produit est top.',
            'Très bien pour le prix. La qualité est correcte et il remplit son rôle parfaitement. Je recommande.',
            'Globalement satisfait. Le produit fait ce qu\'on lui demande. J\'aurais aimé un peu plus de finesse dans les détails.',
        ],
        3 => [
            'Le produit est correct mais sans plus. Il fait le travail minimum. Pour le prix, on pourrait s\'attendre à mieux.',
            'Moyen. Le produit fonctionne mais la qualité n\'est pas exceptionnelle. À voir sur la durabilité.',
            'Acceptable pour un premier achat mais je ne suis pas sûr de racheter. Des améliorations sont nécessaires.',
        ],
        2 => [
            'Déçu par la qualité. Le produit ne correspond pas vraiment à la description. Je m\'attendais à mieux.',
            'Pas terrible. La finition laisse à désirer et le produit semble fragile. Difficile de recommander.',
        ],
        1 => [
            'Très mauvaise expérience. Le produit est arrivé endommagé et la qualité est vraiment médiocre. À éviter.',
        ]
    ];

    // Messages de recommandation
    $recommendMessages = [
        'Je te recommande ce produit, il est vraiment bien !',
        'Regarde ce produit, il pourrait t\'intéresser.',
        'Super rapport qualité-prix, je te le conseille.',
        'J\'ai testé et approuvé ! Tu devrais essayer.',
        'Ce produit vaut vraiment le coup, fais-toi plaisir.',
        'Un ami me l\'a recommandé et je fais pareil pour toi !',
        'Excellent produit, je suis sûr que tu vas adorer.',
        'Parfait pour ton usage quotidien, n\'hésite pas !',
    ];

    // ============================================================
    // PHASE 1 : Créer entreprises + admins + produits (dans une transaction)
    // ============================================================

    $pdo->beginTransaction();
    $created = 0;
    $newProductIds = [];

    for ($i = 0; $i < $count; $i++) {
        $companyName = $prefixes[array_rand($prefixes)] . $sectors[array_rand($sectors)] . ' ' . $suffixes[array_rand($suffixes)];

        $checkStmt = $pdo->prepare('SELECT id FROM companies WHERE name = ?');
        $checkStmt->execute([$companyName]);
        if ($checkStmt->fetch()) {
            $companyName .= ' ' . rand(1, 99);
        }

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $companyName));
        $description = "$companyName est une entreprise leader dans son secteur, offrant des produits et services de qualité pour la clientèle africaine.";

        $stmt = $pdo->prepare('
            INSERT INTO companies (name, slug, description, is_active, created_by, created_at)
            VALUES (?, ?, ?, TRUE, ?, NOW())
        ');
        $stmt->execute([$companyName, $slug, $description, $user_id]);
        $companyId = $pdo->lastInsertId();

        // Admin entreprise
        $adminName = 'Admin ' . explode(' ', $companyName)[0];
        $adminPhone = '+225' . rand(10, 99) . rand(100, 999) . rand(1000, 9999);
        $adminCAU = $auth->generateCAU('admin_entreprise');
        $adminReferralCode = $auth->generateReferralCode();

        $stmt = $pdo->prepare("
            INSERT INTO users (cau, name, phone, role, company_id, balance, referral_code, is_active, created_by, created_at)
            VALUES (?, ?, ?, 'admin_entreprise', ?, 0, ?, TRUE, ?, NOW())
        ");
        $stmt->execute([$adminCAU, $adminName, $adminPhone, $companyId, $adminReferralCode, $user_id]);

        // Produits
        if ($generateProducts) {
            $selectedSector = $sectors[array_rand($sectors)];
            $products = $productNames[$selectedSector] ?? ['Produit Premium', 'Article Qualité', 'Nouveauté', 'Best-seller', 'Exclusivité'];
            $numProducts = rand(5, 10);

            for ($p = 0; $p < $numProducts; $p++) {
                $productName = $products[array_rand($products)] . ' ' . explode(' ', $companyName)[0] . ' #' . rand(100, 999);
                $productSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $productName)) . '-' . uniqid();
                $productPrice = rand(5, 100) * 1000;
                $productDesc = "Découvrez $productName par $companyName. Produit de qualité supérieure.";

                $pdo->prepare('
                    INSERT INTO products (company_id, title, slug, description, price, is_auto_generated, is_active, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, TRUE, TRUE, ?, NOW())
                ')->execute([$companyId, $productName, $productSlug, $productDesc, $productPrice, $user_id]);
                $newProductIds[] = $pdo->lastInsertId();
            }
        }

        $created++;
    }

    $pdo->commit();

    // ============================================================
    // PHASE 2 : Générer utilisateurs + activité (si demandé)
    // ============================================================

    $usersCreated = 0;
    $reviewsCreated = 0;
    $likesCreated = 0;
    $tasksCompleted = 0;

    if ($generateUsers) {

        // Récupérer TOUS les produits (existants + nouveaux) pour interactions
        $allProducts = $pdo->query('SELECT id FROM products WHERE is_active = TRUE ORDER BY RAND()')->fetchAll(PDO::FETCH_COLUMN);
        if (empty($allProducts)) {
            $allProducts = $newProductIds;
        }

        // Récupérer les définitions de tâches actives
        $taskDefs = $pdo->query("SELECT id, task_code, task_name, reward_amount FROM tasks_definitions WHERE is_active = TRUE")->fetchAll(PDO::FETCH_ASSOC);
        $tasksByCode = [];
        foreach ($taskDefs as $td) {
            $tasksByCode[$td['task_code']] = $td;
        }

        // Tâches éligibles pour génération (pas de deposit ni invite)
        $generableTasks = ['daily_login', 'leave_review', 'like_review', 'recommend_product'];

        // Créer les utilisateurs par lots de 10 pour éviter timeout
        $batchSize = 10;
        $generatedUserIds = [];

        for ($batch = 0; $batch < ceil($userCount / $batchSize); $batch++) {
            $batchCount = min($batchSize, $userCount - ($batch * $batchSize));

            $pdo->beginTransaction();

            for ($u = 0; $u < $batchCount; $u++) {
                $isFemale = rand(0, 1);
                $firstName = $isFemale
                    ? $firstNamesFemale[array_rand($firstNamesFemale)]
                    : $firstNamesMale[array_rand($firstNamesMale)];
                $lastName = $lastNames[array_rand($lastNames)];
                $userName = $firstName . ' ' . $lastName;
                $userPhone = '+225' . rand(1, 9) . rand(10, 99) . rand(10, 99) . rand(10, 99) . rand(10, 99);
                $userCAU = $auth->generateCAU('user');
                $userReferralCode = $auth->generateReferralCode();

                // Date de création aléatoire dans les 30 derniers jours
                $daysAgo = rand(0, 30);
                $createdDate = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days -" . rand(0, 23) . " hours -" . rand(0, 59) . " minutes"));

                $pdo->prepare("
                    INSERT INTO users (cau, name, phone, role, balance, referral_code, is_active, created_by, created_at)
                    VALUES (?, ?, ?, 'user', 0, ?, TRUE, ?, ?)
                ")->execute([$userCAU, $userName, $userPhone, $userReferralCode, $user_id, $createdDate]);

                $newUserId = intval($pdo->lastInsertId());
                $generatedUserIds[] = $newUserId;
                $usersCreated++;

                // --------------------------------------------------
                // Activité aléatoire pour cet utilisateur
                // --------------------------------------------------

                $totalReward = 0;

                // Nombre de jours d'activité (entre 1 et min(daysAgo, 15))
                $activeDays = rand(1, max(1, min($daysAgo, 15)));

                for ($day = 0; $day < $activeDays; $day++) {
                    $activityDate = date('Y-m-d H:i:s', strtotime("-" . rand(0, $daysAgo) . " days -" . rand(6, 22) . " hours -" . rand(0, 59) . " minutes"));

                    // --- daily_login (80% de chance) ---
                    if (rand(1, 100) <= 80 && isset($tasksByCode['daily_login'])) {
                        $task = $tasksByCode['daily_login'];
                        $pdo->prepare('INSERT INTO user_tasks (user_id, task_id, completed_at, reward_earned) VALUES (?, ?, ?, ?)')
                            ->execute([$newUserId, $task['id'], $activityDate, $task['reward_amount']]);
                        $totalReward += $task['reward_amount'];
                        $tasksCompleted++;

                        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, reference_type, created_at) VALUES (?, 'reward', ?, ?, 'task', ?)")
                            ->execute([$newUserId, $task['reward_amount'], 'Tâche: ' . $task['task_name'], $activityDate]);
                    }

                    // --- leave_review (60% de chance, sur un produit aléatoire non encore reviewé) ---
                    if (rand(1, 100) <= 60 && !empty($allProducts) && isset($tasksByCode['leave_review'])) {
                        // Prendre un produit au hasard
                        $productId = $allProducts[array_rand($allProducts)];

                        // Vérifier qu'on n'a pas déjà un avis de cet utilisateur sur ce produit
                        $checkReview = $pdo->prepare('SELECT id FROM reviews WHERE user_id = ? AND product_id = ?');
                        $checkReview->execute([$newUserId, $productId]);

                        if (!$checkReview->fetch()) {
                            $rating = weightedRating();
                            $title = $reviewTitles[$rating][array_rand($reviewTitles[$rating])];
                            $body = $reviewBodies[$rating][array_rand($reviewBodies[$rating])];

                            $pdo->prepare('INSERT INTO reviews (product_id, user_id, rating, title, body, likes_count, dislikes_count, created_at) VALUES (?, ?, ?, ?, ?, 0, 0, ?)')
                                ->execute([$productId, $newUserId, $rating, $title, $body, $activityDate]);
                            $reviewsCreated++;

                            // Compter la tâche
                            $task = $tasksByCode['leave_review'];
                            $pdo->prepare('INSERT INTO user_tasks (user_id, task_id, completed_at, reward_earned) VALUES (?, ?, ?, ?)')
                                ->execute([$newUserId, $task['id'], $activityDate, $task['reward_amount']]);
                            $totalReward += $task['reward_amount'];
                            $tasksCompleted++;

                            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, reference_type, created_at) VALUES (?, 'reward', ?, ?, 'task', ?)")
                                ->execute([$newUserId, $task['reward_amount'], 'Tâche: ' . $task['task_name'], $activityDate]);
                        }
                    }

                    // --- like_review (70% de chance) ---
                    if (rand(1, 100) <= 70 && isset($tasksByCode['like_review'])) {
                        // Trouver un avis à liker (pas le sien, pas déjà liké)
                        $reviewToLike = $pdo->prepare('
                            SELECT r.id FROM reviews r
                            WHERE r.user_id != ?
                            AND r.id NOT IN (SELECT review_id FROM review_reactions WHERE user_id = ?)
                            ORDER BY RAND() LIMIT 1
                        ');
                        $reviewToLike->execute([$newUserId, $newUserId]);
                        $likeableReview = $reviewToLike->fetch();

                        if ($likeableReview) {
                            $pdo->prepare("INSERT INTO review_reactions (review_id, user_id, reaction_type, created_at) VALUES (?, ?, 'like', ?)")
                                ->execute([$likeableReview['id'], $newUserId, $activityDate]);

                            // Mettre à jour le compteur likes_count
                            $pdo->prepare('UPDATE reviews SET likes_count = likes_count + 1 WHERE id = ?')
                                ->execute([$likeableReview['id']]);
                            $likesCreated++;

                            // Compter la tâche
                            $task = $tasksByCode['like_review'];
                            $pdo->prepare('INSERT INTO user_tasks (user_id, task_id, completed_at, reward_earned) VALUES (?, ?, ?, ?)')
                                ->execute([$newUserId, $task['id'], $activityDate, $task['reward_amount']]);
                            $totalReward += $task['reward_amount'];
                            $tasksCompleted++;

                            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, reference_type, created_at) VALUES (?, 'reward', ?, ?, 'task', ?)")
                                ->execute([$newUserId, $task['reward_amount'], 'Tâche: ' . $task['task_name'], $activityDate]);
                        }
                    }

                    // --- recommend_product (40% de chance) ---
                    if (rand(1, 100) <= 40 && !empty($allProducts) && isset($tasksByCode['recommend_product'])) {
                        $recProductId = $allProducts[array_rand($allProducts)];
                        $recMessage = $recommendMessages[array_rand($recommendMessages)];

                        $pdo->prepare('INSERT INTO recommendations (product_id, recommender_id, recommended_to_id, message, created_at) VALUES (?, ?, ?, ?, ?)')
                            ->execute([$recProductId, $newUserId, $newUserId, $recMessage, $activityDate]);

                        $task = $tasksByCode['recommend_product'];
                        $pdo->prepare('INSERT INTO user_tasks (user_id, task_id, completed_at, reward_earned) VALUES (?, ?, ?, ?)')
                            ->execute([$newUserId, $task['id'], $activityDate, $task['reward_amount']]);
                        $totalReward += $task['reward_amount'];
                        $tasksCompleted++;

                        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, reference_type, created_at) VALUES (?, 'reward', ?, ?, 'task', ?)")
                            ->execute([$newUserId, $task['reward_amount'], 'Tâche: ' . $task['task_name'], $activityDate]);
                    }
                }

                // Mettre à jour le solde de l'utilisateur
                if ($totalReward > 0) {
                    $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?')
                        ->execute([$totalReward, $newUserId]);
                }
            }

            $pdo->commit();
        }

        // ============================================================
        // PHASE 3 : Interactions croisées (utilisateurs générés likent d'autres avis)
        // ============================================================

        // Récupérer tous les avis disponibles
        $allReviews = $pdo->query('SELECT id, user_id FROM reviews ORDER BY RAND()')->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($allReviews) && !empty($generatedUserIds)) {
            $pdo->beginTransaction();
            $crossLikes = 0;

            // Chaque utilisateur généré like 2-8 avis additionnels
            foreach ($generatedUserIds as $gUserId) {
                $numExtraLikes = rand(2, 8);
                shuffle($allReviews);

                $liked = 0;
                foreach ($allReviews as $rev) {
                    if ($liked >= $numExtraLikes)
                        break;
                    if ($rev['user_id'] == $gUserId)
                        continue; // pas son propre avis

                    // Vérifier qu'on n'a pas déjà liké
                    $check = $pdo->prepare('SELECT id FROM review_reactions WHERE user_id = ? AND review_id = ?');
                    $check->execute([$gUserId, $rev['id']]);
                    if ($check->fetch())
                        continue;

                    $likeDate = date('Y-m-d H:i:s', strtotime("-" . rand(0, 25) . " days -" . rand(0, 23) . " hours"));

                    $pdo->prepare("INSERT INTO review_reactions (review_id, user_id, reaction_type, created_at) VALUES (?, ?, 'like', ?)")
                        ->execute([$rev['id'], $gUserId, $likeDate]);

                    $pdo->prepare('UPDATE reviews SET likes_count = likes_count + 1 WHERE id = ?')
                        ->execute([$rev['id']]);

                    $crossLikes++;
                    $liked++;
                }
            }

            $likesCreated += $crossLikes;
            $pdo->commit();
        }

        // ============================================================
        // PHASE 4 : Notifications résumées (1 par utilisateur)
        // ============================================================

        $pdo->beginTransaction();
        foreach ($generatedUserIds as $gUserId) {
            $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, created_at) VALUES (?, 'reward', 'Bienvenue sur TrustPick', 'Votre compte a été créé. Complétez vos tâches quotidiennes pour gagner des récompenses !', NOW())")
                ->execute([$gUserId]);
        }
        $pdo->commit();
    }

    // ============================================================
    // MESSAGE FINAL
    // ============================================================

    $message = "$created entreprises générées avec leurs administrateurs !";
    if ($generateProducts) {
        $message .= " " . count($newProductIds) . " produits créés.";
    }
    if ($generateUsers) {
        $message .= " $usersCreated utilisateurs générés | $reviewsCreated avis | $likesCreated likes | $tasksCompleted tâches.";
    }
    addToast('success', $message);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur lors de la génération: ' . $e->getMessage());
}

redirect(url('index.php?page=superadmin_dashboard'));

// ============================================================
// HELPERS LOCAUX
// ============================================================

/**
 * Note pondérée réaliste : majorité de 4-5, peu de 1-2
 */
function weightedRating(): int
{
    $rand = rand(1, 100);
    if ($rand <= 40)
        return 5;  // 40%
    if ($rand <= 75)
        return 4;  // 35%
    if ($rand <= 90)
        return 3;  // 15%
    if ($rand <= 97)
        return 2;  // 7%
    return 1;                   // 3%
}
