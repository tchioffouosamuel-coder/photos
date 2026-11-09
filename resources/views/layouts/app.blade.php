<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="acid-app">

    {{-- Lien d'evitement (accessibilite clavier) --}}
    <a href="#acidContent" class="acid-sr-only">Aller au contenu principal</a>

    {{-- Zone des notifications --}}
    <div class="acid-toasts" id="acidToasts" aria-live="polite" aria-atomic="false"></div>

    @include('layouts.partials.sidebar')

    <div class="acid-main">

        @include('layouts.partials.topbar')

        <main class="acid-content acid-fade-in" id="acidContent">
            {{-- En-tete de page optionnel : <x-slot:header> --}}
            @isset($header)
                {{ $header }}
            @endisset

            {{ $slot }}
        </main>
    </div>

    {{-- Navigation basse : mobile et tablette uniquement --}}
    @include('layouts.partials.bottomnav')

    @include('layouts.partials.scripts')
</body>
</html>
