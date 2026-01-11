<?php
$base = 'http://localhost/TrustPick/public/index.php?page=catalog';
$cases = [
    [],
    ['q' => 'Webcam'],
    ['price' => 'lt50'],
    ['price' => '50-100'],
    ['min_rating' => '4.0'],
    ['in_stock' => '1'],
    ['eco' => '1'],
    ['sort' => 'recent'],
    ['p' => 2],
    ['q' => 'Hub', 'price' => 'lt50', 'eco' => '1']
];
foreach ($cases as $c) {
    $url = $base . '&' . http_build_query($c);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $content = curl_exec($ch);
    curl_close($ch);
    $count = preg_match_all('/<article[^>]*class="card\b/', $content, $m);
    echo $url . "\tArticles:" . $count . "\n";
}
