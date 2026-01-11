<?php
// Script CLI to perform login -> review -> withdraw using cookies
$base = 'http://localhost/TrustPick/';
$cookie = __DIR__ . '/tmp_cookies.txt';
if (file_exists($cookie))
    unlink($cookie);

function do_post($url, $data, $cookie)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $err, $res];
}

// 1) login
list($c, $e, $r) = do_post($base . 'actions/login.php', ['email' => 'test+bot@example.com', 'password' => 'TestPass123!'], $cookie);
echo "LOGIN: code=$c err=$e len=" . strlen($r) . "\n";

// 2) post review for product_id=1
list($c, $e, $r) = do_post($base . 'actions/review.php', ['product_id' => 1, 'rating' => 5, 'title' => 'Test review', 'body' => 'Automated test review'], $cookie);
echo "REVIEW: code=$c err=$e len=" . strlen($r) . "\n";

// 3) request withdraw of 5
list($c, $e, $r) = do_post($base . 'actions/withdraw.php', ['amount' => 5], $cookie);
echo "WITHDRAW: code=$c err=$e len=" . strlen($r) . "\n";

// 4) check DB for review and wallet balance
require __DIR__ . '/includes/db.php';
$rev = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE product_id = ?');
$rev->execute([1]);
echo "REVIEWS for product 1: " . $rev->fetchColumn() . "\n";
$bal = $pdo->prepare('SELECT balance FROM wallets WHERE user_id = ?');
$bal->execute([6]);
$b = $bal->fetchColumn();
echo "WALLET for user 6: " . ($b === false ? 'none' : $b) . "\n";
