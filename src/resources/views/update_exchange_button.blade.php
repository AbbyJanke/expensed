@if ($crud->hasAccess('update'))
    <a href="{{ url($crud->route.'/rates') }} " class="btn btn-xs btn-info"><i class="la la-refresh"></i> {{ @trans('expensed::expensed.update_rates') }}</a>
@endif
