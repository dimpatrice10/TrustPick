<?php
$url = 'http://localhost/TrustPick/public/index.php?page=catalog';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$content = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
if ($err) {
    echo "ERROR: $err\n";
    exit(1);
}
file_put_contents(__DIR__ . '/temp_catalog.html', $content);
echo "SAVED\n";
