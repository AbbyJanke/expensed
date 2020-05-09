<?php

namespace AbbyJanke\Expensed\App\Http\Controllers\Admin;

use App\Http\Requests\CurrencyRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Route;
use Prologue\Alerts\Facades\Alert;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/money/currencies');
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

        CRUD::column('code')
            ->label(trans('expensed::base.code'));
        CRUD::column('name')
            ->label(trans('backpack::base.name'));
        CRUD::column('exchange_rate')
            ->label(trans('expensed::base.exchange_rate'));
        CRUD::column('active')
            ->type('check')
            ->label(trans('expensed::base.active_currency'));
        CRUD::column('updated_at')
            ->label(trans('expensed::base.last_update'));
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

        Alert::info(trans('expensed::base.rates_processing'))->flash();

        return redirect(backpack_url('currency'));
    }

    /**
     * Add `rates` to the CRUD routes.
     * @param $segment
     * @param $routeName
     * @param $controller
     */
    protected function setupRefreshRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/rates', [
            'as'        => $routeName.'.rates',
            'uses'      => $controller.'@updateRates',
            'operation' => 'updateRates',
        ]);
    }

    protected function setupFilters()
    {
        CRUD::filter('status')
            ->type('dropdown')
            ->label(trans('expensed::base.status'))
            ->values([
                1 => 'Is Active',
                0 => 'Not Active',
            ])
            ->whenActive(function($value) { // if the filter is active
                $this->crud->addClause('where', 'active', $value);
            });
    }

}
