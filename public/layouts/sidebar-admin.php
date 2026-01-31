<!-- Sidebar Admin Entreprise -->
<div class="col-md-3 col-lg-2 trustpick-sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="index.php?page=admin_dashboard"
                class="<?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <span class="sidebar-icon">📊</span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_products"
                class="<?php echo ($current_page ?? '') === 'products' ? 'active' : ''; ?>">
                <span class="sidebar-icon">🛍️</span>
                <span>Mes Produits</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_reviews"
                class="<?php echo ($current_page ?? '') === 'reviews' ? 'active' : ''; ?>">
                <span class="sidebar-icon">⭐</span>
                <span>Avis Clients</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_analytics"
                class="<?php echo ($current_page ?? '') === 'analytics' ? 'active' : ''; ?>">
                <span class="sidebar-icon">📈</span>
                <span>Statistiques</span>
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