<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - TrustPick</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="assets/css/trustpick.css">
    <link rel="stylesheet" href="assets/css/components.css">

    <style>
        body {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            max-width: 450px;
            width: 90%;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }

        .login-logo p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .cau-input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .cau-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        .cau-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .cau-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #0ea5e9);
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .login-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .login-help {
            text-align: center;
            margin-top: 2rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .lockout-message {
            background-color: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <?php
    require_once '../includes/config.php';
    require_once '../includes/db.php';
    require_once '../includes/auth.php';
    require_once '../includes/session.php';

    // Si déjà connecté, rediriger selon le rôle
    if (SessionManager::isLoggedIn()) {
        SessionManager::redirectByRole();
    }

    $error = '';
    $lockoutMessage = '';

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cau = strtoupper(trim($_POST['cau'] ?? ''));

        if (empty($cau)) {
            $error = 'Veuillez saisir votre Code d\'Accès Utilisateur';
        } else {
            // Vérifier si le compte est bloqué
            if (SessionManager::isLocked($cau)) {
                $remaining = SessionManager::getLockoutTime($cau);
                $minutes = ceil($remaining / 60);
                $lockoutMessage = "Trop de tentatives. Compte bloqué pendant {$minutes} minute(s).";
            } else {
                try {
                    $db = Database::getInstance()->getConnection();
                    $auth = new AuthCAU($db);

                    $result = $auth->loginWithCAU($cau);

                    if ($result['success']) {
                        // Enregistrer le succès
                        SessionManager::recordLoginAttempt($cau, true);

                        // Créer la session
                        SessionManager::create($result['user']);

                        // Rediriger selon le rôle
                        SessionManager::redirectByRole();
                    } else {
                        // Enregistrer l'échec
                        SessionManager::recordLoginAttempt($cau, false);
                        $error = $result['message'];
                    }
                } catch (Exception $e) {
                    $error = 'Erreur de connexion au serveur';
                    error_log('Login error: ' . $e->getMessage());
                }
            }
        }
    }
    ?>

    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <h1>🛍️ TrustPick</h1>
                <p>Plateforme d'avis et de recommandations</p>
            </div>

            <?php if ($lockoutMessage): ?>
                <div class="lockout-message">
                    <strong>🔒 <?php echo htmlspecialchars($lockoutMessage); ?></strong>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <span class="alert-icon">❌</span>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="cau-input-group">
                    <span class="cau-icon">🔑</span>
                    <input type="text" name="cau" id="cau" class="cau-input" placeholder="Ex: USER001, ADMIN001"
                        maxlength="20" required autocomplete="off" autofocus>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    Se connecter
                </button>
            </form>

            <div class="login-help">
                <p><strong>Code d'Accès Utilisateur (CAU)</strong></p>
                <p>Votre CAU vous a été fourni lors de votre inscription.<br>
                    Ex: USER001, TECH001, ADMIN001</p>
                <p style="margin-top: 1rem;">
                    <small>Besoin d'aide ? Contactez le support.</small>
                </p>
            </div>
        </div>

        <p style="text-align: center; color: white; margin-top: 2rem; font-size: 0.9rem;">
            © 2026 TrustPick - Tous droits réservés
        </p>
    </div>

    <script>
        // Validation en temps réel
        const cauInput = document.getElementById('cau');
        const loginBtn = document.getElementById('loginBtn');
        const loginForm = document.getElementById('loginForm');

        cauInput.addEventListener('input', function () {
            // Convertir en majuscules
            this.value = this.value.toUpperCase();

            // Activer/désactiver le bouton
            loginBtn.disabled = this.value.trim().length < 4;
        });

        // Prévenir les doubles soumissions
        loginForm.addEventListener('submit', function () {
            loginBtn.disabled = true;
            loginBtn.textContent = 'Connexion en cours...';
        });

        // Auto-focus sur l'input
        window.addEventListener('load', function () {
            cauInput.focus();
        });
    </script>
</body>

</html>