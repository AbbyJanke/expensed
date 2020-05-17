<?php

namespace AbbyJanke\Expensed\App\Console\Commands;

use AbbyJanke\Expensed\App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update exchange rates from online resources.';

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
        if(is_null(config('backpack.expensed.api_token'))) {
            $this->error('Please enter your OpenExchangeRates.com API Token');
            return;
        } else {
            $this->info("\n".'Updating Currencies..');
            $currencyRates = Http::get(getCurrencyURL('latest', '&show_alternative=true'))['rates'];

            $bar = $this->output->createProgressBar(count($currencyRates));
            foreach($currencyRates as $code => $currencyRate) {
                if(!$currency = Currency::where('code', $code)->first()) {
                    $currency = $this->currencyNotFound($code);
                }

                $currency->exchange_rate = $currencyRate;
                $currency->save();

                $bar->advance();
            }

            $bar->finish();
            $this->info("\n".'Currencies Successfully Updated.');
            return;
        }
    }

    /**
     * Create a new currency in the database as one was not found.
     *
     * @param $currencyCode
     * @return Currency
     */
    private function currencyNotFound($currencyCode) {
        $currencies = Http::get(getCurrencyURL('currencies'))->json();
        $currency = new Currency;
        $currency->name = $currencies[$currencyCode];
        $currency->code = $currencyCode;
        return $currency;
    }

}
