<?php

namespace Chatify;

use Chatify\Console\InstallCommand;
use Chatify\Console\PublishCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ChatifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->bind('ChatifyMessenger', function () {
            return new \Chatify\ChatifyMessenger;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load Views, Migrations and Routes
        $this->loadViewsFrom(__DIR__ . '/views', 'Chatify');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PublishCommand::class,
            ]);
            $this->setPublishes();
        }

    }

    /**
     * Publishing the files that the user may override.
     *
     * @return void
     */
    protected function setPublishes()
    {
        // Load user's avatar folder from package's config
        $userAvatarFolder = json_decode(json_encode(include(__DIR__.'/config/chatify.php')))->user_avatar->folder;

        // Config
        $this->publishes([
            __DIR__ . '/config/chatify.php' => config_path('chatify.php')
        ], 'chatify-config');

        // Routes
        $this->publishes([
            __DIR__ . '/routes/web.php' => base_path('routes/chatify.php')
        ], 'chatify-routes');

        // Migrations
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ], 'chatify-migrations');

        // Models
        $isV8 = explode('.',app()->version())[0] >= 8;
        $this->publishes([
            __DIR__ . '/Models' => app_path($isV8 ? 'Models' : '')
        ], 'chatify-models');

        // Controllers
        $this->publishes([
            __DIR__ . '/Http/Controllers' => app_path('Http/Controllers/vendor/Chatify')
        ], 'chatify-controllers');

        // Views
        $this->publishes([
            __DIR__ . '/views' => resource_path('views/vendor/Chatify')
        ], 'chatify-views');

        // Assets
        $this->publishes([
            // CSS
            __DIR__ . '/assets/css' => public_path('css/chatify'),
            // JavaScript
            __DIR__ . '/assets/js' => public_path('js/chatify'),
            // Images
            __DIR__ . '/assets/imgs' => storage_path('app/public/' . $userAvatarFolder),
        ], 'chatify-assets');
    }

    /**
     * Group the routes and set up configurations to load them.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        Route::group($this->routesConfigurations(), function () {
            $this->loadRoutesFrom(config('chatify.routes.file', null) ?? __DIR__ . '/routes/web.php');
        });
    }

    /**
     * Routes configurations.
     *
     * @return array
     */
    private function routesConfigurations()
    {
        return [
            'prefix' => config('chatify.routes.prefix'),
            'namespace' =>  config('chatify.routes.namespace'),
            'middleware' => config('chatify.routes.middleware'),
        ];
    }
}
