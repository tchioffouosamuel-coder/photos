@push('styles')
    <style>
        .acid-landing { background: var(--acid-canvas); }

        .acid-landing__wrap {
            width: 100%;
            max-width: 1120px;
            margin-inline: auto;
            padding-inline: var(--acid-space-5);
        }

        .acid-landing__bar {
            position: sticky;
            inset-block-start: 0;
            z-index: 10;
            padding-block: var(--acid-space-4);
            background: rgba(255, 249, 239, 0.88);
            backdrop-filter: blur(8px);
            border-block-end: 1px solid var(--acid-border);
        }

        .acid-landing__hero {
            position: relative;
            overflow: hidden;
            padding-block: var(--acid-space-12);
            background: var(--acid-navy);
            color: var(--acid-text-invert);
        }
        .acid-landing__hero::after {
            content: "";
            position: absolute;
            inset-block-start: -30%;
            inset-inline-end: -6%;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: rgba(24, 197, 178, 0.12);
            pointer-events: none;
        }
        .acid-landing__hero .acid-landing__wrap { position: relative; z-index: 1; }

        .acid-landing__title {
            margin: var(--acid-space-4) 0 var(--acid-space-4);
            font-size: clamp(2rem, 4.2vw, 3.125rem);
            font-weight: var(--acid-fw-extrabold);
            line-height: 1.12;
            letter-spacing: -0.025em;
            color: var(--acid-text-invert);
        }

        .acid-landing__lead {
            max-width: 58ch;
            margin: 0;
            font-size: var(--acid-text-lg);
            color: rgba(255, 255, 255, 0.76);
        }

        .acid-landing__footer {
            padding-block: var(--acid-space-6);
            border-block-start: 1px solid var(--acid-border);
            font-size: var(--acid-text-sm);
            color: var(--acid-text-muted);
        }

        @media (max-width: 575.98px) {
            .acid-landing__bar .acid-btn-group { width: 100%; }
            .acid-landing__bar .acid-row { flex-direction: column; align-items: flex-start; }
            .acid-landing__footer .acid-row { flex-direction: column; gap: var(--acid-space-1); }
        }
    </style>
@endpush

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="acid-landing">

    <header class="acid-landing__bar">
        <div class="acid-landing__wrap acid-row acid-row--between">
            <x-acid-logo variant="dark" size="md" :tagline="true" />

            <div class="acid-btn-group">
                @auth
                    <a href="{{ route('dashboard') }}" class="acid-btn acid-btn--primary">
                        <i class="fas fa-gauge-high" aria-hidden="true"></i>
                        Mon tableau de bord
                    </a>
                @else
                    <a href="{{ route('login') }}" class="acid-btn acid-btn--primary">
                        <i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i>
                        Se connecter
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="acid-btn acid-btn--neutral">
                            Creer un acces
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    <main>
        <section class="acid-landing__hero">
            <div class="acid-landing__wrap">
                <span class="acid-badge acid-badge--teal">
                    <span class="acid-badge__dot" aria-hidden="true"></span>
                    Plateforme d'identification scolaire
                </span>

                <h1 class="acid-landing__title">
                    L'identite de chaque eleve,<br>
                    maitrisee de bout en bout.
                </h1>

                <p class="acid-landing__lead">
                    {{ config('app.name', 'ACID') }} centralise les eleves, les photographies,
                    les cartes scolaires et les documents d'examen. Une seule chaine de production,
                    des resultats conformes et prets a imprimer.
                </p>

                <div class="acid-btn-group" style="margin-block-start:var(--acid-space-6);">
                    @auth
                        <a href="{{ route('dashboard') }}" class="acid-btn acid-btn--secondary acid-btn--lg">
                            Acceder a la plateforme
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="acid-btn acid-btn--secondary acid-btn--lg">
                            Acceder a la plateforme
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        <section class="acid-landing__wrap" style="padding-block:var(--acid-space-12);">
            <h2 class="acid-section-title" style="margin-block-start:0;">Ce que la plateforme prend en charge</h2>

            <div class="acid-grid acid-grid--cards">
                <article class="acid-card" style="padding:var(--acid-space-5);">
                    <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-users"></i></span>
                    <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">
                        Gestion des eleves
                    </h3>
                    <p class="acid-card__subtitle" style="margin:0;">
                        Classes, effectifs et import de listes Excel en une operation.
                    </p>
                </article>

                <article class="acid-card" style="padding:var(--acid-space-5);">
                    <span class="acid-stat__icon" aria-hidden="true"
                          style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                        <i class="fas fa-camera-retro"></i>
                    </span>
                    <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">
                        Prise de vue guidee
                    </h3>
                    <p class="acid-card__subtitle" style="margin:0;">
                        Camera integree, cadrage normalise et recadrage automatique.
                    </p>
                </article>

                <article class="acid-card" style="padding:var(--acid-space-5);">
                    <span class="acid-stat__icon" aria-hidden="true"
                          style="background:var(--acid-sky-50);color:var(--acid-sky-600);">
                        <i class="fas fa-id-card"></i>
                    </span>
                    <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">
                        Cartes scolaires
                    </h3>
                    <p class="acid-card__subtitle" style="margin:0;">
                        Generation automatique, par classe ou pour tout l'etablissement.
                    </p>
                </article>

                <article class="acid-card" style="padding:var(--acid-space-5);">
                    <span class="acid-stat__icon" aria-hidden="true"
                          style="background:var(--acid-coral-50);color:var(--acid-coral-700);">
                        <i class="fas fa-print"></i>
                    </span>
                    <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">
                        Documents d'examen
                    </h3>
                    <p class="acid-card__subtitle" style="margin:0;">
                        Photos labellisees et planches imprimables aux formats officiels.
                    </p>
                </article>
            </div>
        </section>
    </main>

    <footer class="acid-landing__footer">
        <div class="acid-landing__wrap acid-row acid-row--between">
            <span>
                &copy; {{ date('Y') }} {{ config('app.name', 'ACID') }}
                @if (config('school.name'))
                    &middot; {{ config('school.name') }}
                @endif
            </span>
            <span>Acces reserve au personnel autorise</span>
        </div>
    </footer>

    @include('layouts.partials.scripts')
</body>
</html>
