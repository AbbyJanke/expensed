<?php

namespace AbbyJanke\Expensed\App\Console\Commands;

use Illuminate\Console\Command;
use AbbyJanke\Expensed\App\Models\Currency;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:install-expensed {categories=true}';

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
        Artisan::call('database:migrate'); // Add new database tables

        // Check to see if the API Token is set
        if(!is_null(config('backpack.expensed.api_token'))) {
            Artisan::call('currency:install'); // Add the currency
        }
        // double check to make sure they want default categories
        if($this->argument('categories')) {
            Artisan::call('db:seed', ['class' => 'AbbyJanke\Expensed\Database\Seeds\CategoriesTableSeeder']);
        }
    }

}
