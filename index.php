<?php
// Rediriger vers public/index.php en préservant les paramètres GET
$query = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: public/index.php' . $query);
exit;
