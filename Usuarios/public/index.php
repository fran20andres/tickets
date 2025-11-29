<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use App\Middleware\CorsMiddleware;

$app = AppFactory::create();

// 🔥 Debe ir PRIMERO
$app->add(new CorsMiddleware());

// Body parser
$app->addBodyParsingMiddleware();

// Routing
$app->addRoutingMiddleware();

// Base de datos
require __DIR__ . '/../app/Config/database.php';

// Rutas
$routes = require __DIR__ . '/../app/Config/userRoutes.php';
$routes($app);

$app->run();
