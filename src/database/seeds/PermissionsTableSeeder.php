<?php

namespace AbbyJanke\Expensed\Database\Seeds;

use Illuminate\Database\Seeder;
use AbbyJanke\Expensed\App\Models\Category;

class CategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $permissionNames = [
            'view_income',
            'create_income',
            'edit_income',
            'delete_income',
            'view_expense',
            'create_expense',
            'edit_expense',
            'delete_expense',
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',
            'view_reports',
            'view_currencies',
            'refresh_currencies',
            'view_users',
        ];

    }
}
