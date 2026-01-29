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
        $group->get('/users', \App\Application\Actions\Admin\ListUsersAction::class);
        $group->post('/users/{id}/role', \App\Application\Actions\Admin\UpdateUserRoleAction::class);

        $group->get('/cms', \App\Application\Actions\Admin\Cms\ListPagesAction::class);
        $group->map(['GET', 'POST'], '/cms/create', \App\Application\Actions\Admin\Cms\CreatePageAction::class);
        $group->get('/cms/delete/{id}', \App\Application\Actions\Admin\Cms\DeletePageAction::class);
        
        // Builder
        $group->get('/cms/builder/{id}', \App\Application\Actions\Admin\Cms\CmsBuilderAction::class);
        $group->post('/cms/builder/{id}/save', \App\Application\Actions\Admin\Cms\SaveBuilderAction::class);
        $group->post('/cms/{id}/publish', \App\Application\Actions\Admin\Cms\PublishPageAction::class);
        $group->post('/cms/{id}/unpublish', \App\Application\Actions\Admin\Cms\UnpublishPageAction::class);
        $group->post('/cms/{id}/set-homepage', \App\Application\Actions\Admin\Cms\SetHomepageAction::class);
        $group->post('/cms/upload', \App\Application\Actions\Admin\Cms\CmsUploadAction::class);
        
        // API Keys
        $group->get('/api-keys', \App\Application\Actions\Admin\ApiKeys\ListApiKeysAction::class);
        $group->get('/api-keys/create', \App\Application\Actions\Admin\ApiKeys\SaveApiKeyAction::class);
        $group->map(['GET', 'POST'], '/api-keys/edit/{id}', \App\Application\Actions\Admin\ApiKeys\SaveApiKeyAction::class);
        $group->post('/api-keys/create', \App\Application\Actions\Admin\ApiKeys\SaveApiKeyAction::class);
        $group->get('/api-keys/delete/{id}', \App\Application\Actions\Admin\ApiKeys\DeleteApiKeyAction::class);

        // Book Search
        $group->get('/books/search', \App\Application\Actions\Search\BookSearchPageAction::class);
    })->add(new \App\Application\Middleware\RoleMiddleware('admin'));

    // Bibliophile routes
    $app->group('/bibliofilo', function ($group) {
        $group->get('/dashboard', \App\Application\Actions\Biblio\DashboardAction::class);
        
        // Book Search
        $group->get('/books/search', \App\Application\Actions\Search\BookSearchPageAction::class);
    })->add(new \App\Application\Middleware\RoleMiddleware('bibliophile'));

    // App routes (protected for users)
    $app->group('/app', function ($group) {
        $group->get('/dashboard', \App\Application\Actions\App\DashboardAction::class);
    })->add(new \App\Application\Middleware\RoleMiddleware(['user', 'admin', 'bibliophile']));

    // Test endpoint
    $app->get('/api/tests/backend', \App\Application\Actions\Test\RunTestsAction::class);

    // Search API endpoint
    $app->post('/api/search/books', \App\Application\Actions\Search\SearchBooksAction::class);

    // Home page
    $app->get('/', HomeAction::class);

    // Public CMS pages
    $app->get('/p/{slug}', \App\Application\Actions\Cms\PublicPageAction::class);
};
