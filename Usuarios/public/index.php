<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

// Cargar base de datos
require __DIR__ . '/../app/Config/database.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// CORS
$app->add(function ($req, $handler) {
    $res = $handler->handle($req);
    return $res
        ->withHeader("Access-Control-Allow-Origin", "*")
        ->withHeader("Access-Control-Allow-Headers", "Content-Type, Authorization")
        ->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
});

// Rutas
$routes = require __DIR__ . '/../app/Config/userRoutes.php';
$routes($app);

$app->run();
