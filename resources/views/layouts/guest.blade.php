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
                    L'identification scolaire,<br>maitrisee de bout en bout.
                </h2>
                <p class="acid-auth__pitch">
                    Photographies, cartes scolaires et documents d'examen :
                    une seule plateforme, des resultats conformes et pres a imprimer.
                </p>

                <ul class="acid-auth__features">
                    <li><i class="fas fa-camera-retro" aria-hidden="true"></i> Prise de vue guidee et cadrage normalise</li>
                    <li><i class="fas fa-id-card" aria-hidden="true"></i> Generation automatique des cartes scolaires</li>
                    <li><i class="fas fa-print" aria-hidden="true"></i> Planches de photos pretes a l'impression</li>
                    <li><i class="fas fa-shield-halved" aria-hidden="true"></i> Donnees centralisees et tracables</li>
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
                Acces reserve au personnel autorise de l'etablissement.
            </p>
        </main>
    </div>

    @include('layouts.partials.scripts')
</body>
</html>
