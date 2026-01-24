<?php

declare(strict_types=1);

use App\Application\Actions\HomeAction;
use App\Application\Actions\HealthAction;
use Slim\App;

return function (App $app) {
    
    // Health check
    $app->get('/health', HealthAction::class);

    // Home page
    $app->get('/', HomeAction::class);

};
