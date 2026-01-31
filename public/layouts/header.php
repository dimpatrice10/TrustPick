<?php
// Header - À inclure en haut de chaque page
require_once __DIR__ . '/../includes/session.php';

// Vérifier la session
SessionManager::requireLogin();

$currentUser = SessionManager::getCurrentUser();
$page_title = $page_title ?? 'TrustPick';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - TrustPick</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="assets/css/trustpick.css">
    <link rel="stylesheet" href="assets/css/components.css">

    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <!-- Données utilisateur pour JavaScript -->
    <script id="user-data" type="application/json">
        <?php echo json_encode($currentUser); ?>
    </script>

    <!-- Header principal -->
    <header class="trustpick-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-6 col-md-3">
                    <a href="<?php
                    echo match ($currentUser['role']) {
                        'super_admin' => 'index.php?page=superadmin_dashboard',
                        'admin_entreprise' => 'index.php?page=admin_dashboard',
                        default => 'index.php?page=user_dashboard'
                    };
                    ?>" class="trustpick-logo">
                        🛍️ TrustPick
                    </a>
                </div>

                <!-- Navigation -->
                <div class="col-6 col-md-9">
                    <div class="d-flex justify-content-end align-items-center gap-3">
                        <!-- Solde (sauf pour super admin) -->
                        <?php if ($currentUser['role'] !== 'super_admin'): ?>
                            <a href="index.php?page=wallet" class="text-white text-decoration-none">
                                <strong>💰 <?php echo number_format($currentUser['balance'], 0, ',', ' '); ?> FCFA</strong>
                            </a>
                        <?php endif; ?>

                        <!-- Notifications -->
                        <div class="position-relative">
                            <a href="index.php?page=user_notifications" class="text-white text-decoration-none">
                                🔔
                                <span class="notification-badge" id="notif-count">0</span>
                            </a>
                        </div>

                        <!-- Menu utilisateur -->
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="userMenu"
                                data-bs-toggle="dropdown">
                                👤 <?php echo htmlspecialchars($currentUser['name']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="index.php?page=profile">Mon Profil</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Déconnexion</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">