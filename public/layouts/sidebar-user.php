<!-- Sidebar utilisateur -->
<div class="col-md-3 col-lg-2 trustpick-sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="index.php?page=user_dashboard"
                class="<?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <span class="sidebar-icon">🏠</span>
                <span>Accueil</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=catalog"
                class="<?php echo ($current_page ?? '') === 'products' ? 'active' : ''; ?>">
                <span class="sidebar-icon">🛍️</span>
                <span>Produits</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=tasks" class="<?php echo ($current_page ?? '') === 'tasks' ? 'active' : ''; ?>">
                <span class="sidebar-icon">✅</span>
                <span>Mes Tâches</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=referrals"
                class="<?php echo ($current_page ?? '') === 'referrals' ? 'active' : ''; ?>">
                <span class="sidebar-icon">👥</span>
                <span>Parrainages</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=wallet" class="<?php echo ($current_page ?? '') === 'wallet' ? 'active' : ''; ?>">
                <span class="sidebar-icon">💰</span>
                <span>Portefeuille</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=user_notifications"
                class="<?php echo ($current_page ?? '') === 'notifications' ? 'active' : ''; ?>">
                <span class="sidebar-icon">🔔</span>
                <span>Notifications</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=profile" class="<?php echo ($current_page ?? '') === 'profile' ? 'active' : ''; ?>">
                <span class="sidebar-icon">👤</span>
                <span>Mon Profil</span>
            </a>
        </li>

        <li style="margin-top: 2rem;">
            <a href="logout.php" style="color: #ef4444;">
                <span class="sidebar-icon">🚪</span>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>
</div>