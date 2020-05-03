<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use AbbyJanke\Expensed\App\Models\Category;
use AbbyJanke\Expensed\App\Models\Expense;
use AbbyJanke\Expensed\App\Models\Income;
use App\Http\Requests\ReportsRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;

/**
 * Class ReportsController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ReportsController extends CrudController
{
    public function index()
    {
        $this->data['title'] = trans('expensed::base.reports');
        $this->data['widgets']['before_content'] = [
            [
                'type'        => 'progress_white',
                'class'       => 'card col-lg-12 mb-2',
                'wrapperClass' => 'float-left mr-4',
                'value'       => number_format($this->incomeAgainstExpense(date('Y'), date('n')), 2).'%',
                'description' => 'Income Spent This <strong>Month</strong>',
                'progress'    => $this->incomeAgainstExpense(date('Y'), date('n')),
                'progressClass' => 'progress-bar bg-primary',
            ],
            [
                'type'        => 'progress_white',
                'class'       => 'card col-lg-12 mb-2',
                'wrapperClass' => 'float-left mr-4',
                'value'       => number_format($this->incomeAgainstExpense(date('Y')), 2).'%',
                'description' => 'Income Spent This <strong>Year</strong>',
                'progress'    => $this->incomeAgainstExpense(date('Y')),
                'progressClass' => 'progress-bar bg-primary',
            ],
            [
                'type'        => 'progress_white',
                'class'       => 'card col-lg-12 mb-2',
                'wrapperClass' => 'float-left mr-4',
                'value'       => $this->highestExpenseCategory()->category->name,
                'description' => 'Highest Expense Category',
                'progress'    => 0,
                'progressClass' => 'progress-bar bg-primary',
            ],
            [
                'type'        => 'progress_white',
                'class'       => 'card col-lg-12 mb-2',
                'wrapperClass' => 'float-left mr-4',
                'value'       => $this->highestIncomeCategory()->category->name,
                'description' => 'Highest Income Category',
                'progress'    => 0,
                'progressClass' => 'progress-bar bg-primary',
            ]
        ];

        return view('expensed::reports', $this->data);
    }

    public function incomeAgainstExpense($year, $month = null)
    {

        $totalIncome = 0;
        $totalExpense = 0;

        $allIncome = Income::whereYear('entry_date', $year);

        if($month) {
            $allIncome->whereMonth('entry_date', $month);
        }

        foreach ($allIncome->get() as $income) {
            $totalIncome = $totalIncome + str_replace(',', '', number_format($income->amount));
        }

        $expense = 1;
        $allExpenses = Expense::whereYear('entry_date', $year);

        if($month) {
            $allExpenses->whereMonth('entry_date', $month);
        }

        foreach ($allExpenses->get() as $expense) {
            $totalExpense = $totalExpense + str_replace(',', '', number_format($expense->amount));
        }

        if($totalIncome) {
            return ($totalExpense*100)/$totalIncome;
        }

        return 0;
    }

    public function highestExpenseCategory()
    {
        return Expense::groupBy('category_id')
                        ->whereMonth('entry_date', date('n'))
                        ->selectRaw('sum(amount) as amount, category_id')
                        ->orderBy('amount', 'desc')
                        ->first();
    }

    public function highestIncomeCategory()
    {
        return Income::groupBy('category_id')
                        ->whereMonth('entry_date', date('n'))
                        ->selectRaw('sum(amount) as amount, category_id')
                        ->orderBy('amount', 'desc')
                        ->first();
    }
}

