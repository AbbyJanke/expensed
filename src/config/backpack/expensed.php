<?php

return [

    /*
     * Default Currency You wish to use
     */
    'default_currency' => 'USD',

    /*
     * Should the income entries be viewable to all or only viewable by the creator.
     */
    'private_income' => true,

    /*
     * Should the expenses entries be viewable to all or only viewable by the creator.
     */
    'private_expense' => true,

    /**
     * Permission Names to be used when verifying access.
     */
    'permissions' => [
        'income' => [
            'view' => 'view_income',
            'add' => 'add_income',
            'edit' => 'edit_income',
            'delete' => 'delete_income',
        ],
        'expense' => [
            'view' => 'view_expense',
            'add' => 'add_expense',
            'edit' => 'edit_expense',
            'delete' => 'delete_expense',
        ],
        'categories' => [
            'view' => 'view_categories',
            'add' => 'add_categories',
            'edit' => 'edit_categories',
            'delete' => 'delete_categories',
        ],
        'reports' => [
            'view' => 'view_reports',
        ],
        'currency' => [
            'view' => 'view_currencies',
            'refresh' => 'refresh_currencies',
        ],
        'users' => [
            'view' => 'view_users',
        ],
        'override' => [
            'priv_income' => 'view_priv_income',
            'priv_expense' => 'view_priv_expense'
        ]
    ],

    /*
     * API Token received from your service provider.
     */
    'api_token' => env('CURRENCY_API_TOKEN', null),

];
