<?php

namespace AbbyJanke\Expensed\App\Console\Commands;

use Illuminate\Console\Command;
use AbbyJanke\Expensed\App\Models\Currency;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CleanupCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install exchange rates from online resources.';

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
        $confirm = $this->confirm('This will delete ALL currency records and repopulate them. This may result in any current expenses/income to fail to find the correct currency.');
        if($confirm) {
            DB::table('currencies')->truncate();
            $this->comment('Currency data has been cleaned up.');

            Artisan::call('currency:install');
            $this->comment('Currency data has been rebuilt.');
            $this->info('Currencies successfully have been cleaned up.');
        } else {
            $this->info('We have not adjusted the currencies at all. Use currency:update if you wish to update the rates.');
        }


        return;

    }

}
