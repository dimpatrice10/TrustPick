<?php
session_start();
require __DIR__ . '/../includes/url.php';
session_unset();
session_destroy();
// redirect('index.php?page=home');
header('Location: ../public/index.php?page=home');
exit();
