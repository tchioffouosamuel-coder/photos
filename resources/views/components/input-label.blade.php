@props(['value'])

<label {{ $attributes->merge(['class' => 'acid-label']) }}>
    {{ $value ?? $slot }}
</label>
