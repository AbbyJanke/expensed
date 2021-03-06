<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use AbbyJanke\Expensed\App\Http\Requests\ExpenseRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use AbbyJanke\Expensed\App\Models\Category;
use AbbyJanke\Expensed\App\Models\Currency;

/**
 * Class ExpenseCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ExpenseCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as storeTrait; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('AbbyJanke\Expensed\App\Models\Expense');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/money/expenses');
        $this->crud->setEntityNameStrings(trans('expensed::base.expense'), trans('expensed::base.expenses'));
    }

    protected function setupListOperation()
    {
        $this->setupFilters();

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
            'label' => trans('expensed::base.date_paid'),
        ]);
        $this->crud->addColumn([
            'label' => trans('expensed::base.added_by'),
            'type' => 'select',
            'name' => 'added_by_id',
            'entity' => 'added_by',
            'attribute' => 'name',
            'model' => config('backpack.base.user_model_fqn'),
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->addField([
            'name'   => 'amount',
            'label'  => trans('expensed::base.amount'),
            'type'   => 'currency',
            'view_namespace'    => 'expensed::fields'
        ]);
        $this->crud->setValidation(ExpenseRequest::class);
        $this->crud->addField([
            'type'  => 'date',
            'name'  => 'entry_date',
            'label' => trans('expensed::base.date_paid'),
        ]);
        $this->crud->addField([
            'label' => trans('expensed::base.category'),
            'type' => 'select',
            'name' => 'category_id',
            'entity' => 'category',
            'options'   => (function ($query) {
                return $query->where('type', 'expense')->orWhere('type', 'other')->get();
            })
        ]);

        $defaultCurrency = Currency::where('code', config('backpack.expensed.default_currency'))->first();

        $this->crud->addField([
            'label' => trans('expensed::base.currency'),
            'type' => 'select',
            'name' => 'currency_id',
            'entity' => 'currency',
            'options' => (function ($query) {
                return $query->orderBy('name')->get();
            }),
            'default'   => $defaultCurrency->id,
        ]);

        $this->crud->addField([
            'label' => trans('expensed::base.comments'),
            'type'  => 'textarea',
            'name'  => 'comments',
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function store()
    {
        if(is_null($this->crud->getRequest()->get('entry_date'))) {
            $this->crud->getRequest()->request->add(['entry_date' => now()]);
        }
        $response = $this->storeTrait();
        return $response;
    }

    private function setupFilters()
    {
        $categories = Category::whereIn('type', ['other', 'expense'])->get();
        $options = [];

        foreach($categories as $category) {
            $options[$category->id] = $category->name;
        }

        $this->crud->addFilter([
            'name' => 'category',
            'type' => 'dropdown',
            'label'=> trans('expensed::base.category')
        ], $options, function($value) {
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
    }
}
