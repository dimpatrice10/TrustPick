# Configuration CRON pour TrustPick V2

## Installation des tâches CRON

### Sur Linux/Mac

Éditer le crontab:

```bash
crontab -e
```

Ajouter ces lignes:

```bash
# Génération automatique de produits (3 fois par jour: 8h, 14h, 20h)
0 8,14,20 * * * /usr/bin/php /var/www/html/TrustPick/cron/generate_products.php >> /var/www/html/TrustPick/cron/logs/cron.log 2>&1

# Notifications quotidiennes (2 fois par jour: 9h, 18h)
0 9,18 * * * /usr/bin/php /var/www/html/TrustPick/cron/daily_notifications.php >> /var/www/html/TrustPick/cron/logs/cron.log 2>&1

# Rappels de tâches (2 fois par jour: 10h, 16h)
0 10,16 * * * /usr/bin/php /var/www/html/TrustPick/cron/task_reminders.php >> /var/www/html/TrustPick/cron/logs/cron.log 2>&1
```

### Sur Windows (Planificateur de tâches)

1. Ouvrir le Planificateur de tâches Windows
2. Créer une nouvelle tâche
3. Déclencheur: Répéter aux heures spécifiées
4. Action: Démarrer un programme
   - Programme: `C:\xampp2\php\php.exe`
   - Arguments: `C:\xampp2\htdocs\TrustPick\cron\generate_products.php`

Répéter pour chaque script CRON.

### Alternative: Script PowerShell Windows

Créer `run_cron_tasks.ps1`:

```powershell
# Configuration
$phpPath = "C:\xampp2\php\php.exe"
$projectPath = "C:\xampp2\htdocs\TrustPick"

# Fonction pour exécuter un script PHP
function Run-PhpScript {
    param($scriptName)
    $scriptPath = Join-Path $projectPath "cron\$scriptName"
    & $phpPath $scriptPath
}

# Vérifier l'heure actuelle
$currentHour = (Get-Date).Hour

# Génération de produits: 8h, 14h, 20h
if ($currentHour -in @(8, 14, 20)) {
    Write-Host "Génération de produits..."
    Run-PhpScript "generate_products.php"
}

# Notifications: 9h, 18h
if ($currentHour -in @(9, 18)) {
    Write-Host "Génération de notifications..."
    Run-PhpScript "daily_notifications.php"
}

# Rappels de tâches: 10h, 16h
if ($currentHour -in @(10, 16)) {
    Write-Host "Envoi des rappels..."
    Run-PhpScript "task_reminders.php"
}
```

Puis planifier ce script PowerShell pour s'exécuter toutes les heures.

## Test Manuel

Pour tester les scripts CRON manuellement:

```bash
# Linux/Mac
php /path/to/TrustPick/cron/generate_products.php

# Windows
C:\xampp2\php\php.exe C:\xampp2\htdocs\TrustPick\cron\generate_products.php
```

## Vérification des Logs

Les logs sont créés dans:

```
cron/logs/
├── products_generation.log
├── notifications.log
├── task_reminders.log
└── cron.log
```

Consulter les logs:

```bash
# Linux/Mac
tail -f cron/logs/products_generation.log

# Windows
Get-Content cron\logs\products_generation.log -Tail 50 -Wait
```

## Désactivation

Pour désactiver temporairement les CRON:

### Linux/Mac

```bash
crontab -e
# Commenter les lignes avec #
```

### Windows

Désactiver la tâche dans le Planificateur de tâches

## Fréquences Recommandées

| Tâche               | Fréquence | Heures       | Raison                      |
| ------------------- | --------- | ------------ | --------------------------- |
| Génération produits | 3x/jour   | 8h, 14h, 20h | Nouveaux produits réguliers |
| Notifications       | 2x/jour   | 9h, 18h      | Engagement matin/soir       |
| Rappels tâches      | 2x/jour   | 10h, 16h     | Maximiser les completions   |

## Personnalisation

Pour modifier les fréquences, éditer le fichier `system_settings`:

```sql
UPDATE system_settings
SET setting_value = '5'
WHERE setting_key = 'products_generation_frequency';

UPDATE system_settings
SET setting_value = '3'
WHERE setting_key = 'daily_notifications_count';
```

## Monitoring

Vérifier que les CRON s'exécutent correctement:

```sql
-- Produits générés aujourd'hui
SELECT COUNT(*) as today_products
FROM products
WHERE is_auto_generated = TRUE
AND DATE(created_at) = CURDATE();

-- Notifications envoyées aujourd'hui
SELECT COUNT(*) as today_notifications
FROM notifications
WHERE DATE(created_at) = CURDATE();

-- Dernière exécution
SELECT MAX(created_at) as last_product_generation
FROM products
WHERE is_auto_generated = TRUE;
```

## Alertes

Pour être notifié en cas d'échec:

1. Configurer un email dans les scripts CRON
2. Utiliser un service de monitoring (ex: Cronitor, Healthchecks.io)
3. Vérifier les logs quotidiennement

## Troubleshooting

### Le CRON ne s'exécute pas

1. Vérifier les permissions:

   ```bash
   chmod +x cron/*.php
   ```

2. Vérifier le chemin PHP:

   ```bash
   which php  # Linux/Mac
   where php  # Windows
   ```

3. Vérifier les logs d'erreur système:

   ```bash
   # Linux
   grep CRON /var/log/syslog

   # Windows
   # Consulter l'Observateur d'événements
   ```

### Les scripts s'exécutent mais échouent

1. Vérifier les logs spécifiques dans `cron/logs/`
2. Tester manuellement les scripts
3. Vérifier la connexion à la base de données
4. Vérifier les permissions des fichiers de logs

### Performance

Si les scripts prennent trop de temps:

1. Réduire le nombre de produits générés par exécution
2. Optimiser les requêtes SQL
3. Ajouter des index sur les tables fréquemment utilisées
4. Envisager une file d'attente (Redis/RabbitMQ)
