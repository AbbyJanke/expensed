<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use App\Http\Requests\CurrencyRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
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
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('AbbyJanke\Expensed\App\Models\Currency');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/currency');
        $this->crud->setEntityNameStrings(trans('expensed::base.currency'), trans('expensed::base.currencies'));

        $this->crud->denyAccess('create');
        $this->crud->addButton('top', 'update_rates', 'view', 'expensed::buttons.update_rates', 'beginning');

        if (!$this->crud->getRequest()->has('order')) {
            $this->crud->orderBy('id');
        }
    }

    protected function setupListOperation()
    {
        $this->setupFilters();

        $this->crud->addColumn([
            'name'      => 'code',
            'label'     => trans('expensed::base.code'),
        ]);
        $this->crud->addColumn([
            'name'      => 'name',
            'label'     => trans('backpack::base.name'),
        ]);
        $this->crud->addColumn([
            'name'      => 'exchange_rate',
            'label'     => trans('expensed::base.exchange_rate'),
        ]);
        $this->crud->addColumn([
            'name'       => 'active',
            'label'     => trans('expensed::base.active_currency'),
            'type'       => 'check'
        ]);
        $this->crud->addColumn([
           'name'       => 'updated_at',
            'label'     => trans('expensed::base.last_update'),
           'type'       => 'datetime'
        ]);
    }

    /**
     * Allow rates to be updated via web interface.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateRates()
    {
        $cmd = 'php '.base_path().'/artisan currency:update';
        $export = shell_exec($cmd);

        Alert::info(trans('expensed::expensed.rates_processing'))->flash();

        return redirect(backpack_url('currency'));
    }

    /**
     * Add `rates` to the CRUD routes.
     * @param $segment
     * @param $routeName
     * @param $controller
     */
    protected function setupPublishRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/rates', [
            'as'        => $routeName.'.rates',
            'uses'      => $controller.'@updateRates',
            'operation' => 'updateRates',
        ]);
    }

    protected function setupFilters()
    {
        $this->crud->addFilter([
            'name' => 'status',
            'type' => 'dropdown',
            'label'=> 'Status'
        ], [
            1 => 'Is Active',
            0 => 'Not Active',
        ], function($value) { // if the filter is active
            $this->crud->addClause('where', 'active', $value);
        });
    }

}
