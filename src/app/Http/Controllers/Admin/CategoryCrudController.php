<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use AbbyJanke\Expensed\App\Http\Requests\CategoryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;

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
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/expenses/category');
        $this->crud->setEntityNameStrings(trans('expensed::base.categories'), trans('expensed::base.categories'));
    }

    protected function setupListOperation()
    {
        $this->crud->addColumn([
           'name'           => 'name',
           'label'          => trans('backpack::base.name')
        ]);
        $this->crud->addColumn([
            'name'          => 'type',
            'label'         => trans('expensed::base.category_type'),
        ]);
        $this->crud->addColumn([
            'name'          => 'this_year',
            'type'          => 'model_function',
            'label'         => trans('expensed::base.this_year_entries'),
            'function_name' => 'yearTotal',
        ]);
        $this->crud->addColumn([
            'name'          => 'entry_count',
            'type'          => 'model_function',
            'label'         => trans('expensed::base.total_entries'),
            'function_name' => 'countEntries',
        ]);
        $this->crud->addColumn([
            'name'          => 'created_at',
            'type'          => 'text',
            'label'         => trans('expensed::base.date_created')
        ]);
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
