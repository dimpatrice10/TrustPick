<!-- Sidebar Super Admin -->
<div class="col-md-3 col-lg-2 trustpick-sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="index.php?page=superadmin_dashboard"
                class="<?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <span class="sidebar-icon">🎯</span>
                <span>Dashboard Global</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=superadmin_companies"
                class="<?php echo ($current_page ?? '') === 'companies' ? 'active' : ''; ?>">
                <span class="sidebar-icon">🏢</span>
                <span>Entreprises</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=superadmin_users"
                class="<?php echo ($current_page ?? '') === 'users' ? 'active' : ''; ?>">
                <span class="sidebar-icon">👥</span>
                <span>Utilisateurs</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=superadmin_tasks_config"
                class="<?php echo ($current_page ?? '') === 'tasks-config' ? 'active' : ''; ?>">
                <span class="sidebar-icon">⚙️</span>
                <span>Configuration Tâches</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=superadmin_withdrawals"
                class="<?php echo ($current_page ?? '') === 'withdrawals' ? 'active' : ''; ?>">
                <span class="sidebar-icon">💵</span>
                <span>Retraits</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=superadmin_settings"
                class="<?php echo ($current_page ?? '') === 'settings' ? 'active' : ''; ?>">
                <span class="sidebar-icon">🔧</span>
                <span>Paramètres</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=superadmin_logs"
                class="<?php echo ($current_page ?? '') === 'logs' ? 'active' : ''; ?>">
                <span class="sidebar-icon">📋</span>
                <span>Logs Système</span>
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