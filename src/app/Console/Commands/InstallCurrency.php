<?php

namespace AbbyJanke\Expensed\App\Console\Commands;

use Illuminate\Console\Command;
use AbbyJanke\Expensed\App\Models\Currency;
use Illuminate\Support\Facades\Http;

class InstallCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:install';

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
        $this->info("\n".'Attempting To Install New Currency Rates..');

        if(is_null(config('backpack.expensed.api_token'))) {
            $this->error("\n".'Please enter your OpenExchangeRates.com API Token');
            return;
        }

        $currencyRates = Http::get(getCurrencyURL('latest', '&show_alternative=true'))['rates'];
        $currencies = Http::get(getCurrencyURL('currencies'))->json();
        $inactiveCurrencies = Http::get(getCurrencyURL('currencies', '&only_alternative=true'))->json();
        $totalCurrencyCount = count($currencies) + count($inactiveCurrencies);

        $bar = $this->output->createProgressBar($totalCurrencyCount);

        foreach($currencies as $code => $name) {
            $this->createNewCurrency($name, $code, $currencyRates[$code], true);
            $bar->advance();
        }

        foreach($inactiveCurrencies as $code => $name) {
            $this->createNewCurrency($name, $code, $currencyRates[$code]);
            $bar->advance();
        }

        $bar->finish();

        $this->info("\n".'Currencies Successfully Installed.');
        return;
    }

    private function createNewCurrency($name, $code, $rate, $status = false)
    {
        $currency = new Currency;
        $currency->name = $name;
        $currency->code = $code;
        $currency->exchange_rate = $rate;
        $currency->active = $status;

        $currency->save();
    }

}
