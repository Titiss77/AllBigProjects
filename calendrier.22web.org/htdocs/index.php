<?php
declare(strict_types=1);

// 1. Fonction pour parser le fichier .env
function loadEnv(string $path): void {
    if (!file_exists($path)) {
        return;
    }

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

// 2. Chargement du .env situé dans le même dossier que ce fichier index.php
loadEnv(__DIR__ . '/.env');

// 3. Appel du contrôleur
require_once __DIR__ . '/Controllers/CalendarController.php';

// 4. Lancement de l'application
$app = new CalendarController();
$app->index();