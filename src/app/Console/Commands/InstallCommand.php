<?php

namespace AbbyJanke\Expensed\App\Console\Commands;

use AbbyJanke\Expensed\App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'backpack:install-expensed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Backpack Expensed.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bar = $this->output->createProgressBar(5);

        // using permission manager?
        $usePermissionManager = $this->confirm('Use Backpack/PermissionManager to restrict access?', 'yes');
        if($usePermissionManager) {
            $this->info("\n".'Installing Backpack/PermissionManager');
            $command = 'composer require backpack/permissionmanager --prefer-source';
            shell_exec($command);
        } else {
            $this->comment('Skipping Backpack/PermissionManager');
        }
        $bar->advance();

        // Add new Database tables
        Artisan::call('migrate');
        $this->info("\n".'New Tables Migrated.');
        $bar->advance();

        // Check to see if the API Token is set
        $currencies = Currency::all()->count();
        if($currencies == 0 && $this->confirm('Install/Update currency rates using OpenExchange.com?', 'yes')) {
            $this->info("\n".'Installing/Updating Currencies');
            Artisan::call('currency:update'); // Add the currency
        } else {
            $this->error('Currencies not updated, you will need to manually update them or add an API Token and run "php artisan currency:install"');
        }
        $bar->advance();

        // double check to make sure they want default categories
        if($this->confirm('Install Default Categories?')) {
            $this->info("\n".'Adding Default Categories');
            Artisan::call('db:seed', ['class' => 'AbbyJanke\Expensed\Database\Seeds\CategoriesTableSeeder']);
        } else {
            $this->info("\n".'Skipping Default Categories');
        }
        $this->advance();

        // double check to make sure they want default categories
        if($usePermissionManager && $this->confirm('Install Recommended Permission Names?')) {
            $this->info("\n".'Adding Recommended Permission Names');
            Artisan::call('db:seed', ['class' => 'AbbyJanke\Expensed\Database\Seeds\PermissionsTableSeeder']);
        } else {
            $this->info("\n".'Skipping Default Categories');
        }

        $bar->finish();
        return;
    }

}
