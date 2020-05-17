<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use AbbyJanke\Expensed\App\Http\Requests\CategoryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CategoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('AbbyJanke\Expensed\App\Models\Category');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/money/categories');
        $this->crud->setEntityNameStrings(trans('expensed::base.categories'), trans('expensed::base.categories'));

        if(checkPermission()) {
            $this->crud->denyAccess(['list', 'create', 'show', 'update', 'delete']);

            if (backpack_user()->hasPermissionTo('view_categories')) {
                $this->crud->allowAccess(['list', 'show']);
            }

            if (backpack_user()->hasPermissionTo('create_categories')) {
                $this->crud->allowAccess(['create']);
            }

            if (backpack_user()->hasPermissionTo('edit_categories')) {
                $this->crud->allowAccess(['update']);
            }

            if (backpack_user()->hasPermissionTo('delete_categories')) {
                $this->crud->allowAccess(['delete']);
            }
        }
    }

    protected function setupListOperation()
    {
        CRUD::filter('type')
            ->type('dropdown')
            ->label(trans('expensed::base.type_filter'))
            ->values([
                'other' => trans('expensed::base.other'),
                'income' => trans('expensed::base.income'),
                'expense' => trans('expensed::base.expenses'),
            ])
            ->whenActive(function($value) {
                $this->crud->addClause('where', 'type', $value);
            });
        CRUD::column('name')
            ->label(trans('backpack::base.name'));
        CRUD::column('type')
            ->label(trans('expensed::base.category_type'));
        CRUD::column('this_year')
            ->type('model_function')
            ->label(trans('expensed::base.this_year_entries'))
            ->function_name('yearTotal');
        CRUD::column('entry_count')
            ->type('model_function')
            ->label(trans('expensed::base.total_entries'))
            ->function_name('countEntries');
        CRUD::column('created_at')
            ->type('date')
            ->label(trans('expensed::base.date_created'));
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(CategoryRequest::class);

        $this->crud->addField([
            'name'           => 'name',
            'type'           => 'text',
            'label'          => trans('backpack::base.name')
        ]);

        $this->crud->addField([
            'name'           => 'type',
            'label'          => trans('expensed::base.category_type'),
            'type'           => 'select_from_array',
            'options'        => [
                'other'         => trans('expensed::base.other'),
                'income'        => trans('expensed::base.income'),
                'expense'       => trans('expensed::base.expense'),
            ]
        ]);

    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
