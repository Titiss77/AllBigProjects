<?php
declare(strict_types=1);

// Force l'heure française pour tout le projet
date_default_timezone_set('Europe/Paris');

function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

loadEnv(__DIR__ . '/.env');
require_once __DIR__ . '/auth.php';
startSecureSession();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Cache-Control: no-store, private');
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') || (($_SERVER['SERVER_PORT']??'')==='443')) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

if (isset($_GET['auth']) || currentUser() === null) {
    require_once __DIR__ . '/Controllers/AuthController.php';
    (new AuthController())->handle();
    exit;
}

require_once __DIR__ . '/Controllers/CalendarController.php';

$app = new CalendarController();
$app->index();
