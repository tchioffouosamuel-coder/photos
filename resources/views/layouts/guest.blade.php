<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="acid-auth">

    <div class="acid-toasts" id="acidToasts" aria-live="polite" aria-atomic="false"></div>

    <div class="acid-auth__grid">

        {{-- Volet de marque --}}
        <aside class="acid-auth__brand" aria-hidden="true">
            <div class="acid-auth__brand-top">
                <x-acid-logo variant="light" size="md" :tagline="true" />
            </div>

            <div class="acid-auth__brand-body">
                <h2 class="acid-auth__claim">
                    L'identification scolaire,<br>maîtrisée de bout en bout.
                </h2>
                <p class="acid-auth__pitch">
                    Photographies, cartes scolaires et documents d'examen :
                    une seule plateforme, des résultats conformes et prêts à imprimer.
                </p>

                <ul class="acid-auth__features">
                    <li><i class="fas fa-camera-retro" aria-hidden="true"></i> Prise de vue guidée et cadrage normalisé</li>
                    <li><i class="fas fa-id-card" aria-hidden="true"></i> Génération automatique des cartes scolaires</li>
                    <li><i class="fas fa-print" aria-hidden="true"></i> Planches de photos prêtes à l'impression</li>
                    <li><i class="fas fa-shield-halved" aria-hidden="true"></i> Données centralisées et traçables</li>
                </ul>
            </div>

            <p class="acid-auth__brand-foot">
                &copy; {{ date('Y') }} {{ config('app.name', 'ACID') }}
                @if (config('school.name'))
                    &middot; {{ config('school.name') }}
                @endif
            </p>
        </aside>

        {{-- Volet formulaire --}}
        <main class="acid-auth__panel">
            <div class="acid-auth__card acid-fade-in">

                <div class="acid-auth__mobile-logo">
                    <x-acid-logo variant="dark" size="md" :tagline="true" />
                </div>

                {{ $slot }}
            </div>

            <p class="acid-auth__legal">
                Accès reservé au personnel autorisé de l'établissement.
            </p>
        </main>
    </div>

    @include('layouts.partials.scripts')
</body>
</html>
