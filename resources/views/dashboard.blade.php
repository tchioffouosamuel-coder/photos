@php
    $totalStudents    = $totalStudents ?? 0;
    $totalClasses     = $totalClasses ?? 0;
    $totalBoys        = $totalBoys ?? 0;
    $totalGirls       = $totalGirls ?? 0;
    $totalWithPhotos  = $totalWithPhotos ?? 0;
    $totalWithout     = max(0, $totalStudents - $totalWithPhotos);

    $pct = fn ($value) => $totalStudents > 0 ? round(($value / $totalStudents) * 100, 1) : 0;

    $pctPhotos = $pct($totalWithPhotos);
    $pctBoys   = $pct($totalBoys);
    $pctGirls  = $pct($totalGirls);
@endphp

<x-app-layout>

    {{-- Bandeau d'accueil --}}
    <section class="acid-hero" style="margin-block-end:var(--acid-space-6);">
        <div class="acid-row acid-row--between" style="gap:var(--acid-space-5);">
            <div style="min-width:0;">
                <h1 class="h2" style="margin:0 0 .375rem;">Tableau de bord</h1>
                <p>
                    Pilotage de l'identification scolaire &middot;
                    {{ now()->translatedFormat('d F Y') }}
                </p>
            </div>
            <div class="acid-hero__pill">
                <span>Annee scolaire</span>
                <strong>{{ date('Y') }}-{{ date('Y') + 1 }}</strong>
            </div>
        </div>
    </section>

    {{-- Indicateurs cles --}}
    <div class="acid-grid acid-grid--stats" style="margin-block-end:var(--acid-space-6);">

        <article class="acid-stat">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-school"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Classes</span>
                <span class="acid-stat__value">{{ number_format($totalClasses, 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">Classes actives cette annee</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--sky">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-users"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Eleves</span>
                <span class="acid-stat__value">{{ number_format($totalStudents, 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">
                    {{ number_format($totalBoys, 0, ',', ' ') }} garcons &middot;
                    {{ number_format($totalGirls, 0, ',', ' ') }} filles
                </span>
            </div>
        </article>

        <article class="acid-stat acid-stat--teal">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-camera-retro"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Photos enregistrees</span>
                <span class="acid-stat__value">{{ number_format($totalWithPhotos, 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">{{ $pctPhotos }}% de l'effectif couvert</span>
            </div>
        </article>

        <article class="acid-stat {{ $totalWithout > 0 ? 'acid-stat--coral' : '' }}">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-user-slash"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Photos manquantes</span>
                <span class="acid-stat__value">{{ number_format($totalWithout, 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">
                    @if ($totalWithout > 0)
                        <a href="{{ route('statistics.no-photos') }}" style="font-weight:var(--acid-fw-semibold);">
                            Voir la liste <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    @else
                        Effectif complet
                    @endif
                </span>
            </div>
        </article>
    </div>

    <div class="acid-grid" style="grid-template-columns:minmax(0,2fr) minmax(280px,1fr);align-items:start;">

        {{-- Repartition par classe --}}
        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Repartition des eleves par classe</h2>
                    <p class="acid-card__subtitle">Effectifs et couverture photographique</p>
                </div>
                <div class="acid-card__actions">
                    <a href="{{ route('classes.index') }}" class="acid-btn acid-btn--neutral acid-btn--sm">
                        <i class="fas fa-list" aria-hidden="true"></i>
                        Toutes les classes
                    </a>
                </div>
            </div>

            <div class="acid-table-wrap">
                <table class="acid-table acid-table--tiles">
                    <thead>
                        <tr>
                            <th scope="col">Classe</th>
                            <th scope="col" class="acid-table__num">Effectif</th>
                            <th scope="col" class="acid-table__num">Garcons</th>
                            <th scope="col" class="acid-table__num">Filles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classes ?? [] as $classe)
                            <tr data-acid-searchable="{{ $classe->nom }}">
                                <td data-label="Classe" data-cell="identity">
                                    <a href="{{ route('classes.show', $classe) }}" class="acid-table__primary">
                                        {{ $classe->nom }}
                                    </a>
                                </td>
                                <td data-label="Effectif" data-cell="meta" class="acid-table__num">
                                    <span class="acid-badge acid-badge--navy">{{ $classe->etudiants_count }}</span>
                                </td>
                                <td data-label="Garcons" data-cell="meta" class="acid-table__num">
                                    <span class="acid-badge acid-badge--sky">{{ $classe->effectif_garcons }}</span>
                                </td>
                                <td data-label="Filles" data-cell="meta" class="acid-table__num">
                                    <span class="acid-badge acid-badge--coral">{{ $classe->effectif_filles }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="acid-empty">
                                        <span class="acid-empty__icon" aria-hidden="true">
                                            <i class="fas fa-school"></i>
                                        </span>
                                        <h3 class="acid-empty__title">Aucune classe enregistree</h3>
                                        <p class="acid-empty__text">
                                            Creez une classe ou importez une liste d'eleves pour
                                            demarrer la campagne d'identification.
                                        </p>
                                        <div class="acid-empty__actions">
                                            <a href="{{ route('classes.create') }}" class="acid-btn acid-btn--primary">
                                                <i class="fas fa-plus" aria-hidden="true"></i> Nouvelle classe
                                            </a>
                                            <a href="{{ route('import.index') }}" class="acid-btn acid-btn--neutral">
                                                <i class="fas fa-file-import" aria-hidden="true"></i> Importer une liste
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="acid-stack">

            {{-- Avancement photographique --}}
            <section class="acid-card">
                <div class="acid-card__header">
                    <div>
                        <h2 class="acid-card__title">Avancement photographique</h2>
                        <p class="acid-card__subtitle">Eleves disposant d'une photo</p>
                    </div>
                </div>
                <div class="acid-card__body">
                    <div class="acid-row acid-row--between" style="margin-block-end:var(--acid-space-3);">
                        <span class="acid-stat__value" style="font-size:var(--acid-text-2xl);">{{ $pctPhotos }}%</span>
                        <span class="acid-badge acid-badge--teal">
                            {{ number_format($totalWithPhotos, 0, ',', ' ') }} / {{ number_format($totalStudents, 0, ',', ' ') }}
                        </span>
                    </div>

                    <div class="acid-progress" role="progressbar" aria-valuenow="{{ $pctPhotos }}"
                         aria-valuemin="0" aria-valuemax="100"
                         aria-label="Couverture photographique">
                        <div class="acid-progress__bar" style="width:{{ $pctPhotos }}%;"></div>
                    </div>

                    @if ($totalWithout > 0)
                        <a href="{{ route('statistics.no-photos') }}"
                           class="acid-btn acid-btn--outline-coral acid-btn--sm acid-btn--block"
                           style="margin-block-start:var(--acid-space-4);">
                            <i class="fas fa-user-slash" aria-hidden="true"></i>
                            {{ number_format($totalWithout, 0, ',', ' ') }} eleve(s) sans photo
                        </a>
                    @else
                        <p class="acid-hint" style="margin-block-start:var(--acid-space-3);">
                            <i class="fas fa-circle-check acid-text-teal" aria-hidden="true"></i>
                            Tous les eleves disposent d'une photo.
                        </p>
                    @endif
                </div>
            </section>

            {{-- Repartition par sexe --}}
            <section class="acid-card">
                <div class="acid-card__header">
                    <div>
                        <h2 class="acid-card__title">Repartition par sexe</h2>
                    </div>
                </div>
                <div class="acid-card__body">
                    <div class="acid-row" style="gap:var(--acid-space-2);margin-block-end:var(--acid-space-4);">
                        <div style="flex:{{ max($pctBoys, 1) }};min-width:2rem;">
                            <div style="height:.625rem;border-radius:var(--acid-radius-pill);
                                        background:var(--acid-sky-500);"></div>
                        </div>
                        <div style="flex:{{ max($pctGirls, 1) }};min-width:2rem;">
                            <div style="height:.625rem;border-radius:var(--acid-radius-pill);
                                        background:var(--acid-coral-500);"></div>
                        </div>
                    </div>

                    <div class="acid-form-grid acid-form-grid--2" style="gap:var(--acid-space-3);">
                        <div class="acid-card acid-card--soft" style="padding:var(--acid-space-4);text-align:center;">
                            <i class="fas fa-mars" aria-hidden="true"
                               style="color:var(--acid-sky-600);font-size:var(--acid-text-lg);"></i>
                            <div class="acid-stat__value" style="font-size:var(--acid-text-xl);">
                                {{ number_format($totalBoys, 0, ',', ' ') }}
                            </div>
                            <span class="acid-stat__hint">Garcons &middot; {{ $pctBoys }}%</span>
                        </div>

                        <div class="acid-card acid-card--soft" style="padding:var(--acid-space-4);text-align:center;">
                            <i class="fas fa-venus" aria-hidden="true"
                               style="color:var(--acid-coral-600);font-size:var(--acid-text-lg);"></i>
                            <div class="acid-stat__value" style="font-size:var(--acid-text-xl);">
                                {{ number_format($totalGirls, 0, ',', ' ') }}
                            </div>
                            <span class="acid-stat__hint">Filles &middot; {{ $pctGirls }}%</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- Acces rapides --}}
    <h2 class="acid-section-title">Acces rapides</h2>

    <div class="acid-grid acid-grid--cards">

        <a href="{{ route('classes.create') }}" class="acid-card acid-card--hover"
           style="padding:var(--acid-space-5);display:flex;gap:var(--acid-space-4);align-items:flex-start;">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
            <span>
                <strong style="display:block;color:var(--acid-text-strong);font-weight:var(--acid-fw-bold);">
                    Nouvelle classe
                </strong>
                <span class="acid-stat__hint">Creer une classe et son effectif</span>
            </span>
        </a>

        <a href="{{ route('import.index') }}" class="acid-card acid-card--hover"
           style="padding:var(--acid-space-5);display:flex;gap:var(--acid-space-4);align-items:flex-start;">
            <span class="acid-stat__icon" aria-hidden="true" style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                <i class="fas fa-file-import"></i>
            </span>
            <span>
                <strong style="display:block;color:var(--acid-text-strong);font-weight:var(--acid-fw-bold);">
                    Importer une liste
                </strong>
                <span class="acid-stat__hint">Charger un fichier Excel d'eleves</span>
            </span>
        </a>

        <a href="{{ route('cards.index') }}" class="acid-card acid-card--hover"
           style="padding:var(--acid-space-5);display:flex;gap:var(--acid-space-4);align-items:flex-start;">
            <span class="acid-stat__icon" aria-hidden="true" style="background:var(--acid-coral-50);color:var(--acid-coral-700);">
                <i class="fas fa-id-card"></i>
            </span>
            <span>
                <strong style="display:block;color:var(--acid-text-strong);font-weight:var(--acid-fw-bold);">
                    Cartes &amp; documents
                </strong>
                <span class="acid-stat__hint">Generer cartes, photos et planches</span>
            </span>
        </a>

        <a href="{{ route('statistics.index') }}" class="acid-card acid-card--hover"
           style="padding:var(--acid-space-5);display:flex;gap:var(--acid-space-4);align-items:flex-start;">
            <span class="acid-stat__icon" aria-hidden="true" style="background:var(--acid-sky-50);color:var(--acid-sky-600);">
                <i class="fas fa-chart-column"></i>
            </span>
            <span>
                <strong style="display:block;color:var(--acid-text-strong);font-weight:var(--acid-fw-bold);">
                    Statistiques
                </strong>
                <span class="acid-stat__hint">Suivi detaille par classe</span>
            </span>
        </a>
    </div>
</x-app-layout>
