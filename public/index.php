<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Build DI Container
$containerBuilder = new ContainerBuilder();

// Load dependencies
$dependencies = require __DIR__ . '/../config/dependencies.php';
$dependencies($containerBuilder);

// Build container
$container = $containerBuilder->build();

// Create App
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$displayErrorDetails = ($_ENV['APP_ENV'] ?? 'production') === 'development';
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);

// Register routes
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

$app->run();
