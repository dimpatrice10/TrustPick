<?php
/**
 * Interface de gestion des utilisateurs (Super Admin uniquement)
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'utilisateur est super admin
SessionManager::requireRole('super_admin');

$pdo = Database::getInstance()->getConnection();

// Récupérer tous les utilisateurs
$users = $pdo->query('
    SELECT id, name, cau, phone, role, balance, created_at, last_login, is_active
    FROM users
    ORDER BY created_at DESC
')->fetchAll();

include __DIR__ . '/layouts/header.php';
?>

<main class="container fade-up" style="padding:40px 20px;max-width:1200px;margin:0 auto">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px">
        <h1 style="margin:0"><i class="bi bi-people me-2"></i>Gestion des Utilisateurs</h1>
        <button onclick="document.getElementById('create-user-modal').style.display='flex'"
            class="btn btn-animated ripple">
            <i class="bi bi-plus-circle me-1"></i>Créer un utilisateur
        </button>
    </div>

    <!-- Statistiques rapides -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:32px">
        <div
            style="background:white;padding:20px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <div style="color:#6c757d;font-size:13px;margin-bottom:8px">Total Utilisateurs</div>
            <div style="font-size:28px;font-weight:700;color:#1a1f36"><?= count($users) ?></div>
        </div>
        <div
            style="background:white;padding:20px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <div style="color:#6c757d;font-size:13px;margin-bottom:8px">Actifs</div>
            <div style="font-size:28px;font-weight:700;color:#10b981">
                <?= count(array_filter($users, fn($u) => $u['is_active'])) ?>
            </div>
        </div>
        <div
            style="background:white;padding:20px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <div style="color:#6c757d;font-size:13px;margin-bottom:8px">Admins</div>
            <div style="font-size:28px;font-weight:700;color:#0066cc">
                <?= count(array_filter($users, fn($u) => in_array($u['role'], ['super_admin', 'admin_entreprise']))) ?>
            </div>
        </div>
    </div>

    <!-- Tableau des utilisateurs -->
    <section
        style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="border-bottom:2px solid #e0e4e8;text-align:left">
                        <th style="padding:12px">Nom</th>
                        <th style="padding:12px">CAU</th>
                        <th style="padding:12px">Téléphone</th>
                        <th style="padding:12px">Rôle</th>
                        <th style="padding:12px">Solde</th>
                        <th style="padding:12px">Statut</th>
                        <th style="padding:12px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr style="border-bottom:1px solid #e0e4e8">
                            <td style="padding:12px">
                                <strong><?= clean($u['name']) ?></strong><br>
                                <small style="color:#6c757d">Inscrit <?= formatRelativeTime($u['created_at']) ?></small>
                            </td>
                            <td style="padding:12px">
                                <code style="background:#f1f5f9;padding:4px 8px;border-radius:4px;font-size:12px">
                              <?= $u['cau'] ?>
                            </code>
                            </td>
                            <td style="padding:12px"><?= clean($u['phone']) ?></td>
                            <td style="padding:12px"><?= roleBadge($u['role']) ?></td>
                            <td style="padding:12px"><strong><?= formatFCFA($u['balance']) ?></strong></td>
                            <td style="padding:12px">
                                <?= $u['is_active']
                                    ? '<span class="badge bg-success">Actif</span>'
                                    : '<span class="badge bg-danger">Inactif</span>' ?>
                            </td>
                            <td style="padding:12px">
                                <form action="<?= url('actions/toggle_user.php') ?>" method="POST" style="display:inline">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit"
                                        style="padding:6px 12px;border:1px solid #e0e4e8;background:white;border-radius:6px;cursor:pointer;font-size:13px">
                                        <?= $u['is_active'] ? '<i class="bi bi-x-circle me-1"></i>Désactiver' : '<i class="bi bi-check-circle me-1"></i>Activer' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Modal Créer Utilisateur -->
    <div id="create-user-modal"
        style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center"
        onclick="if(event.target.id==='create-user-modal') this.style.display='none'">
        <div style="background:white;padding:32px;border-radius:12px;max-width:600px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2)"
            onclick="event.stopPropagation()">
            <h2 style="margin-top:0"><i class="bi bi-plus-circle me-2"></i>Créer un Utilisateur</h2>
            <form action="<?= url('actions/create_user_admin.php') ?>" method="POST">
                <div style="margin-bottom:16px">
                    <label style="display:block;margin-bottom:8px;font-weight:600">Nom complet</label>
                    <input type="text" name="name" required
                        style="width:100%;padding:12px;border:1px solid #e6eef8;border-radius:8px">
                </div>
                <div style="margin-bottom:16px">
                    <label style="display:block;margin-bottom:8px;font-weight:600">Téléphone</label>
                    <input type="text" name="phone" required placeholder="+237 690 123 456"
                        style="width:100%;padding:12px;border:1px solid #e6eef8;border-radius:8px">
                </div>
                <div style="margin-bottom:16px">
                    <label style="display:block;margin-bottom:8px;font-weight:600">Rôle</label>
                    <select name="role" required
                        style="width:100%;padding:12px;border:1px solid #e6eef8;border-radius:8px">
                        <option value="user">Utilisateur</option>
                        <option value="admin_entreprise">Admin Entreprise</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div style="margin-bottom:16px">
                    <label style="display:block;margin-bottom:8px;font-weight:600">Solde initial (optionnel)</label>
                    <input type="number" name="balance" value="5000" step="100" min="0"
                        style="width:100%;padding:12px;border:1px solid #e6eef8;border-radius:8px">
                    <small style="color:#6c757d">Le solde par défaut est <?= formatFCFA(5000) ?></small>
                </div>
                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px">
                    <button type="button" onclick="document.getElementById('create-user-modal').style.display='none'"
                        style="padding:10px 20px;border:1px solid #e0e4e8;background:white;border-radius:8px;cursor:pointer">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-animated ripple">
                        Créer l'utilisateur
                    </button>
                </div>
            </form>
        </div>
    </div>

</main>

<?php include __DIR__ . '/layouts/footer.php'; ?>