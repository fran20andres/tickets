<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

require __DIR__ . '/../app/Config/database.php';
require __DIR__ . '/../app/Config/ticketRoutes.php';

$app->run();
