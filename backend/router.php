<?php
// Router pour le serveur PHP intégré
// Utilisation: php -S localhost:8000 router.php

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Rerouter toutes les requêtes /api/* vers api/index.php
if (strpos($url, '/api') === 0) {
    $_GET['path'] = substr($url, 1); // Enlever le premier /
    require __DIR__ . '/api/index.php';
    return true;
}

// Servir les fichiers statiques normalement
if (file_exists(__DIR__ . $url)) {
    return false;
}

// Rerouter le reste vers api/index.php
$_GET['path'] = substr($url, 1);
require __DIR__ . '/api/index.php';
return true;
