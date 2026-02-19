<?php
/**
 * TrustPick V2 - Instructions de paiement Mobile Money
 * Orange Money & MTN Mobile Money
 */

if (!isset($_SESSION['payment_reference'])) {
    redirect(url('index.php?page=wallet'));
}

$reference = $_SESSION['payment_reference'];
$amount = formatFCFA($_SESSION['payment_amount'] ?? 0);
$channel = $_SESSION['payment_channel'] ?? 'orange'; // orange ou mtn
$phone = $_SESSION['payment_phone'] ?? '';
$config = require __DIR__ . '/../includes/config.php';

// Comptes de réception selon l'opérateur
$receivingAccount = $channel === 'orange'
    ? $config['payment']['receiving_accounts']['orange']
    : $config['payment']['receiving_accounts']['mtn'];

$operatorName = $channel === 'orange' ? 'Orange Money' : 'MTN Mobile Money';
$ussdCode = $channel === 'orange' ? '#150#' : '#126#';
$colorClass = $channel === 'orange' ? 'orange' : 'mtn';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructions de Paiement - <?php echo $operatorName; ?></title>
    <link rel="stylesheet" href="<?php echo url('assets/css/app.css'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .payment-instructions {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
        }

        .operator-badge {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }

        .operator-badge.orange {
            background: linear-gradient(135deg, #ff6b00, #ff8c00);
            color: white;
        }

        .operator-badge.mtn {
            background: linear-gradient(135deg, #ffcc00, #ffdd00);
            color: #000;
        }

        .step-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #4CAF50;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .ussd-code {
            font-size: 2em;
            font-weight: bold;
            color: #2196F3;
            padding: 20px;
            background: #f0f8ff;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
            cursor: pointer;
            position: relative;
        }

        .ussd-code:hover {
            background: #e1f0ff;
        }

        .copy-btn {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: #2196F3;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.5em;
        }

        .copy-btn:hover {
            background: #1976D2;
        }

        .account-number {
            font-size: 1.8em;
            font-weight: bold;
            color: #333;
            padding: 15px;
            background: #fff3cd;
            border-radius: 8px;
            text-align: center;
            margin: 15px 0;
        }

        .amount-highlight {
            font-size: 1.5em;
            font-weight: bold;
            color: #4CAF50;
        }

        .status-checking {
            padding: 20px;
            background: #e3f2fd;
            border-radius: 10px;
            text-align: center;
            margin-top: 30px;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #2196F3;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            display: none;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="payment-instructions">
            <!-- Badge Opérateur -->
            <div class="text-center">
                <span class="operator-badge <?php echo $colorClass; ?>">
                    <?php echo $operatorName; ?>
                </span>
            </div>

            <!-- Titre -->
            <h2 class="text-center mb-4">Instructions de Paiement</h2>

            <!-- Montant -->
            <div class="text-center mb-4">
                <div class="amount-highlight"><?php echo $amount; ?></div>
                <small class="text-muted">Référence: <?php echo htmlspecialchars($reference); ?></small>
            </div>

            <!-- Instructions étape par étape -->
            <div class="step-card">
                <div class="step-number">1</div>
                <h5>Composez le code USSD</h5>
                <p>Sur votre téléphone <?php echo $operatorName; ?>, composez:</p>
                <div class="ussd-code" onclick="copyToClipboard('<?php echo $ussdCode; ?>', this)">
                    <?php echo $ussdCode; ?>
                    <button class="copy-btn">Copier</button>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">2</div>
                <h5>Sélectionnez "Transfert d'argent"</h5>
                <p>Dans le menu qui s'affiche, choisissez l'option de transfert d'argent.</p>
            </div>

            <div class="step-card">
                <div class="step-number">3</div>
                <h5>Entrez le numéro du bénéficiaire</h5>
                <p>Saisissez ce numéro:</p>
                <div class="account-number" onclick="copyToClipboard('<?php echo $receivingAccount; ?>', this)">
                    <?php echo $receivingAccount; ?>
                    <button class="copy-btn" style="font-size: 0.6em;">Copier</button>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">4</div>
                <h5>Entrez le montant</h5>
                <p>Montant à transférer: <strong><?php echo $amount; ?></strong></p>
            </div>

            <div class="step-card">
                <div class="step-number">5</div>
                <h5>Confirmez avec votre code PIN</h5>
                <p>Entrez votre code PIN <?php echo $operatorName; ?> pour valider la transaction.</p>
            </div>

            <!-- Avertissement -->
            <div class="warning-box">
                <strong>⚠️ Important:</strong>
                <ul class="mb-0">
                    <li>Assurez-vous d'avoir suffisamment de solde</li>
                    <li>Le montant exact doit être transféré: <?php echo $amount; ?></li>
                    <li>Le numéro bénéficiaire: <?php echo $receivingAccount; ?></li>
                </ul>
            </div>

            <!-- Vérification du statut -->
            <div class="status-checking" id="statusCheck">
                <div class="spinner"></div>
                <p><strong>Vérification du paiement en cours...</strong></p>
                <p class="text-muted small">Cette page se mettra à jour automatiquement lorsque le paiement sera
                    confirmé.</p>
                <p class="text-muted small" id="checkCounter">Prochaine vérification dans 10 secondes...</p>
            </div>

            <!-- Message de succès (caché par défaut) -->
            <div class="success-message" id="successMessage">
                <h4>✅ Paiement confirmé !</h4>
                <p>Votre compte a été crédité avec succès.</p>
                <p>Redirection automatique...</p>
            </div>

            <!-- Boutons d'action -->
            <div class="text-center mt-4">
                <a href="<?php echo url('index.php?page=wallet'); ?>" class="btn btn-outline-secondary">
                    Retour au portefeuille
                </a>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const paymentReference = '<?php echo addslashes($reference); ?>';
        const checkInterval = 10000; // 10 secondes
        let checkCount = 0;
        let countdown = 10;
        let countdownInterval;

        // Fonction de copie
        function copyToClipboard(text, element) {
            navigator.clipboard.writeText(text).then(() => {
                const btn = element.querySelector('.copy-btn');
                const originalText = btn.textContent;
                btn.textContent = 'Copié!';
                btn.style.background = '#4CAF50';

                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '#2196F3';
                }, 2000);
            }).catch(err => {
                console.error('Erreur de copie:', err);
                alert('Impossible de copier. Veuillez sélectionner et copier manuellement.');
            });
        }

        // Compte à rebours
        function startCountdown() {
            countdown = 10;
            const counterElement = document.getElementById('checkCounter');

            if (countdownInterval) {
                clearInterval(countdownInterval);
            }

            countdownInterval = setInterval(() => {
                countdown--;
                if (countdown > 0) {
                    counterElement.textContent = `Prochaine vérification dans ${countdown} secondes...`;
                } else {
                    counterElement.textContent = 'Vérification en cours...';
                }
            }, 1000);
        }

        // Vérifier le statut du paiement
        function checkPaymentStatus() {
            checkCount++;

            fetch(`<?php echo url('api/check-payment-status.php'); ?>?reference=${encodeURIComponent(paymentReference)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Statut vérifié:', data);

                    if (data.success && (data.status === 'success' || data.status === 'complete')) {
                        // Paiement confirmé !
                        clearInterval(countdownInterval);
                        document.getElementById('statusCheck').style.display = 'none';
                        document.getElementById('successMessage').style.display = 'block';

                        // Rediriger après 3 secondes
                        setTimeout(() => {
                            window.location.href = '<?php echo url('index.php?page=wallet'); ?>';
                        }, 3000);
                    } else if (data.status === 'failed') {
                        // Paiement échoué
                        clearInterval(countdownInterval);
                        document.getElementById('statusCheck').innerHTML = `
                            <div class="alert alert-danger">
                                <strong>❌ Paiement échoué</strong><br>
                                ${data.message || 'Veuillez réessayer ou contacter le support.'}
                            </div>
                        `;
                    } else {
                        // Toujours en attente, continuer les vérifications
                        startCountdown();
                    }
                })
                .catch(error => {
                    console.error('Erreur de vérification:', error);
                    // Continuer à vérifier malgré l'erreur
                    startCountdown();
                });
        }

        // Démarrer les vérifications
        startCountdown();
        checkPaymentStatus();
        setInterval(checkPaymentStatus, checkInterval);

        // Nettoyer au déchargement de la page
        window.addEventListener('beforeunload', () => {
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
        });
    </script>
</body>

</html>