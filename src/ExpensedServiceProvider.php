<?php

namespace AbbyJanke\Expensed;

use Illuminate\Support\ServiceProvider;

class ExpensedServiceProvider extends ServiceProvider
{

    /**
     * Where the route file lives, both inside the package and in the app (if overwritten).
     *
     * @var string
     */
    public $routeFilePath = '/routes/backpack/expensed.php';

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupRoutes();
        $this->loadCommands();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'expensed');
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'expensed');

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__.'/config/backpack/expensed.php',
            'backpack.expensed'
        );

        // publish config file
        $this->publishes([__DIR__.'/config' => config_path()], 'config');
    }

    public function setupRoutes()
    {
        $routeFilePathInUse = __DIR__.$this->routeFilePath;
        if (file_exists(base_path().$this->routeFilePath)) {
            $routeFilePathInUse = base_path().$this->routeFilePath;
        }

        $this->loadRoutesFrom($routeFilePathInUse);
    }

    private function loadCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \AbbyJanke\Expensed\App\Console\Commands\CleanupCurrency::class,
                \AbbyJanke\Expensed\App\Console\Commands\InstallCurrency::class,
                \AbbyJanke\Expensed\App\Console\Commands\UpdateCurrency::class
            ]);
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        require_once __DIR__.'/helpers.php';
    }
}
