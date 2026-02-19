# 💰 Intégration MeSomb - TrustPick V2

## 📋 Vue d'ensemble

TrustPick V2 est maintenant intégré avec **MeSomb**, la plateforme de paiement Mobile Money pour le Cameroun. Les utilisateurs peuvent effectuer des dépôts via:

- 🟠 **Orange Money** (compte: 657317490)
- 🟡 **MTN Mobile Money** (compte: 683833646)

## 🔑 Configuration

### 1. Informations d'identification MeSomb

Les credentials MeSomb sont configurés dans `includes/config.php`:

```php
'mesomb' => [
    'application_key' => '18bfc8002ab9555601c82fcb07e2817e221dad36',
    'access_key' => '5c63e664-2993-4f11-9cea-54347347a307',
    'secret_key' => 'd68f6eb3-9a8b-4315-8228-587d6f25c2a4',
    'api_url' => 'https://mesomb.hachther.com/api/v1.1',
    'enabled' => true
]
```

### 2. Comptes de réception

```php
'receiving_accounts' => [
    'orange' => '657317490',
    'mtn' => '683833646'
]
```

### 3. Configuration du webhook

URL du webhook à configurer dans votre tableau de bord MeSomb:

```
https://votre-domaine.com/api/payment-webhook.php
```

Secret du webhook (pour la validation des signatures):

```php
'webhook_secret' => 'votre_secret_webhook'
```

## 📊 Base de données

### Installation

Exécutez le script SQL pour créer la table des paiements:

```bash
mysql -u root -p trustpick_v2 < db/add_payment_tables.sql
```

Ou via phpMyAdmin, importez le fichier `db/add_payment_tables.sql`.

### Tables créées

**payment_transactions** - Enregistre toutes les transactions de paiement

- `id` - Identifiant unique
- `user_id` - ID de l'utilisateur
- `reference` - Référence unique TrustPick (TP_USERID_TIMESTAMP)
- `mesomb_reference` - ID de transaction MeSomb
- `amount` - Montant en FCFA
- `phone` - Numéro de téléphone
- `channel` - Opérateur (orange ou mtn)
- `status` - Statut (pending, success, failed)
- `webhook_data` - Données brutes du webhook
- `created_at` - Date de création
- `completed_at` - Date de complétion

## 🚀 Utilisation

### Flux de paiement utilisateur

1. **Initiation du dépôt**
   - L'utilisateur accède à son portefeuille
   - Clique sur "Déposer des fonds"
   - Sélectionne Orange Money ou MTN Mobile Money
   - Entre le montant (minimum 5000 FCFA)
   - Entre son numéro de téléphone

2. **Page d'instructions**
   - Affiche les instructions USSD étape par étape
   - Code USSD: `#150#` pour Orange, `#126#` pour MTN
   - Numéro bénéficiaire affiché clairement
   - Montant à transférer

3. **Vérification automatique**
   - La page vérifie automatiquement le statut toutes les 10 secondes
   - Affiche un compte à rebours
   - Redirige automatiquement après confirmation

4. **Confirmation**
   - Le compte est crédité automatiquement
   - La tâche quotidienne "Dépôt 5000 FCFA" est validée
   - Notification envoyée à l'utilisateur

## 🔧 Structure des fichiers

### Configuration et logique métier

- `includes/config.php` - Configuration MeSomb
- `includes/payment.php` - Classe PaymentManager
- `actions/deposit.php` - Action de dépôt

### API et webhooks

- `api/payment-webhook.php` - Réception des notifications MeSomb
- `api/check-payment-status.php` - Vérification du statut (AJAX)

### Vues

- `views/wallet.php` - Page portefeuille avec modal de dépôt
- `views/payment_instructions.php` - Instructions de paiement USSD

### Base de données

- `db/add_payment_tables.sql` - Script de création des tables

## 📝 API MeSomb

### Endpoints utilisés

**Collection de paiement (POST)**

```
POST /payment/collect/
```

Payload:

```json
{
  "amount": 5000,
  "service": "ORANGE",
  "payer": "237657317490",
  "currency": "XAF",
  "country": "CM",
  "reference": "TP_123_1234567890",
  "fees": false
}
```

**Vérification du statut (GET)**

```
GET /payment/transactions/{transaction_id}/
```

### Authentification

Headers requis:

```
X-MeSomb-Application: {application_key}
X-MeSomb-AccessKey: {access_key}
X-MeSomb-Nonce: {nonce}
X-MeSomb-Timestamp: {timestamp}
X-MeSomb-Signature: {signature_hmac_sha256}
```

## 🔒 Sécurité

### Signature des requêtes

Chaque requête vers l'API MeSomb est signée avec HMAC-SHA256:

```php
$message = $method . "\n" . $endpoint . "\n" . $timestamp . "\n" . $nonce;
if ($data) {
    $message .= "\n" . json_encode($data);
}
$signature = hash_hmac('sha256', $message, $secretKey);
```

### Validation des webhooks

Les webhooks sont validés avec la même méthode de signature.

## 📱 Codes USSD

### Orange Money

```
#150# → Transfert d'argent → 657317490 → Montant → Code PIN
```

### MTN Mobile Money

```
#126# → Transfert d'argent → 683833646 → Montant → Code PIN
```

## 🐛 Logs et debugging

### Logs des webhooks

Tous les webhooks sont enregistrés dans:

```
logs/webhook_YYYY-MM-DD.log
```

Format:

```
[2024-01-15 10:30:45] Webhook reçu - Données brutes:
{
  "reference": "TP_123_1234567890",
  "status": "SUCCESS",
  "transaction": { "pk": "abc123" }
}
--------------------------------------------------------------------------------
```

### Logs d'erreurs

Les erreurs PHP sont enregistrées dans:

```
logs/webhook_errors.log
```

## ⚙️ Configuration avancée

### Montants et limites

Modifier dans `includes/config.php`:

```php
'payment' => [
    'min_deposit' => 5000,  // Minimum en FCFA
    'currency' => 'XAF'
]
```

### Délai de vérification

Modifier dans `views/payment_instructions.php`:

```javascript
const checkInterval = 10000; // 10 secondes (10000ms)
```

## 📞 Support

### MeSomb

- Site web: https://mesomb.hachther.com
- Documentation: https://mesomb.hachther.com/api/doc/
- Support: support@hachther.com

### TrustPick

- Webhook URL: `https://votre-domaine.com/api/payment-webhook.php`
- Logs: Vérifier le dossier `logs/`

## ✅ Checklist de déploiement

- [ ] Créer les tables avec `add_payment_tables.sql`
- [ ] Vérifier les credentials MeSomb dans `config.php`
- [ ] Configurer l'URL du webhook dans le dashboard MeSomb
- [ ] Tester un dépôt en sandbox (si disponible)
- [ ] Vérifier que le dossier `logs/` est accessible en écriture (chmod 755)
- [ ] Tester le flux complet: dépôt → instructions → webhook → crédit
- [ ] Vérifier la validation des tâches quotidiennes
- [ ] Tester les notifications

## 🎯 Fonctionnalités

✅ Dépôt via Orange Money  
✅ Dépôt via MTN Mobile Money  
✅ Instructions USSD claires  
✅ Vérification automatique du statut  
✅ Webhooks MeSomb  
✅ Validation des tâches quotidiennes  
✅ Notifications utilisateur  
✅ Historique des transactions  
✅ Copie rapide des codes USSD  
✅ Montants rapides (5k, 10k, 20k, 50k)  
✅ Validation des numéros de téléphone  
✅ Logs détaillés

## 📈 Prochaines étapes

- [ ] Ajouter le mode sandbox pour les tests
- [ ] Implémenter les retraits Mobile Money
- [ ] Ajouter Wave comme méthode de paiement
- [ ] Dashboard admin pour les paiements
- [ ] Rapports de paiements
- [ ] Remboursements automatiques

## 📄 Licence

Intégration développée pour TrustPick V2.
