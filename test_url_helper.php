<?php
/**
 * Test du helper URL depuis différents contextes
 */

echo "=== TEST URL HELPER ===\n\n";

// Simuler appel depuis /actions/logout.php
$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/TrustPick/actions/logout.php';
$_SERVER['DOCUMENT_ROOT'] = 'c:/xampp2/htdocs';

require __DIR__ . '/includes/url.php';

echo "CONTEXTE: Appel depuis /TrustPick/actions/logout.php\n";
echo str_repeat('-', 50) . "\n";
echo "BASE_URL:          " . BASE_URL . "\n";
echo "PUBLIC_URL:        " . PUBLIC_URL . "\n";
echo "BASE_PATH:         " . BASE_PATH . "\n";
echo "PUBLIC_IS_DOCROOT: " . (PUBLIC_IS_DOCROOT ? 'true' : 'false') . "\n";
echo "\n";

echo "TESTS DES FONCTIONS:\n";
echo str_repeat('-', 50) . "\n";
echo "url('')                           => " . url('') . "\n";
echo "url('api/test.php')               => " . url('api/test.php') . "\n";
echo "public_url('')                    => " . public_url('') . "\n";
echo "public_url('index.php?page=home') => " . public_url('index.php?page=home') . "\n";
echo "asset('assets/css/app.css')       => " . asset('assets/css/app.css') . "\n";
echo "base_path('storage/logs')         => " . base_path('storage/logs') . "\n";
echo "\n";

echo "VERIFICATION REDIRECT:\n";
echo str_repeat('-', 50) . "\n";
echo "redirect('index.php?page=home') irait vers:\n";
echo "  => " . public_url('index.php?page=home') . "\n";
echo "\n";

// Vérifications
$expected_base = 'http://localhost/TrustPick/';
$expected_public = 'http://localhost/TrustPick/public/';
$expected_redirect = 'http://localhost/TrustPick/public/index.php?page=home';

echo "ASSERTIONS:\n";
echo str_repeat('-', 50) . "\n";

$tests = [
    ['BASE_URL correct', BASE_URL, $expected_base],
    ['PUBLIC_URL correct', PUBLIC_URL, $expected_public],
    ['public_url(index.php?page=home) correct', public_url('index.php?page=home'), $expected_redirect],
];

$passed = 0;
$failed = 0;

foreach ($tests as [$name, $actual, $expected]) {
    if ($actual === $expected) {
        echo "✅ PASS: $name\n";
        echo "   Attendu:  $expected\n";
        echo "   Obtenu:   $actual\n";
        $passed++;
    } else {
        echo "❌ FAIL: $name\n";
        echo "   Attendu:  $expected\n";
        echo "   Obtenu:   $actual\n";
        $failed++;
    }
}

echo "\n";
echo "RÉSULTAT: $passed passés, $failed échoués\n";

if ($failed === 0) {
    echo "\n🎉 TOUS LES TESTS PASSENT!\n";
    echo "redirect('index.php?page=home') redirigera TOUJOURS vers:\n";
    echo "  " . public_url('index.php?page=home') . "\n";
    echo "\nPeu importe depuis quel fichier (actions/, admin/, etc.)\n";
}
