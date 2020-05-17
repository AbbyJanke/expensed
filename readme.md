# Laravel Expensed

Laravel Expensed is an open-source laravel package to help track your income and expenses. Expensed is based on [Backpack Admin Panel](https://backpackforlaravel.com).

This package uses [Open Exchange Rates](https://openexchangerates.org) to manage the currency data and exchange rates. It is strongly recommended you signup for the free plan and add your App ID to your `.env` file.

`CURRENCY_API_TOKEN=YOURAPPID`

If you do not wish to use Open Exchange Rates, I have provided a `currencies.sql` that you can import however any exchange rates would not be up to date.

## Install

Expensed uses Laravel Backpack as a foundation and is required. If you have not already installed Backpack do so by following the [Installation Documentation](https://backpackforlaravel.com/docs/4.0/installation).

If you already have it installed then you can proceed further.

Run command:
`composer require abbyjanke/expensed`

Once composer completed the necessary requirements, you should run:

`php artisan backpack:install-expensed`

This will install the migrations, currencies and default categories.

## Options
1. [Automatically Update Exchange Rates](https://github.com/AbbyJanke/expensed/wiki/Automatically-Update-Currency-Exchange-Rates)
2. [Limiting Permissions](https://github.com/AbbyJanke/expensed/wiki/Limit-Permissions)

Open Exchange Rates update their exchange rates hourly. The free plan allows up to 1,000 requests per month, with approximately 730 hours per month you SHOULD be able to run it every hour without problem.

## License

The Laravel Expensed package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
