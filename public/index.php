<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$allowed = [
    'home','catalog','product','company','login','register','user_dashboard','company_dashboard','admin_dashboard','wallet'
];
if (!in_array($page, $allowed)) {
    http_response_code(404);
    $page = '404';
}
require __DIR__ . '/../views/layouts/header.php';
if ($page === '404') {
    require __DIR__ . '/../views/404.php';
} else {
    require __DIR__ . "/../views/{$page}.php";
}
require __DIR__ . '/../views/layouts/footer.php';
