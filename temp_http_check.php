<?php
$urls = [
    'http://localhost/TrustPick/',
    'http://localhost/TrustPick/public/index.php?page=catalog',
    'http://localhost/TrustPick/public/index.php?page=product&id=1',
    'http://localhost/TrustPick/public/index.php?page=company&id=1',
    'http://localhost/TrustPick/public/index.php?page=login',
    'http://localhost/TrustPick/public/index.php?page=register',
];
$out = __DIR__ . '/temp_http_check.txt';
file_put_contents($out, "");
foreach ($urls as $u) {
    $ch = curl_init($u);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $content = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($err) {
        file_put_contents($out, "$u\tERROR\t$err\n", FILE_APPEND);
    } else {
        $count = preg_match_all('/<article[^>]*class=\"card\b/', $content, $m);
        $len = strlen($content);
        file_put_contents($out, "$u\t$code\tArticles:$count\tLength:$len\n", FILE_APPEND);
    }
}
echo "DONE\n";
