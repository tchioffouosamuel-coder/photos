@props([
    'variant' => 'light',   /* light = sur fond marine | dark = sur fond clair */
    'size' => 'md',         /* sm | md | lg */
    'wordmark' => true,     /* affiche le nom a cote du monogramme */
    'tagline' => false,     /* affiche la baseline sous le nom */
])

@php
    /*
     | Logo officiel : depose ton fichier dans public/assets/images/ sous l'un
     | de ces noms et il remplacera automatiquement le logotype de secours.
     */
    $candidates = [
        'assets/images/acid-logo.svg',
        'assets/images/logo.svg',
        'assets/images/acid-logo.png',
        'assets/images/logo.png',
        'assets/images/acid-logo.webp',
    ];

    $logoFile = null;
    foreach ($candidates as $candidate) {
        if (is_file(public_path($candidate))) {
            $logoFile = $candidate;
            break;
        }
    }

    $markSize = ['sm' => '1.75rem', 'md' => '2.25rem', 'lg' => '3rem'][$size] ?? '2.25rem';
    $nameSize = ['sm' => 'var(--acid-text-md)', 'md' => 'var(--acid-text-lg)', 'lg' => 'var(--acid-text-xl)'][$size] ?? 'var(--acid-text-lg)';

    $isLight = $variant === 'light';
    $nameColor = $isLight ? 'var(--acid-text-invert)' : 'var(--acid-navy)';
    $taglineColor = $isLight ? 'rgba(255,255,255,.55)' : 'var(--acid-text-muted)';
@endphp

<span {{ $attributes->merge(['class' => 'acid-logo']) }}
      style="display:inline-flex;align-items:center;gap:.625rem;min-width:0;">

    @if ($logoFile)
        <img src="{{ asset($logoFile) }}"
             alt="{{ config('app.name', 'ACID') }}"
             style="height:{{ $markSize }};width:auto;display:block;flex:0 0 auto;">
    @else
        {{-- Logotype de secours : monogramme ACID --}}
        <svg width="{{ $markSize }}" height="{{ $markSize }}" viewBox="0 0 40 40"
             fill="none" xmlns="http://www.w3.org/2000/svg"
             style="flex:0 0 auto;display:block;" role="img"
             aria-label="{{ config('app.name', 'ACID') }}">
            <rect width="40" height="40" rx="11"
                  fill="{{ $isLight ? '#FFFFFF' : '#082B59' }}"
                  fill-opacity="{{ $isLight ? '0.10' : '1' }}"/>
            <path d="M20 9.5 29.5 30.5h-5.1l-1.7-4.1h-5.4l-1.7 4.1H10.5L20 9.5Zm0 8.1-2.1 5.1h4.2L20 17.6Z"
                  fill="{{ $isLight ? '#FFFFFF' : '#FFFFFF' }}"/>
            <circle cx="30.5" cy="12" r="3.5" fill="#18C5B2"/>
        </svg>
    @endif

    @if ($wordmark)
        <span style="min-width:0;line-height:1.15;">
            <strong style="display:block;font-size:{{ $nameSize }};font-weight:800;
                           letter-spacing:-.02em;color:{{ $nameColor }};">
                {{ config('app.name', 'ACID') }}
            </strong>
            @if ($tagline)
                <span style="display:block;font-size:var(--acid-text-xs);font-weight:500;
                             letter-spacing:.04em;color:{{ $taglineColor }};">
                    Identification scolaire
                </span>
            @endif
        </span>
    @endif
</span>
