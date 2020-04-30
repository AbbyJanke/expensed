{{-- regular object attribute --}}
@php
    $value = data_get($entry, $column['name']);
    $value = is_array($value) ? json_encode($value) : $value;

    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 40;
    $column['text'] = Str::limit($value, $column['limit'], '[...]');

    $currency = $entry->currency;
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
    {{ currencySymbol($entry->currency->code) }} {{ number_format($column['text'], 2) }}
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
