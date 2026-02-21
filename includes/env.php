<?php
/**
 * TrustPick V2 - Fonction utilitaire pour lire les variables d'environnement
 * Ce fichier peut être inclus partout sans risque de redéclaration.
 */

if (!function_exists('tp_env')) {
    /**
     * Lire une variable d'environnement depuis toutes les sources possibles.
     * Compatible CLI, Apache, Docker, Render.
     *
     * @param string $name Nom de la variable
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    function tp_env(string $name, $default = null)
    {
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
