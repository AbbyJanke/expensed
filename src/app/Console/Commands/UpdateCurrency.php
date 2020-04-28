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
        $currencyRates = Http::get(getCurrencyURL('latest', '&show_alternative=true'))['rates'];
        foreach($currencyRates as $code => $currencyRate) {
            if(!$currency = Currency::where('code', $code)->first()) {
                $currency = $this->currencyNotFound($code);
            }

            $currency->exchange_rate = $currencyRate;
            $currency->save();
        }

        return;
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
