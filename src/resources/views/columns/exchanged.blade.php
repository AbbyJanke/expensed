{{-- single relationships (1-1, 1-n) --}}
@php
    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 40;
    $column['attribute'] = $column['attribute'] ?? (new $column['model'])->identifiableAttribute();

    $attributes = $crud->getRelatedEntriesAttributes($entry, $column['entity'], $column['attribute']);
    foreach ($attributes as $key => $text) {
        $text = Str::limit($text, $column['limit'], '[...]');
    }

    $defaultCurrency = \AbbyJanke\Expensed\App\Models\Currency::where('code', config('backpack.expensed.default_currency'))->first();
@endphp

<span>
    @if(count($attributes))
        @php
            $lastKey = array_key_last($attributes)
        @endphp

        @foreach($attributes as $key => $text)
            @php
                $related_key = $key;
            @endphp

            @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')

            {{ currencySymbol($defaultCurrency->code) }} {{ number_format($entry->amount / $text, 2) }}

            @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
            @if($lastKey != $key), @endif
        @endforeach
    @else
        -
    @endif
</span>
