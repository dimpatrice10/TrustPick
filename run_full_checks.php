<?php
require __DIR__ . '/includes/db.php';
$base = 'http://localhost/TrustPick/';
$urls = [
    $base,
    $base . 'public/index.php?page=catalog',
    $base . 'public/index.php?page=login',
    $base . 'public/index.php?page=register',
    $base . 'public/index.php?page=user_dashboard',
    $base . 'public/index.php?page=wallet',
    $base . 'public/index.php?page=company_dashboard',
    $base . 'public/index.php?page=admin_dashboard'
];

// add some product pages
try {
    $prods = $pdo->query('SELECT id FROM products ORDER BY id LIMIT 8')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($prods as $pid)
        $urls[] = $base . 'public/index.php?page=product&id=' . intval($pid);
} catch (Exception $e) {
}

// add some company pages
try {
    $comps = $pdo->query('SELECT id FROM companies ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($comps as $cid)
        $urls[] = $base . 'public/index.php?page=company&id=' . intval($cid);
} catch (Exception $e) {
}

$out = __DIR__ . '/temp_full_check.txt';
file_put_contents($out, "");
foreach ($urls as $u) {
    $ch = curl_init($u);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $content = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($err) {
        file_put_contents($out, "$u\tERROR\t$err\n", FILE_APPEND);
        continue;
    }
    $warnings = 0;
    preg_match_all('/Warning:/i', $content, $m);
    $warnings = count($m[0]);
    $fatals = 0;
    preg_match_all('/Fatal error:/i', $content, $m2);
    $fatals = count($m2[0]);
    $articles = 0;
    preg_match_all('/<article[^>]*class=\"card\b/i', $content, $m3);
    $articles = count($m3[0]);
    $len = strlen($content);
    file_put_contents($out, "$u\t$code\tWarnings:$warnings\tFatals:$fatals\tArticles:$articles\tLen:$len\n", FILE_APPEND);
}
echo "DONE\n";
