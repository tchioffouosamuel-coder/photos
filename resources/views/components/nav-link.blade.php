@props(['active' => false])

<a {{ $attributes->merge(['class' => 'acid-sidebar__link ' . ($active ? 'is-active' : '')]) }}>
    {{ $slot }}
</a>
