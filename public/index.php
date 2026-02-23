<?php
// Point d'entrée central TrustPick V2
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Pages autorisées
$allowed = [
    // Pages publiques
    'home',
    'catalog',
    'product',
    'company',
    'login',
    'register',

    // Dashboard utilisateur
    'user_dashboard',
    'wallet',
    'tasks',
    'notifications',
    'referrals',
    'profile',

    // Dashboard Admin Entreprise
    'admin_dashboard',
    'company_dashboard',
    'admin_products',
    'admin_reviews',
    'admin_stats',

    // Dashboard Super Admin
    'superadmin_dashboard',
    'manage_users',
    'manage_companies',
    'manage_withdrawals',
    'system_stats',

    // Documentation
    'documentation',

    // Erreurs
    '404',
    '403'
];

if (!in_array($page, $allowed)) {
    http_response_code(404);
    $page = '404';
}

require __DIR__ . '/../views/layouts/header.php';

if ($page === '404' || $page === '403') {
    require __DIR__ . "/../views/{$page}.php";
} else {
    $viewFile = __DIR__ . "/../views/{$page}.php";
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
    }
}

require __DIR__ . '/../views/layouts/footer.php';
