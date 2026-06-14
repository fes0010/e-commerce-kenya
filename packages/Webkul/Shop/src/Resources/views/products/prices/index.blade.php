@if ($prices['final']['price'] < $prices['regular']['price'])
    <p
        class="final-price font-semibold text-blue-900 max-sm:leading-4"
        aria-label="{{ $prices['final']['formatted_price'] }}"
    >
        {{ $prices['final']['formatted_price'] }}
    </p>

    <p class="font-medium text-zinc-500 line-through max-sm:leading-4">
        {{ $prices['regular']['formatted_price'] }}
    </p>
@else
    <p class="final-price font-semibold text-blue-900 max-sm:leading-4">
        {{ $prices['regular']['formatted_price'] }}
    </p>
@endif
