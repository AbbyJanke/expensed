<?php

return [

    /*
     * Default Currency You wish to use
     */
    'default_currency' => 'USD',

    /*
     * Should the income entries be viewable to all or only viewable by the creator.
     */
    'private_income' => false,

    /*
     * Should the expenses entries be viewable to all or only viewable by the creator.
     */
    'private_expense' => true,

    /*
     * API Token received from your service provider.
     */
    'api_token' => env('CURRENCY_API_TOKEN', null),

];
