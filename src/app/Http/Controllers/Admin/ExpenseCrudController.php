<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use AbbyJanke\Expensed\App\Http\Requests\ExpenseRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use AbbyJanke\Expensed\App\Models\Category;
use AbbyJanke\Expensed\App\Models\Currency;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ExpenseCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ExpenseCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as storeTrait; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { edit as editTrait; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation { show as showTrait; }

    public function setup()
    {
        $this->crud->setModel('AbbyJanke\Expensed\App\Models\Expense');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/money/expenses');
        $this->crud->setEntityNameStrings(trans('expensed::base.expense'), trans('expensed::base.expenses'));

        if(checkPermission()) {
            $this->crud->denyAccess(['list','create','show','update','delete']);

            if(backpack_user()->hasPermissionTo('view_expense')) {
                $this->crud->allowAccess(['list', 'show']);
            }

            if(backpack_user()->hasPermissionTo('create_expense')) {
                $this->crud->allowAccess(['create']);
            }

            if(backpack_user()->hasPermissionTo('edit_expense')) {
                $this->crud->allowAccess(['update']);
            }

            if(backpack_user()->hasPermissionTo('delete_expense')) {
                $this->crud->allowAccess(['delete']);
            }
        }

        $this->setupFilters();

        if(!checkPermission() && !backpack_user()->hasPermissionTo(config('backpack.expensed.permissions.override.priv_expense'))) {
            $this->crud->addClause('where', 'added_by_id', backpack_user()->id);
        }

        if(!checkPermission() OR backpack_user()->hasPermissionTo(config('backpack.expensed.permissions.users.view_users'))) {
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

    protected function setupListOperation()
    {
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
        CRUD::column('added_by_id')
            ->type('select')
            ->label(trans('expensed::base.added_by'))
            ->entity('added_by')
            ->attribute('name')
            ->model(config('backpack.base.user_model_fqn'));
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(ExpenseRequest::class);
        $defaultCurrency = Currency::where('code', config('backpack.expensed.default_currency'))->first();

        CRUD::field('amount')
            ->type('currency')
            ->label(trans('expensed::base.amount'))
            ->view_namespace('expensed::fields');
        CRUD::field('entry_date')
            ->type('date')
            ->label(trans('expensed::base.date_received'));
        CRUD::field('category_id')
            ->type('select')
            ->label(trans('expensed::base.category'))
            ->entity('category')
            ->attribute('name')
            ->options(function ($query) {
                return $query->where('type', 'income')->orWhere('type', 'other')->get();
            });
        CRUD::field('currency_id')
            ->type('select')
            ->label(trans('expensed::base.currency'))
            ->entity('currency')
            ->attribute('name')
            ->options(function ($query) {
                return $query->orderBy('name')->get();
            })
            ->default($defaultCurrency->id);
        CRUD::field('comments')
            ->type('textarea')
            ->label(trans('expensed::base.comments'));
    }

    public function show($id)
    {
        if(!$this->crud->getEntry($id)->added_by_id == backpack_user()->id) {
            $this->crud->denyAccess('show');
        }
        return $this->showTrait($id);
    }

    public function edit($id)
    {
        if(!$this->crud->getEntry($id)->added_by_id == backpack_user()->id) {
            $this->crud->denyAccess('update');
        }
        return $this->editTrait($id);
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
        $categories = Category::whereIn('type', ['other', 'income'])->get();
        $options = [];

        foreach($categories as $category) {
            $options[$category->id] = $category->name;
        }

        CRUD::filter('category')
            ->type('dropdown')
            ->label(trans('expensed::base.category'))
            ->values($options)
            ->whenActive(function($value) {
                $this->crud->addClause('where', 'category_id', $value);
            })
            ->apply();

        CRUD::filter('from_to')
            ->type('date_range')
            ->label(trans('expensed::base.date_range'))
            ->whenActive(function($range) {
                $dates = json_decode($range);
                $this->crud->addClause('where', 'entry_date', '>=', $dates->from);
                $this->crud->addClause('where', 'entry_date', '<=', $dates->to);
            })->apply();
    }
}
