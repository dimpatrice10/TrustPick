<?php
// ============================================================
// TRUSTPICK V2 - DÉCONNEXION
// ============================================================

require_once '../includes/session.php';

// Détruire la session
SessionManager::destroy();

// Rediriger vers la page de connexion
header("Location: login.php?logout=success");
exit;
