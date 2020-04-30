<?php

if (! function_exists('getLatestCurrencyURL')) {

    function getCurrencyURL($method = 'latest', $additionalQuery = null)
    {
        $apiToken = config('backpack.expensed.api_token');
        $defaultCurrency = config('backpack.expensed.default_currency');
        $serviceURL = 'https://openexchangerates.org/api/'.$method.'.json?app_id='.$apiToken.'&prettyprint=false&base='.$defaultCurrency.$additionalQuery;

        return $serviceURL;
    }

}

if (! function_exists('currencySymbol')) {

    function currencySymbol($code)
    {
        $currencies = include(__DIR__.'/resources/currencies.php');
        return $currencies[$code]['symbol'];
    }

}
