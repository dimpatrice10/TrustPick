<?php
/**
 * Helpers Globaux TrustPick V2
 * Fonctions utilitaires utilisées dans toute l'application
 */

/**
 * Formater un montant en FCFA
 * @param float|int $amount Montant à formater
 * @param int $decimals Nombre de décimales (défaut: 0)
 * @return string Montant formaté (ex: "450 000 FCFA")
 */
function formatFCFA($amount, $decimals = 0)
{
    return number_format((float) $amount, $decimals, ',', ' ') . ' FCFA';
}

/**
 * Formater une date relative (il y a X temps)
 * @param string $datetime Date à formater
 * @return string Date relative
 */
function formatRelativeTime($datetime)
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60)
        return 'À l\'instant';
    if ($diff < 3600)
        return floor($diff / 60) . ' min';
    if ($diff < 86400)
        return floor($diff / 3600) . ' h';
    if ($diff < 604800)
        return floor($diff / 86400) . ' j';
    if ($diff < 2592000)
        return floor($diff / 604800) . ' sem';
    if ($diff < 31536000)
        return floor($diff / 2592000) . ' mois';
    return floor($diff / 31536000) . ' an' . (floor($diff / 31536000) > 1 ? 's' : '');
}

/**
 * Démarrer session de manière sécurisée
 */
function initSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Ajouter une notification toast
 * @param string $type Type: success, error, warning, info
 * @param string $message Message à afficher
 */
function addToast($type, $message)
{
    initSession();
    if (!isset($_SESSION['toasts'])) {
        $_SESSION['toasts'] = [];
    }
    $_SESSION['toasts'][] = [
        'type' => $type,
        'message' => $message,
        'time' => time()
    ];
}

/**
 * Récupérer et nettoyer les toasts
 * @return array Toasts à afficher
 */
function getToasts()
{
    initSession();
    if (!isset($_SESSION['toasts'])) {
        return [];
    }
    $toasts = $_SESSION['toasts'];
    unset($_SESSION['toasts']);
    return $toasts;
}

/**
 * Obtenir le rôle de l'utilisateur connecté
 * @return string|null Rôle ou null si non connecté
 */
function getCurrentUserRole()
{
    initSession();
    return $_SESSION['user_role'] ?? null;
}

/**
 * Vérifier si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn()
{
    initSession();
    return !empty($_SESSION['user_id']);
}

/**
 * Obtenir les informations de l'utilisateur connecté
 * @return array|null
 */
function getCurrentUser()
{
    initSession();
    if (!isLoggedIn())
        return null;

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'cau' => $_SESSION['user_cau'] ?? null
    ];
}

/**
 * Générer un badge HTML pour un rôle
 * @param string $role
 * @return string HTML
 */
function roleBadge($role)
{
    $badges = [
        'super_admin' => '<span class="badge bg-danger">Super Admin</span>',
        'admin_entreprise' => '<span class="badge bg-primary">Admin Entreprise</span>',
        'user' => '<span class="badge bg-success">Utilisateur</span>'
    ];
    return $badges[$role] ?? '<span class="badge bg-secondary">Inconnu</span>';
}

/**
 * Générer un badge HTML pour un statut
 * @param string $status
 * @return string HTML
 */
function statusBadge($status)
{
    $badges = [
        'pending' => '<span class="badge bg-warning">En attente</span>',
        'approved' => '<span class="badge bg-info">Approuvé</span>',
        'completed' => '<span class="badge bg-success">Complété</span>',
        'rejected' => '<span class="badge bg-danger">Rejeté</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Nettoyer et échapper une chaîne pour l'affichage HTML
 * @param string $string
 * @return string
 */
function clean($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Générer des étoiles de notation
 * @param float $rating Note de 0 à 5
 * @return string HTML
 */
function stars($rating)
{
    $html = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<span class="star filled">★</span>';
        } else {
            $html .= '<span class="star">☆</span>';
        }
    }
    $html .= '</span>';
    return $html;
}
