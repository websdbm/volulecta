<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        
        // Logger
        LoggerInterface::class => function (ContainerInterface $c) {
            $logger = new Logger('volulecta');
            $logFile = __DIR__ . '/../logs/app.log';
            $logLevel = ($_ENV['APP_ENV'] ?? 'production') === 'development' 
                ? Logger::DEBUG 
                : Logger::INFO;
            
            $logger->pushHandler(new StreamHandler($logFile, $logLevel));
            return $logger;
        },

        // Twig
        Twig::class => function (ContainerInterface $c) {
            $twig = Twig::create(__DIR__ . '/../src/Views', [
                'cache' => ($_ENV['APP_ENV'] ?? 'production') === 'production' 
                    ? __DIR__ . '/../cache/twig' 
                    : false,
                'auto_reload' => true,
                'debug' => ($_ENV['APP_ENV'] ?? 'production') === 'development',
            ]);

            // Add global variables
            $environment = $twig->getEnvironment();
            $environment->addGlobal('app_name', 'Volulecta');
            $environment->addGlobal('app_url', $_ENV['APP_URL'] ?? 'http://localhost:8080');

            return $twig;
        },

        // PDO Database Connection
        PDO::class => function (ContainerInterface $c) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'volulecta';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            return new PDO($dsn, $user, $pass, $options);
        },

        // Repositories
        \App\Domain\Repositories\UserRepositoryInterface::class => function (ContainerInterface $c) {
            return new \App\Infrastructure\Persistence\PdoUserRepository($c->get(PDO::class));
        },

        // Services
        \App\Application\Services\AuthService::class => function (ContainerInterface $c) {
            return new \App\Application\Services\AuthService($c->get(\App\Domain\Repositories\UserRepositoryInterface::class));
        },

        // Middleware
        \App\Application\Middleware\AuthMiddleware::class => function (ContainerInterface $c) {
            return new \App\Application\Middleware\AuthMiddleware();
        },

    ]);
};
