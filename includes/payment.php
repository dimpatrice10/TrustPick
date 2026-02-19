<?php
/**
 * TrustPick V2 - Gestionnaire de Paiements Mobile Money
 * Intégration avec MeSomb pour Orange Money et MTN Mobile Money
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/task_manager.php';

class PaymentManager
{
    private $config;
    private $pdo;
    private $apiUrl;
    private $applicationKey;
    private $accessKey;
    private $secretKey;

    public function __construct()
    {
        $this->config = require __DIR__ . '/config.php';
        $this->pdo = Database::getInstance()->getConnection();

        $mesombConfig = $this->config['payment']['mesomb'];
        $this->apiUrl = $mesombConfig['api_url'];
        $this->applicationKey = $mesombConfig['application_key'];
        $this->accessKey = $mesombConfig['access_key'];
        $this->secretKey = $mesombConfig['secret_key'];
    }

    /**
     * Initier un paiement via MeSomb (Collection)
     * 
     * @param int $userId ID de l'utilisateur
     * @param float $amount Montant en FCFA
     * @param string $phone Numéro de téléphone
     * @param string $service Service de paiement (MTN ou ORANGE)
     * @return array Résultat avec code, message et données
     */
    public function initiatePayment($userId, $amount, $phone, $service = 'ORANGE')
    {
        try {
            // Validation
            $minDeposit = $this->config['payment']['min_deposit'];
            if ($amount < $minDeposit) {
                return [
                    'success' => false,
                    'message' => "Le montant minimum est de {$minDeposit} FCFA"
                ];
            }

            // Nettoyer le numéro de téléphone
            $phone = $this->cleanPhoneNumber($phone);
            if (!$this->validatePhoneNumber($phone, $service)) {
                return [
                    'success' => false,
                    'message' => 'Numéro de téléphone invalide pour cet opérateur'
                ];
            }

            // Générer une référence unique
            $reference = 'TP_' . $userId . '_' . time();

            // Créer l'enregistrement de transaction en attente
            $stmt = $this->pdo->prepare('
                INSERT INTO payment_transactions 
                (user_id, reference, amount, phone, channel, status, created_at) 
                VALUES (?, ?, ?, ?, ?, "pending", NOW())
            ');
            $channel = strtolower($service);
            $stmt->execute([$userId, $reference, $amount, $phone, $channel]);
            $transactionId = $this->pdo->lastInsertId();

            // Appeler l'API MeSomb pour collecter le paiement
            $result = $this->makeCollection($amount, $phone, $service, $reference);

            if ($result['success']) {
                // Mettre à jour avec les informations MeSomb
                $this->pdo->prepare('
                    UPDATE payment_transactions 
                    SET mesomb_reference = ?, status = ?
                    WHERE id = ?
                ')->execute([
                            $result['transaction_id'] ?? null,
                            $result['status'] ?? 'pending',
                            $transactionId
                        ]);

                // Si le paiement est immédiatement réussi (cas MeSomb)
                if (isset($result['status']) && $result['status'] === 'success') {
                    $this->creditUserAccount($transactionId);
                }

                return [
                    'success' => true,
                    'message' => $result['message'] ?? 'Paiement initié avec succès',
                    'reference' => $reference,
                    'transaction_id' => $transactionId,
                    'status' => $result['status'] ?? 'pending',
                    'requires_ussd' => $result['requires_ussd'] ?? false
                ];
            } else {
                // Échec de l'initialisation
                $this->pdo->prepare('
                    UPDATE payment_transactions 
                    SET status = "failed"
                    WHERE id = ?
                ')->execute([$transactionId]);

                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Échec de l\'initialisation du paiement'
                ];
            }

        } catch (Exception $e) {
            error_log('PaymentManager Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation du paiement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Effectuer une collection MeSomb
     * 
     * @param float $amount Montant
     * @param string $phone Numéro de téléphone
     * @param string $service Service (MTN ou ORANGE)
     * @param string $reference Référence de transaction
     * @return array Résultat de la collection
     */
    private function makeCollection($amount, $phone, $service, $reference)
    {
        try {
            $endpoint = '/payment/collect/';

            // Préparer les données
            $data = [
                'amount' => intval($amount),
                'service' => strtoupper($service), // MTN ou ORANGE
                'payer' => $phone,
                'currency' => 'XAF',
                'country' => 'CM',
                'reference' => $reference,
                'fees' => false // TrustPick paie les frais
            ];

            // Générer la signature (nonce + timestamp)
            $nonce = bin2hex(random_bytes(16));
            $timestamp = time();

            // Appel API
            $response = $this->callMeSombAPI($endpoint, $data, 'POST', $nonce, $timestamp);

            if (isset($response['success']) && $response['success'] === true) {
                return [
                    'success' => true,
                    'message' => 'Paiement collecté avec succès',
                    'transaction_id' => $response['transaction']['pk'] ?? null,
                    'status' => 'success',
                    'requires_ussd' => false,
                    'data' => $response
                ];
            } elseif (isset($response['detail'])) {
                // Erreur spécifique MeSomb
                return [
                    'success' => false,
                    'message' => $response['detail'],
                    'requires_ussd' => $this->shouldUseUSSD($response['detail'])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur de paiement MeSomb',
                    'requires_ussd' => true
                ];
            }

        } catch (Exception $e) {
            error_log('MeSomb Collection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'requires_ussd' => true
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement
     * 
     * @param string $reference Référence de la transaction
     * @return array Statut du paiement
     */
    public function checkPaymentStatus($reference)
    {
        try {
            // Récupérer la transaction locale
            $stmt = $this->pdo->prepare('
                SELECT * FROM payment_transactions 
                WHERE reference = ?
                LIMIT 1
            ');
            $stmt->execute([$reference]);
            $transaction = $stmt->fetch();

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction introuvable',
                    'status' => 'not_found'
                ];
            }

            // Si déjà complétée, retourner le statut
            if ($transaction['status'] === 'success' || $transaction['status'] === 'complete') {
                return [
                    'success' => true,
                    'status' => 'success',
                    'transaction' => $transaction
                ];
            }

            // Sinon vérifier auprès de MeSomb si on a un ID MeSomb
            if (!empty($transaction['mesomb_reference'])) {
                $endpoint = '/payment/transactions/' . $transaction['mesomb_reference'] . '/';
                $response = $this->callMeSombAPI($endpoint, null, 'GET');

                if (isset($response['status'])) {
                    return [
                        'success' => true,
                        'status' => $response['status'],
                        'data' => $response
                    ];
                }
            }

            // Statut local par défaut
            return [
                'success' => true,
                'status' => $transaction['status'],
                'transaction' => $transaction
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Créditer le compte utilisateur après paiement réussi
     * 
     * @param int $transactionId ID de la transaction dans payment_transactions
     * @return bool Succès
     */
    private function creditUserAccount($transactionId)
    {
        try {
            // Récupérer la transaction
            $stmt = $this->pdo->prepare('SELECT * FROM payment_transactions WHERE id = ?');
            $stmt->execute([$transactionId]);
            $transaction = $stmt->fetch();

            if (!$transaction || $transaction['status'] === 'success') {
                return false; // Déjà traitée
            }

            $this->pdo->beginTransaction();

            $userId = $transaction['user_id'];
            $amount = $transaction['amount'];

            // Créditer le solde
            $this->pdo->prepare('
                UPDATE users SET balance = balance + ? WHERE id = ?
            ')->execute([$amount, $userId]);

            // Récupérer le nouveau solde
            $balanceStmt = $this->pdo->prepare('SELECT balance FROM users WHERE id = ?');
            $balanceStmt->execute([$userId]);
            $newBalance = $balanceStmt->fetchColumn();

            // Enregistrer dans l'historique des transactions
            $this->pdo->prepare('
                INSERT INTO transactions 
                (user_id, type, amount, description, reference_type, reference_id, balance_after, created_at)
                VALUES (?, "deposit", ?, ?, "payment", ?, ?, NOW())
            ')->execute([
                        $userId,
                        $amount,
                        'Dépôt Mobile Money - ' . strtoupper($transaction['channel']),
                        $transactionId,
                        $newBalance
                    ]);

            // Mettre à jour le statut de la transaction de paiement
            $this->pdo->prepare('
                UPDATE payment_transactions 
                SET status = "success", completed_at = NOW()
                WHERE id = ?
            ')->execute([$transactionId]);

            // Vérifier et compléter la tâche quotidienne
            require_once __DIR__ . '/task_manager.php';
            $checkTask = TaskManager::isTaskCompletedToday($userId, 'deposit_5000', $this->pdo);

            if (!$checkTask && $amount >= $this->config['payment']['min_deposit']) {
                TaskManager::completeTask($userId, 'deposit_5000', $this->pdo);

                // Notification
                $this->pdo->prepare('
                    INSERT INTO notifications (user_id, type, title, message, created_at)
                    VALUES (?, "reward", "Dépôt validé", ?, NOW())
                ')->execute([
                            $userId,
                            'Votre dépôt de ' . formatFCFA($amount) . ' a été crédité. Tâche quotidienne validée !'
                        ]);
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Credit Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Traiter une notification webhook de MeSomb
     * 
     * @param array $data Données du webhook
     * @return bool Succès du traitement
     */
    public function processWebhook($data)
    {
        try {
            $reference = $data['reference'] ?? null;
            $status = $data['status'] ?? null;

            if (!$reference) {
                throw new Exception('Webhook invalide: référence manquante');
            }

            // Récupérer la transaction
            $stmt = $this->pdo->prepare('
                SELECT * FROM payment_transactions 
                WHERE reference = ?
                LIMIT 1
            ');
            $stmt->execute([$reference]);
            $transaction = $stmt->fetch();

            if (!$transaction) {
                throw new Exception('Transaction introuvable: ' . $reference);
            }

            // Si déjà traitée, ignorer
            if ($transaction['status'] === 'success') {
                return true;
            }

            // Mettre à jour le statut
            $this->pdo->prepare('
                UPDATE payment_transactions 
                SET status = ?, webhook_data = ?, completed_at = NOW()
                WHERE id = ?
            ')->execute([
                        $status === 'SUCCESS' ? 'success' : 'failed',
                        json_encode($data),
                        $transaction['id']
                    ]);

            // Si succès, créditer le compte
            if ($status === 'SUCCESS') {
                $this->creditUserAccount($transaction['id']);
            }

            return true;

        } catch (Exception $e) {
            error_log('Webhook Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Appel API MeSomb
     * 
     * @param string $endpoint Endpoint de l'API
     * @param array|null $data Données à envoyer
     * @param string $method Méthode HTTP
     * @param string $nonce Nonce pour la signature
     * @param int $timestamp Timestamp pour la signature
     * @return array Réponse de l'API
     */
    private function callMeSombAPI($endpoint, $data = null, $method = 'POST', $nonce = null, $timestamp = null)
    {
        $url = $this->apiUrl . $endpoint;

        // Générer nonce et timestamp si non fournis
        if ($nonce === null) {
            $nonce = bin2hex(random_bytes(16));
        }
        if ($timestamp === null) {
            $timestamp = time();
        }

        // Générer la signature
        $signature = $this->generateSignature($method, $endpoint, $nonce, $timestamp, $data);

        $headers = [
            'X-MeSomb-Application: ' . $this->applicationKey,
            'X-MeSomb-AccessKey: ' . $this->accessKey,
            'X-MeSomb-Nonce: ' . $nonce,
            'X-MeSomb-Timestamp: ' . $timestamp,
            'X-MeSomb-Signature: ' . $signature,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Erreur cURL: ' . $error);
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = $decodedResponse['detail'] ?? $decodedResponse['message'] ?? 'Erreur inconnue';
            throw new Exception('Erreur API MeSomb: ' . $errorMsg);
        }

        return $decodedResponse;
    }

    /**
     * Générer la signature pour MeSomb
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param string $nonce Nonce
     * @param int $timestamp Timestamp
     * @param array|null $data Données de la requête
     * @return string Signature
     */
    private function generateSignature($method, $endpoint, $nonce, $timestamp, $data = null)
    {
        // Construction du message à signer selon la doc MeSomb
        $message = $method . "\n" . $endpoint . "\n" . $timestamp . "\n" . $nonce;

        if ($data) {
            $message .= "\n" . json_encode($data);
        }

        // Signature HMAC-SHA256 avec la secret key
        $signature = hash_hmac('sha256', $message, $this->secretKey);

        return $signature;
    }

    /**
     * Nettoyer un numéro de téléphone
     */
    private function cleanPhoneNumber($phone)
    {
        // Retirer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ajouter 237 si nécessaire (Cameroun)
        if (strlen($phone) === 9) {
            $phone = '237' . $phone;
        } elseif (strlen($phone) === 12 && substr($phone, 0, 3) === '237') {
            // Déjà au bon format
        } else {
            // Retirer le + ou 00 au début
            $phone = ltrim($phone, '0+');
        }

        return $phone;
    }

    /**
     * Valider un numéro de téléphone
     */
    private function validatePhoneNumber($phone, $service)
    {
        $phone = $this->cleanPhoneNumber($phone);

        // Vérifier la longueur (237 + 9 chiffres = 12)
        if (strlen($phone) !== 12 || substr($phone, 0, 3) !== '237') {
            return false;
        }

        // Obtenir l'indicatif opérateur (2 chiffres après 237)
        $operator = substr($phone, 3, 2);

        // Validation selon l'opérateur
        if (strtoupper($service) === 'ORANGE') {
            // Orange: 65, 69
            return in_array($operator, ['65', '69']);
        } elseif (strtoupper($service) === 'MTN') {
            // MTN: 67, 68, 65, 69
            return in_array($operator, ['67', '68', '65', '69']);
        }

        return true;
    }

    /**
     * Déterminer si on doit utiliser USSD selon l'erreur
     */
    private function shouldUseUSSD($errorMessage)
    {
        $ussdKeywords = ['pin', 'confirmation', 'approved', 'timeout', 'pending'];
        foreach ($ussdKeywords as $keyword) {
            if (stripos($errorMessage, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtenir l'historique des paiements d'un utilisateur
     */
    public function getUserPaymentHistory($userId, $limit = 10)
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM payment_transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ');
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}