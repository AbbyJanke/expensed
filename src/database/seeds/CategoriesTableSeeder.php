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
        Category::create([
            'name'  => 'Miscellaneous',
            'type'  => 'other',
        ]);

        $income = [
            'Paycheck',
            'Investments',
            'Gifts',
        ];

        foreach($income as $item) {
            Category::create([
                'name'  => $item,
                'type'  => 'income',
            ]);
        }

        $expenses = [
            'Grocery',
            'Medical',
            'Household Bills',
            'Automobile',
            'Mileage',
        ];

        foreach($expenses as $item) {
            Category::create([
                'name'  => $item,
                'type'  => 'expense',
            ]);
        }

    }
}
