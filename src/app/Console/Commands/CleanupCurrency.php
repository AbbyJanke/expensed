<?php

namespace AbbyJanke\Expensed\App\Console\Commands;

use Illuminate\Console\Command;
use AbbyJanke\Expensed\App\Models\Currency;
use Illuminate\Support\Facades\Artisan;

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
        Currency::query()->delete();
        $this->comment('Currency data has been cleaned up.');

        Artisan::call('currency:install');
        $this->comment('Currency data has been rebuilt.');
    }

}
