<?php
/**
 * TrustPick V2 - Fonction utilitaire pour lire les variables d'environnement
 * Ce fichier peut être inclus partout sans risque de redéclaration.
 * Supporte: .env file, getenv(), $_ENV, $_SERVER
 */

if (!function_exists('tp_env')) {
    // Charger le fichier .env s'il existe (une seule fois)
    if (!isset($GLOBALS['_tp_env_loaded'])) {
        $GLOBALS['_tp_env_loaded'] = true;
        $GLOBALS['_tp_env_vars'] = [];
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#')
                    continue;
                if (strpos($line, '=') === false)
                    continue;
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                // Retirer les guillemets
                if (
                    (substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")
                ) {
                    $value = substr($value, 1, -1);
                }
                $GLOBALS['_tp_env_vars'][$key] = $value;
            }
        }
    }

    /**
     * Lire une variable d'environnement depuis toutes les sources possibles.
     * Compatible CLI, Apache, Docker, Render, InfinityFree (.env).
     *
     * @param string $name Nom de la variable
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    function tp_env(string $name, $default = null)
    {
        // 0. Fichier .env (priorité haute)
        if (isset($GLOBALS['_tp_env_vars'][$name]) && $GLOBALS['_tp_env_vars'][$name] !== '') {
            return $GLOBALS['_tp_env_vars'][$name];
        }
        // 1. getenv() - fonctionne en CLI et parfois en Apache
        $val = getenv($name);
        if ($val !== false && $val !== '') {
            return $val;
        }
        // 2. $_ENV - fonctionne quand variables_order contient 'E'
        if (isset($_ENV[$name]) && $_ENV[$name] !== '') {
            return $_ENV[$name];
        }
        // 3. $_SERVER - Apache passe souvent les env vars ici via PassEnv
        if (isset($_SERVER[$name]) && $_SERVER[$name] !== '') {
            return $_SERVER[$name];
        }
        // 4. Fallback
        return $default;
    }
}
