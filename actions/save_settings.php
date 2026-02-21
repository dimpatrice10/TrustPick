<?php
/**
 * TrustPick V2 - Action: Sauvegarder les paramètres système (Super Admin)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/settings.php';

// Vérifier rôle super_admin
SessionManager::requireRole('super_admin', 'index.php?page=login');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('index.php?page=superadmin_dashboard'));
}

// Liste des paramètres autorisés avec validation
$allowedSettings = [
    'min_deposit' => ['type' => 'int', 'min' => 1, 'label' => 'Dépôt minimum'],
    'min_withdrawal' => ['type' => 'int', 'min' => 1, 'label' => 'Retrait minimum'],
    'review_reward' => ['type' => 'int', 'min' => 0, 'label' => 'Récompense avis'],
    'referral_reward' => ['type' => 'int', 'min' => 0, 'label' => 'Récompense parrainage'],
    'daily_notifications_count' => ['type' => 'int', 'min' => 0, 'label' => 'Notifications/jour'],
    'products_generation_frequency' => ['type' => 'int', 'min' => 1, 'label' => 'Fréq. génération produits'],
];

$updates = [];
$errors = [];

foreach ($allowedSettings as $key => $rules) {
    if (isset($_POST[$key])) {
        $value = trim($_POST[$key]);

        if ($value === '') {
            continue; // Skip empty
        }

        if ($rules['type'] === 'int') {
            $intVal = intval($value);
            if ($intVal < $rules['min']) {
                $errors[] = "{$rules['label']} doit être au minimum {$rules['min']}";
                continue;
            }
            $updates[$key] = (string) $intVal;
        } else {
            $updates[$key] = $value;
        }
    }
}

if (!empty($errors)) {
    addToast('error', implode('<br>', $errors));
    redirect(url('index.php?page=superadmin_dashboard'));
}

if (empty($updates)) {
    addToast('info', 'Aucun paramètre modifié.');
    redirect(url('index.php?page=superadmin_dashboard'));
}

$success = Settings::setMany($updates);

if ($success) {
    addToast('success', count($updates) . ' paramètre(s) mis à jour avec succès.');
} else {
    addToast('error', 'Erreur lors de la mise à jour des paramètres.');
}

redirect(url('index.php?page=superadmin_dashboard'));
