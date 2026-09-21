<?php

// Configuration sécurisée des cookies de session avant le démarrage
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']) {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Génération du token CSRF s'il n'existe pas
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'controllers/MetricController.php';

require_once 'controllers/AuthController.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$authController = new AuthController();

// Gestion des routes publiques
if ('login' === $action) {
    $authController->login();

    exit;
}
if ('register' === $action) {
    $authController->register();

    exit;
}
if ('logout' === $action) {
    $authController->logout();

    exit;
}

// Vérification stricte de l'authentification pour toutes les autres actions
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: index.php?action=login');

    exit;
}

// Gestion des routes protégées
$controller = new MetricController();

if ('save' === $action) {
    $controller->save();
} elseif ('export' === $action) {
    $controller->exportCSV();
} elseif ('import' === $action) { // ROUTE D'IMPORTATION
    $controller->importCSV();
} elseif ('delete' === $action) {
    $controller->delete();
} else {
    $controller->index();
}
