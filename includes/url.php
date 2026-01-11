<?php
/**
 * URL Helper — Génère des URLs absolues correctes
 */

define('BASE_URL', '/TrustPick/');
define('BASE_PATH', __DIR__ . '/..');

/**
 * Génère une URL absolue
 */
function url($path = '')
{
    return BASE_URL . ltrim($path, '/');
}

/**
 * Redirige vers une URL
 */
function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Chemin absolu du projet
 */
function base_path($path = '')
{
    return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
}
