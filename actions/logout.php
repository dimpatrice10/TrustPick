<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/url.php';

session_unset();
session_destroy();
redirect('index.php?page=home');