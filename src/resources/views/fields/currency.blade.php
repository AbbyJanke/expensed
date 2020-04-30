<!-- text input -->
@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')

@if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
    @if(isset($field['prefix'])) <div class="input-group-prepend"><span class="input-group-text">{!! $field['prefix'] !!}</span></div> @endif
    <input
        type="text"
        name="{{ $field['name'] }}"
        value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
        data-inputmask="'alias': 'numeric', 'groupSeparator': ',', 'rightAlign': false, 'autoGroup': true, 'digits': 2, 'digitsOptional': false, 'prefix': '{{ currencySymbol(config('backpack.expensed.default_currency')) }} ', 'placeholder': '0'"
        @include('crud::fields.inc.attributes')
    >
    @if(isset($field['suffix'])) <div class="input-group-append"><span class="input-group-text">{!! $field['suffix'] !!}</span></div> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
    </div>

    @push('after_scripts')
        <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.3/dist/jquery.inputmask.min.js"></script>
        <script>
            $(":input").inputmask();
        </script>
    @endpush
