<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use App\Http\Requests\CurrencyRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Prologue\Alerts\Facades\Alert;

/**
 * Class CurrencyCrudController
 * @package AbbyJanke\Expensed\App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CurrencyCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('AbbyJanke\Expensed\App\Models\Currency');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/currency');
        $this->crud->setEntityNameStrings('currency', 'currencies');

        $this->crud->denyAccess('create');
        $this->crud->addButton('top', 'update_rates', 'view', 'expensed::update_exchange_button', 'beginning');

        if (!$this->crud->getRequest()->has('order')) {
            $this->crud->orderBy('id');
        }
    }

    protected function setupListOperation()
    {
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
        $this->crud->addColumn('code');
        $this->crud->addColumn('name');
        $this->crud->addColumn([
            'name'      => 'exchange_rate',
            'label'     => 'Rate'
        ]);
        $this->crud->addColumn([
           'name'       => 'updated_at',
           'label'      => 'Last Update',
           'type'       => 'datetime'
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(CurrencyRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->setFromDb();
    }

    protected function setupPublishRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/rates', [
            'as'        => $routeName.'.rates',
            'uses'      => $controller.'@updateRates',
            'operation' => 'updateRates',
        ]);
    }

    public function updateRates()
    {
        $cmd = 'php '.base_path().'/artisan currency:update';
        $export = shell_exec($cmd);

        Alert::info(trans('expensed::expensed.rates_processing'))->flash();

        return redirect(backpack_url('currency'));
    }

}
