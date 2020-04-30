<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use AbbyJanke\Expensed\App\Http\Requests\IncomeRequest;
use AbbyJanke\Expensed\App\Models\Currency;
use Backpack\CRUD\app\Http\Controllers\CrudController;

/**
 * Class IncomeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class IncomeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('AbbyJanke\Expensed\App\Models\Income');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/expenses/income');
        $this->crud->setEntityNameStrings('income', 'incomes');
    }

    protected function setupListOperation()
    {
        $this->setupFilters();

        $this->crud->addColumn([
            'type'  => 'view',
            'name'  => 'amount',
            'label' => 'Amount',
            'view' => 'expensed::columns.money'
        ]);
        $this->crud->addColumn([
            'label' => 'Currency',
            'type' => 'view',
            'name' => 'currency',
            'entity' => 'currency',
            'attribute' => 'code',
            'symbol'    => true,
            'model' => 'AbbyJanke\Expensed\App\Models\Currency',
            'view' => 'expensed::columns.currency_symbol'
        ]);
        $this->crud->addColumn([
            'label' => 'Exchanged',
            'type' => 'view',
            'name' => 'exchanged',
            'entity' => 'currency',
            'attribute' => 'exchange_rate',
            'model' => 'AbbyJanke\Expensed\App\Models\Currency',
            'view' => 'expensed::columns.exchanged'
        ]);
        $this->crud->addColumn([
            'type'  => 'text',
            'name'  => 'comments',
            'label' => 'Comments',
        ]);
        $this->crud->addColumn([
            'type'  => 'date',
            'name'  => 'entry_date',
            'label' => 'Date Received',
        ]);
        $this->crud->addColumn([
            'label' => "Added By",
            'type' => "select",
            'name' => 'added_by_id',
            'entity' => 'added_by',
            'attribute' => "name",
            'model' => config('backpack.base.user_model_fqn'),
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(IncomeRequest::class);

        $this->crud->addField([
            'type'  => 'date',
            'name'  => 'entry_date',
            'label' => 'Date Received',
        ]);

        $this->crud->addField([
            'name'   => 'amount',
            'label'  => 'Amount',
            'type'   => 'number'
        ]);

        $this->crud->addField([  // Select
            'label' => "Category",
            'type' => 'select',
            'name' => 'category_id',
            'entity' => 'category',
            'options'   => (function ($query) {
                return $query->where('type', 'income')->orWhere('type', 'other')->get();
            })
        ]);

        $defaultCurrency = Currency::where('code', config('backpack.expensed.default_currency'))->first();

        $this->crud->addField([  // Select
            'label' => "Currency",
            'type' => 'select',
            'name' => 'currency_id',
            'entity' => 'currency',
            'options' => (function ($query) {
               return $query->orderBy('name')->get();
            }),
            'default'   => $defaultCurrency->id,
        ]);



        // TODO: remove setFromDb() and manually define Fields
        //$this->crud->setFromDb();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    private function setupFilters()
    {
        $this->crud->addFilter([
           'type'   => 'date_range',
           'name'   => 'from_to',
           'label'  => 'Date Range',
        ], false,
        function ($range) {
            $dates = json_decode($range);
            $this->crud->addClause('where', 'entry_date', '>=', $dates->from);
            $this->crud->addClause('where', 'entry_date', '<=', $dates->to);
        });
    }
}
