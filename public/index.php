<?php

use Slim\Factory\AppFactory;
use App\Middlewares\CorsMiddleware;

define('LOG_FOLDER', __DIR__ . '/../logs');

require __DIR__ . '/../vendor/autoload.php';

session_start();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$bootEloquent = require __DIR__ . '/../config/database.php';
$bootEloquent();

$app = AppFactory::create();

$errors = require __DIR__ . '/../config/slim_errors.php';
$errors($app);

$app->add(new CorsMiddleware());

$routes = require __DIR__ . '/../src/Routes/web.php';
$routes($app);

$app->run();
