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
        $this->loadViews();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'expensed');

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__.'/config/backpack/expensed.php',
            'backpack.expensed'
        );

        $this->publishAssets();
    }

    /**
     * Assign Routes For Expense
     */
    private function setupRoutes()
    {
        $routeFilePathInUse = __DIR__.$this->routeFilePath;
        if (file_exists(base_path().$this->routeFilePath)) {
            $routeFilePathInUse = base_path().$this->routeFilePath;
        }

        $this->loadRoutesFrom($routeFilePathInUse);
    }

    /**
     * Load Necessary Commands
     */
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
     * Load Views
     */
    private function loadViews()
    {
        $customFolder = resource_path('views/vendor/expensed');

        // - first the published/overwritten views (in case they have any changes)
        if (file_exists($customFolder)) {
            $this->loadViewsFrom($customFolder, 'expensed');
        }

        // - then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'expensed');
    }

    /**
     * Publish Assets
     */
    private function publishAssets()
    {
        $this->publishes([
            __DIR__.'/resources/lang' => resource_path('lang/vendor/expensed'),
            __DIR__.'/resources/views' => resource_path('views/vendor/expensed'),
            __DIR__.__DIR__.'/config/backpack/expensed.php' => config_path('backpack/expensed.php'),
        ]);
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
