@props([
    'align' => 'right',
    'width' => '48',
])

@php
    /* Dropdown ACID batie sur Bootstrap 5 (slots historiques : trigger / content). */
    $alignClass = $align === 'left' ? '' : 'dropdown-menu-end';
    $minWidth = is_numeric($width) ? ($width / 4) . 'rem' : '12rem';
@endphp

<div class="dropdown" {{ $attributes }}>
    <div data-bs-toggle="dropdown" aria-expanded="false" role="button" style="cursor:pointer;">
        {{ $trigger }}
    </div>

    <div class="dropdown-menu {{ $alignClass }}" style="min-width:{{ $minWidth }};">
        {{ $content }}
    </div>
</div>
