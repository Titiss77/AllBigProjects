<?php

// Lecture sécurisée du fichier .env
$envPath = __DIR__.'/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (0 === strpos(trim($line), '#')) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Récupération stricte depuis l'environnement sans fallback en clair
$dbHost = trim(getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? '');
$dbUser = trim(getenv('DB_USER') ?: $_ENV['DB_USER'] ?? '');
$dbPass = trim(getenv('DB_PASS') ?: $_ENV['DB_PASS'] ?? '');
$dbName = trim(getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? '');

// Vérification de sécurité : si les variables manquent, on coupe l'exécution
if (empty($dbHost) || empty($dbUser) || empty($dbName)) {
    exit('Erreur de configuration critique.');
}

define('DB_HOST', $dbHost);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_NAME', $dbName);
