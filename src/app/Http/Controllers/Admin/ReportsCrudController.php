<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use AbbyJanke\Expensed\App\Models\Category;
use AbbyJanke\Expensed\App\Models\Currency;
use AbbyJanke\Expensed\App\Models\Expense;
use AbbyJanke\Expensed\App\Models\Income;
use Backpack\CRUD\app\Http\Controllers\CrudController;

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

        $this->crud->addColumn([
            'type'  => 'view',
            'name'  => 'amount',
            'label' => trans('expensed::base.amount'),
            'view' => 'expensed::columns.money'
        ]);
        $this->crud->addColumn([
            'label' => trans('expensed::base.currency'),
            'type' => 'view',
            'name' => 'currency',
            'entity' => 'currency',
            'attribute' => 'code',
            'symbol'    => true,
            'model' => 'AbbyJanke\Expensed\App\Models\Currency',
            'view' => 'expensed::columns.currency_symbol'
        ]);
        $this->crud->addColumn([
            'label' => trans('expensed::base.exchanged_amount'),
            'type' => 'view',
            'name' => 'exchanged',
            'entity' => 'currency',
            'attribute' => 'exchange_rate',
            'model' => 'AbbyJanke\Expensed\App\Models\Currency',
            'view' => 'expensed::columns.exchanged'
        ]);
        $this->crud->addColumn([
            'type'  => 'date',
            'name'  => 'entry_date',
            'label' => trans('expensed::base.entry_date'),
        ]);
        $this->crud->addColumn([
            'label' => trans('expensed::base.category'),
            'type' => "select",
            'name' => 'category_id',
            'entity' => 'category',
            'attribute' => "name",
            'model' => "AbbyJanke\Expensed\App\Models\Category",
        ]);
        $this->crud->addColumn([
            'label' => trans('expensed::base.added_by'),
            'type' => 'select',
            'name' => 'added_by_id',
            'entity' => 'added_by',
            'attribute' => 'name',
            'model' => config('backpack.base.user_model_fqn'),
        ]);

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
        $this->crud->addFilter([
            'name' => 'type',
            'type' => 'dropdown',
            'label'=> trans('expensed::base.entry_type')
        ], [
            'income' => trans('expensed::base.income'),
            'expense' => trans('expensed::base.expense'),
        ], function($value) {
            if($value == 'expense') {
                $this->crud->setModel('AbbyJanke\Expensed\App\Models\Expense');
            } else {
                $this->crud->setModel('AbbyJanke\Expensed\App\Models\Income');
            }
        }, function($value) {
            $this->crud->setModel('AbbyJanke\Expensed\App\Models\Income');
        });

        $categories = Category::get();
        $categoryOptions = [];

        foreach($categories as $category) {
            $categoryOptions[$category->id] = $category->name;
        }

        $this->crud->addFilter([
            'name' => 'category',
            'type' => 'dropdown',
            'label'=> trans('expensed::base.category')
        ], $categoryOptions, function($value) {
            $this->crud->addClause('where', 'category_id', $value);
        });

        $this->crud->addFilter([
            'type'   => 'date_range',
            'name'   => 'from_to',
            'label'  => trans('expensed::base.date_range'),
        ], false,
            function ($range) {
                $dates = json_decode($range);
                $this->crud->addClause('where', 'entry_date', '>=', $dates->from);
                $this->crud->addClause('where', 'entry_date', '<=', $dates->to);
        });

        $this->crud->addFilter([
            'name' => 'currency_id',
            'type' => 'select2_ajax',
            'label'=> 'Currency',
            'placeholder' => 'Pick a currency'
        ],
            url(backpack_url('money/reports/ajax-currency-options')), // the ajax route
            function($value) { // if the filter is active
                $this->crud->addClause('where', 'currency_id', $value);
        });
    }

    /**
     * Get currency options for an AJAX request.
     *
     * @return mixed
     */
    public function currencyOptions() {
        $term = $this->crud->getRequest()->request->get('term');
        $options = Currency::where('name', 'like', '%'.$term.'%')->orWhere('code', 'like', '%'.$term.'%')->get()->pluck('name', 'id');
        return $options;
    }

}

