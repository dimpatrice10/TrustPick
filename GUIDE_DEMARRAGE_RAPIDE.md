# 🚀 Guide de Démarrage Rapide - Paiement Mobile Money

## ⚡ Installation en 5 minutes

### Étape 1: Créer la table de paiements

Ouvrez phpMyAdmin et exécutez ce script SQL:

```bash
# Via terminal
mysql -u root -p trustpick_v2 < db/add_payment_tables.sql

# Ou via phpMyAdmin
# Sélectionnez la base trustpick_v2
# Onglet "SQL"
# Collez le contenu de db/add_payment_tables.sql
# Cliquez sur "Exécuter"
```

### Étape 2: Vérifier la configuration

Le fichier `includes/config.php` contient déjà vos identifiants MeSomb:

```php
'mesomb' => [
    'application_key' => '18bfc8002ab9555601c82fcb07e2817e221dad36',
    'access_key' => '5c63e664-2993-4f11-9cea-54347347a307',
    'secret_key' => 'd68f6eb3-9a8b-4315-8228-587d6f25c2a4',
    ...
]
```

✅ Pas de modification nécessaire !

### Étape 3: Configurer le webhook MeSomb

1. Connectez-vous sur https://mesomb.hachther.com
2. Allez dans **Paramètres → Webhooks**
3. Ajoutez l'URL de votre webhook:
   ```
   https://votre-domaine.com/api/payment-webhook.php
   ```
4. Copiez le secret généré et mettez-le dans `config.php`:
   ```php
   'webhook_secret' => 'le_secret_de_mesomb'
   ```

### Étape 4: Créer le dossier logs

```bash
# Windows
mkdir logs

# Linux/Mac
mkdir logs
chmod 755 logs
```

### Étape 5: Tester !

1. Connectez-vous sur TrustPick
2. Allez dans **Portefeuille**
3. Cliquez sur **"Déposer des fonds"**
4. Sélectionnez **Orange Money** ou **MTN Mobile Money**
5. Entrez votre numéro (ex: 657317490)
6. Entrez le montant (minimum 5000 FCFA)
7. Cliquez sur **"Confirmer le dépôt"**

## 📱 Comment effectuer le paiement

### Avec Orange Money

1. Sur votre téléphone Orange, composez: **#150#**
2. Sélectionnez **"Transfert d'argent"**
3. Entrez le numéro: **657317490**
4. Entrez le montant affiché sur TrustPick
5. Confirmez avec votre code PIN

### Avec MTN Mobile Money

1. Sur votre téléphone MTN, composez: **#126#**
2. Sélectionnez **"Transfert d'argent"**
3. Entrez le numéro: **683833646**
4. Entrez le montant affiché sur TrustPick
5. Confirmez avec votre code PIN

## ✅ Que se passe-t-il après ?

1. **Vérification automatique**: La page vérifie le statut toutes les 10 secondes
2. **Notification MeSomb**: Quand le paiement est confirmé, MeSomb envoie un webhook
3. **Crédit automatique**: Votre solde TrustPick est crédité instantanément
4. **Tâche validée**: La tâche quotidienne "Dépôt 5000 FCFA" est complétée
5. **Notification**: Vous recevez une notification de confirmation

## 🐛 Dépannage

### Le paiement ne se confirme pas

1. Vérifiez que vous avez bien transféré vers le bon numéro:
   - Orange: **657317490**
   - MTN: **683833646**

2. Vérifiez que le montant est exact

3. Consultez les logs du webhook:

   ```
   logs/webhook_YYYY-MM-DD.log
   ```

4. Vérifiez que l'URL du webhook est accessible publiquement

### Erreur "Référence manquante"

Videz votre cache navigateur et réessayez.

### Le webhook ne reçoit rien

1. Vérifiez que votre site est accessible depuis Internet (pas localhost)
2. Vérifiez l'URL du webhook dans le dashboard MeSomb
3. Testez manuellement l'URL du webhook

### Erreur de signature

Vérifiez que le `webhook_secret` dans `config.php` correspond au secret MeSomb.

## 📊 Vérifier que tout fonctionne

### 1. Vérifier la base de données

```sql
-- Voir les transactions de paiement
SELECT * FROM payment_transactions ORDER BY created_at DESC LIMIT 5;

-- Voir l'historique des transactions
SELECT * FROM transactions WHERE type = 'deposit' ORDER BY created_at DESC LIMIT 5;
```

### 2. Vérifier les logs

```bash
# Windows
type logs\webhook_2024-01-15.log

# Linux/Mac
cat logs/webhook_2024-01-15.log
```

### 3. Vérifier le solde utilisateur

```sql
SELECT id, username, balance FROM users WHERE id = VOTRE_USER_ID;
```

## 🎯 Points de vérification

- [ ] Table `payment_transactions` créée
- [ ] Credentials MeSomb dans `config.php`
- [ ] Dossier `logs/` créé et accessible en écriture
- [ ] Webhook configuré dans MeSomb
- [ ] Site accessible depuis Internet (pour le webhook)
- [ ] Premier test de dépôt effectué

## 💡 Conseils

### Pour les tests

- Commencez par un petit montant (5000 FCFA)
- Utilisez votre propre numéro Mobile Money
- Surveillez les logs en temps réel

### Pour la production

- Activez HTTPS sur votre site
- Sauvegardez régulièrement la table `payment_transactions`
- Surveillez les logs quotidiennement
- Configurez des alertes email pour les erreurs

## 📞 Comptes de réception

Les dépôts sont reçus sur ces comptes:

- **Orange Money**: 657317490
- **MTN Mobile Money**: 683833646

⚠️ **Important**: Vérifiez régulièrement ces comptes et transférez les fonds vers un compte principal pour la sécurité.

## 🔐 Sécurité

- ✅ Toutes les requêtes MeSomb sont signées avec HMAC-SHA256
- ✅ Les webhooks sont validés par signature
- ✅ Les numéros de téléphone sont validés côté serveur
- ✅ Les montants minimum sont appliqués
- ✅ Les transactions sont enregistrées avec horodatage

## 📈 Statistiques disponibles

Vous pouvez consulter:

- Nombre de dépôts par jour
- Montant total des dépôts
- Opérateur le plus utilisé (Orange vs MTN)
- Temps moyen de confirmation

```sql
-- Statistiques de paiements
SELECT
    DATE(created_at) as date,
    channel,
    COUNT(*) as nb_paiements,
    SUM(amount) as total,
    AVG(amount) as moyenne
FROM payment_transactions
WHERE status = 'success'
GROUP BY DATE(created_at), channel
ORDER BY date DESC;
```

## 🎉 C'est tout !

Votre intégration MeSomb est maintenant complète et opérationnelle.

Pour toute question, consultez le fichier `README_MESOMB.md` pour plus de détails.
