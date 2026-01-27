<?php

declare(strict_types=1);

use App\Application\Actions\HomeAction;
use App\Application\Actions\HealthAction;
use Slim\App;

return function (App $app) {
    
    // Health check
    $app->get('/health', HealthAction::class);


    // Auth routes
    $app->map(['GET', 'POST'], '/register', \App\Application\Actions\Auth\RegisterAction::class);
    $app->map(['GET', 'POST'], '/login', \App\Application\Actions\Auth\LoginAction::class);
    $app->get('/logout', \App\Application\Actions\Auth\LogoutAction::class);

    // Admin routes
    $app->group('/admin', function ($group) {
        $group->get('/dashboard', \App\Application\Actions\Admin\DashboardAction::class);
    })->add(new \App\Application\Middleware\RoleMiddleware('admin'));

    // Bibliophile routes
    $app->group('/bibliofilo', function ($group) {
        $group->get('/dashboard', \App\Application\Actions\Biblio\DashboardAction::class);
    })->add(new \App\Application\Middleware\RoleMiddleware('bibliophile'));

    // App routes (protected for users)
    $app->group('/app', function ($group) {
        $group->get('/dashboard', \App\Application\Actions\App\DashboardAction::class);
    })->add(new \App\Application\Middleware\RoleMiddleware(['user', 'admin', 'bibliophile']));

    // Home page
    $app->get('/', HomeAction::class);
};
