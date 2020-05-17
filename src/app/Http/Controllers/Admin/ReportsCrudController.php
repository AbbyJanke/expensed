<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use AbbyJanke\Expensed\App\Models\Category;
use AbbyJanke\Expensed\App\Models\Expense;
use AbbyJanke\Expensed\App\Models\Income;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ReportsCrudControllerOLD
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ReportsCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;

    public function setup()
    {
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/money/reports');
        $this->crud->setEntityNameStrings(trans('expensed::base.report'), trans('expensed::base.reports'));

        $this->crud->setModel('AbbyJanke\Expensed\App\Models\Income');

        if(checkPermission() && backpack_user()->hasPermissionTo(config('backpack.expensed.permissions.reports.view'))) {
            $this->crud->denyAccess(['list','show','create','update','delete']);
            $this->crud->allowAccess(['list', 'show']);
        }

        CRUD::column('amount')
            ->type('view')
            ->label(trans('expensed::base.amount'))
            ->view('expensed::columns.money');
        CRUD::column('currency')
            ->type('view')
            ->label(trans('expensed::base.currency'))
            ->entity('currency')
            ->attribute('code')
            ->symbol(true)
            ->model('AbbyJanke\Expensed\App\Models\Currency')
            ->view('expensed::columns.currency_symbol');
        CRUD::column('exchange_rate')
            ->type('view')
            ->label(trans('expensed::base.exchanged_amount'))
            ->entity('currency')
            ->attribute('exchange_rate')
            ->model('AbbyJanke\Expensed\App\Models\Currency')
            ->view('expensed::columns.exchanged');
        CRUD::column('entry_date')
            ->type('date')
            ->label(trans('expensed::base.date_received'));
        CRUD::column('category_id')
            ->type('select')
            ->label(trans('expensed::base.category'))
            ->entity('category')
            ->attribute('name')
            ->model('AbbyJanke\Expensed\App\Models\Category');;
        CRUD::column('added_by_id')
            ->type('select')
            ->label(trans('expensed::base.added_by'))
            ->entity('added_by')
            ->attribute('name')
            ->model(config('backpack.base.user_model_fqn'));

        $this->setupFilters();

        $this->data['widgets']['after_content'] = $this->setupWidgets();
    }

    /**
     * Figure out the percentage of expense to income.
     * @param $year
     * @param null $month
     * @return float|int
     */
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

    /**
     * Get the category with highest expense
     * @return mixed
     */
    public function highestExpenseCategory()
    {
        return Expense::groupBy('category_id')
            ->whereMonth('entry_date', date('n'))
            ->selectRaw('sum(amount) as amount, category_id')
            ->orderBy('amount', 'desc')
            ->first();
    }

    /**
     * Get the category with the highest income
     * @return mixed
     */
    public function highestIncomeCategory()
    {
        return Income::groupBy('category_id')
            ->whereMonth('entry_date', date('n'))
            ->selectRaw('sum(amount) as amount, category_id')
            ->orderBy('amount', 'desc')
            ->first();
    }

    /**
     * Setup the widgets to be displayed at the top
     * @return array[]
     */
    private function setupWidgets() {
        return [
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
    }

    /**
     * Setup filters for the table.
     *
     * @return void
     */
    private function setupFilters()
    {
        CRUD::filter('type')
            ->type('dropdown')
            ->label(trans('expensed::base.entry_type'))
            ->values([
                'income' => trans('expensed::base.income'),
                'expense' => trans('expensed::base.expense'),
            ])
            ->whenActive(function($value) {
                if($value == 'expense') {
                    $this->crud->setModel('AbbyJanke\Expensed\App\Models\Expense');
                    if(config('backpack.expensed.private_expense')) {
                        $this->crud->addClause('where', 'added_by_id', backpack_user()->id);
                    }
                } else {
                    $this->crud->setModel('AbbyJanke\Expensed\App\Models\Income');
                    if($this->crud->getRequest()->request->has('type') && config('backpack.expensed.private_income')) {
                        $this->crud->addClause('where', 'added_by_id', backpack_user()->id);
                    }
                }
            })->apply();

        $categories = Category::get();
        $categoryOptions = [];

        foreach($categories as $category) {
            $categoryOptions[$category->id] = $category->name;
        }

        CRUD::filter('category')
            ->type('dropdown')
            ->label(trans('expensed::base.category'))
            ->values($categoryOptions)
            ->whenActive(function($value) {
                $this->crud->addClause('where', 'category_id', $value);
            });
        CRUD::filter('from_to')
            ->type('date_range')
            ->label(trans('expensed::base.date_range'))
            ->whenActive(function ($range) {
                $dates = json_decode($range);
                $this->crud->addClause('where', 'entry_date', '>=', $dates->from);
                $this->crud->addClause('where', 'entry_date', '<=', $dates->to);
            });
        CRUD::filter('currency_id')
            ->type('select2_ajax')
            ->label(trans('expensed::base.currency'))
            ->placeholder(trans('expensed:base.pick_currency'))
            ->values(backpack_url('money/ajax/currency'))
            ->whenActive(function($value) { // if the filter is active
                $this->crud->addClause('where', 'currency_id', $value);
            });
        if(checkPermission() && backpack_user()->hasPermissionTo(config('backpack.expensed.permissions.users.view_users'))) {
            CRUD::filter('added_by')
                ->type('select2_ajax')
                ->label(trans('expensed::base.added_by'))
                ->placeholder(trans('expensed::base.added_by'))
                ->values(backpack_url('money/ajax/users'))
                ->whenActive(function ($value) {
                    $this->crud->addClause('where', 'added_by_id', $value);
                })->apply();
        }
    }

}

