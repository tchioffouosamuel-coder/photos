@php
    $totalStudents = (int) ($totalStudents ?? 0);
    $pct = fn ($value) => $totalStudents > 0 ? ($value / $totalStudents) * 100 : 0;

    $pctBoys   = $pct($totalBoys);
    $pctGirls  = $pct($totalGirls);
    $pctNormal = $pct($normalStudents);
    $pctRedo   = $pct($redoublants);
@endphp

<x-app-layout>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Pilotage</span>
            <h1 class="acid-page-header__title">Statistiques generales</h1>
            <p class="acid-page-header__lead">
                Vue d'ensemble des effectifs et de l'avancement de la campagne photographique.
            </p>
        </div>
        <div class="acid-page-header__actions">
            <button type="button" class="acid-btn acid-btn--neutral" onclick="window.print()">
                <i class="fas fa-print" aria-hidden="true"></i>
                Imprimer le rapport
            </button>
            <a href="{{ route('statistics.no-photos') }}" class="acid-btn acid-btn--primary">
                <i class="fas fa-user-slash" aria-hidden="true"></i>
                Photos manquantes
            </a>
        </div>
    </div>

    {{-- Indicateurs globaux --}}
    <div class="acid-grid acid-grid--stats" style="margin-block-end:var(--acid-space-6);">
        <article class="acid-stat">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-school"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Classes</span>
                <span class="acid-stat__value">{{ number_format($totalClasses, 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">Total dans le systeme</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--sky">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-users"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Eleves</span>
                <span class="acid-stat__value">{{ number_format($totalStudents, 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">Effectif total</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--teal">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-camera-retro"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Avec photo</span>
                <span class="acid-stat__value">{{ number_format($studentsWithPhotos, 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">{{ number_format($photoPercentage, 1) }}% complete</span>
            </div>
        </article>

        <article class="acid-stat {{ $studentsWithoutPhotos > 0 ? 'acid-stat--coral' : '' }}">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-user-slash"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Sans photo</span>
                <span class="acid-stat__value">{{ number_format($studentsWithoutPhotos, 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">
                    @if ($studentsWithoutPhotos > 0)
                        <a href="{{ route('statistics.no-photos') }}" style="font-weight:var(--acid-fw-semibold);">
                            Voir la liste <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    @else
                        Campagne terminee
                    @endif
                </span>
            </div>
        </article>
    </div>

    {{-- Progression globale --}}
    <section class="acid-card" style="margin-block-end:var(--acid-space-6);">
        <div class="acid-card__header">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                <i class="fas fa-list-check"></i>
            </span>
            <div>
                <h2 class="acid-card__title">Avancement de la campagne photographique</h2>
                <p class="acid-card__subtitle">
                    @if ($studentsWithoutPhotos > 0)
                        Il reste {{ number_format($studentsWithoutPhotos, 0, ',', ' ') }} photo(s) a collecter
                    @else
                        Toutes les photos ont ete collectees
                    @endif
                </p>
            </div>
            <div class="acid-card__actions">
                <span class="acid-stat__value" style="font-size:var(--acid-text-2xl);">
                    {{ number_format($photoPercentage, 1) }}%
                </span>
            </div>
        </div>
        <div class="acid-card__body">
            <div class="acid-progress" style="height:.75rem;" role="progressbar"
                 aria-valuenow="{{ $photoPercentage }}" aria-valuemin="0" aria-valuemax="100"
                 aria-label="Avancement global de la campagne">
                <div class="acid-progress__bar" style="width:{{ $photoPercentage }}%;"></div>
            </div>
            <p class="acid-hint" style="margin-block-start:var(--acid-space-2);">
                {{ number_format($studentsWithPhotos, 0, ',', ' ') }} eleves sur
                {{ number_format($totalStudents, 0, ',', ' ') }} disposent d'une photo.
            </p>
        </div>
    </section>

    {{-- Repartitions --}}
    <div class="acid-grid acid-grid--2" style="margin-block-end:var(--acid-space-6);">

        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Repartition par sexe</h2>
                </div>
            </div>
            <div class="acid-card__body">
                <div class="acid-row" style="gap:.25rem;margin-block-end:var(--acid-space-4);">
                    <div style="flex:{{ max($pctBoys, 1) }};min-width:1.5rem;height:.625rem;
                                border-radius:var(--acid-radius-pill);background:var(--acid-sky-500);"
                         title="Garcons"></div>
                    <div style="flex:{{ max($pctGirls, 1) }};min-width:1.5rem;height:.625rem;
                                border-radius:var(--acid-radius-pill);background:var(--acid-coral-500);"
                         title="Filles"></div>
                </div>

                <div class="acid-form-grid acid-form-grid--2" style="gap:var(--acid-space-3);">
                    <div class="acid-card acid-card--soft" style="padding:var(--acid-space-4);text-align:center;">
                        <i class="fas fa-mars" aria-hidden="true"
                           style="color:var(--acid-sky-600);font-size:var(--acid-text-lg);"></i>
                        <div class="acid-stat__value" style="font-size:var(--acid-text-xl);">
                            {{ number_format($totalBoys, 0, ',', ' ') }}
                        </div>
                        <span class="acid-stat__hint">Garcons &middot; {{ number_format($pctBoys, 1) }}%</span>
                    </div>
                    <div class="acid-card acid-card--soft" style="padding:var(--acid-space-4);text-align:center;">
                        <i class="fas fa-venus" aria-hidden="true"
                           style="color:var(--acid-coral-600);font-size:var(--acid-text-lg);"></i>
                        <div class="acid-stat__value" style="font-size:var(--acid-text-xl);">
                            {{ number_format($totalGirls, 0, ',', ' ') }}
                        </div>
                        <span class="acid-stat__hint">Filles &middot; {{ number_format($pctGirls, 1) }}%</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Repartition par statut</h2>
                </div>
            </div>
            <div class="acid-card__body">
                <div class="acid-row" style="gap:.25rem;margin-block-end:var(--acid-space-4);">
                    <div style="flex:{{ max($pctNormal, 1) }};min-width:1.5rem;height:.625rem;
                                border-radius:var(--acid-radius-pill);background:var(--acid-teal-500);"
                         title="Normal"></div>
                    <div style="flex:{{ max($pctRedo, 1) }};min-width:1.5rem;height:.625rem;
                                border-radius:var(--acid-radius-pill);background:var(--acid-amber-500);"
                         title="Redoublants"></div>
                </div>

                <div class="acid-form-grid acid-form-grid--2" style="gap:var(--acid-space-3);">
                    <div class="acid-card acid-card--soft" style="padding:var(--acid-space-4);text-align:center;">
                        <i class="fas fa-circle-check" aria-hidden="true"
                           style="color:var(--acid-teal-600);font-size:var(--acid-text-lg);"></i>
                        <div class="acid-stat__value" style="font-size:var(--acid-text-xl);">
                            {{ number_format($normalStudents, 0, ',', ' ') }}
                        </div>
                        <span class="acid-stat__hint">Normal &middot; {{ number_format($pctNormal, 1) }}%</span>
                    </div>
                    <div class="acid-card acid-card--soft" style="padding:var(--acid-space-4);text-align:center;">
                        <i class="fas fa-rotate-left" aria-hidden="true"
                           style="color:var(--acid-amber-600);font-size:var(--acid-text-lg);"></i>
                        <div class="acid-stat__value" style="font-size:var(--acid-text-xl);">
                            {{ number_format($redoublants, 0, ',', ' ') }}
                        </div>
                        <span class="acid-stat__hint">Redoublants &middot; {{ number_format($pctRedo, 1) }}%</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Detail par classe --}}
    <section class="acid-card">
        <div class="acid-card__header">
            <div>
                <h2 class="acid-card__title">Detail par classe</h2>
                <p class="acid-card__subtitle">Effectifs et couverture photographique</p>
            </div>
            <div class="acid-card__actions">
                <span class="acid-badge acid-badge--navy">{{ $classes->count() }} classes</span>
            </div>
        </div>

        <div class="acid-table-wrap">
            <table class="acid-table acid-table--tiles">
                <thead>
                    <tr>
                        <th scope="col">Classe</th>
                        <th scope="col" class="acid-table__num">Total</th>
                        <th scope="col" class="acid-table__num">Garcons</th>
                        <th scope="col" class="acid-table__num">Filles</th>
                        <th scope="col" class="acid-table__num">Avec photo</th>
                        <th scope="col" class="acid-table__num">Sans photo</th>
                        <th scope="col">Couverture</th>
                        <th scope="col" class="acid-table__actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $classe)
                        <tr data-acid-searchable="{{ $classe->nom }} {{ $classe->annee_scolaire }}">
                            <td data-label="Classe" data-cell="identity">
                                <span class="acid-identity__text">
                                    <strong>{{ $classe->nom }}</strong>
                                    <span>{{ $classe->annee_scolaire }}</span>
                                </span>
                            </td>
                            <td data-label="Total" data-cell="meta" class="acid-table__num">
                                <strong>{{ $classe->effectif_total }}</strong>
                            </td>
                            <td data-label="Garcons" data-cell="meta" class="acid-table__num">
                                <span class="acid-badge acid-badge--sky">{{ $classe->effectif_garcons }}</span>
                            </td>
                            <td data-label="Filles" data-cell="meta" class="acid-table__num">
                                <span class="acid-badge acid-badge--coral">{{ $classe->effectif_filles }}</span>
                            </td>
                            <td data-label="Avec photo" data-cell="meta" class="acid-table__num">
                                <span class="acid-badge acid-badge--teal">{{ $classe->students_with_photos }}</span>
                            </td>
                            <td data-label="Sans photo" data-cell="meta" class="acid-table__num">
                                @if ($classe->students_without_photos > 0)
                                    <span class="acid-badge acid-badge--amber">{{ $classe->students_without_photos }}</span>
                                @else
                                    <span class="acid-badge acid-badge--teal">0</span>
                                @endif
                            </td>
                            <td data-label="Couverture" data-cell="full">
                                <div class="acid-row" style="gap:var(--acid-space-2);flex-wrap:nowrap;">
                                    <div class="acid-progress" style="flex:1 1 5rem;min-width:4rem;"
                                         role="progressbar" aria-valuenow="{{ $classe->photo_percentage }}"
                                         aria-valuemin="0" aria-valuemax="100"
                                         aria-label="Couverture de {{ $classe->nom }}">
                                        <div class="acid-progress__bar"
                                             style="width:{{ $classe->photo_percentage }}%;"></div>
                                    </div>
                                    <strong class="acid-num" style="font-size:var(--acid-text-sm);">
                                        {{ number_format($classe->photo_percentage, 0) }}%
                                    </strong>
                                </div>
                            </td>
                            <td data-label="Actions" data-cell="actions" class="acid-table__actions">
                                <a href="{{ route('classes.show', $classe) }}"
                                   class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon"
                                   title="Ouvrir la classe" aria-label="Ouvrir la classe {{ $classe->nom }}">
                                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="acid-empty">
                                    <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-chart-column"></i></span>
                                    <h3 class="acid-empty__title">Aucune donnee a analyser</h3>
                                    <p class="acid-empty__text">
                                        Les statistiques apparaitront des que des classes et des eleves
                                        seront enregistres.
                                    </p>
                                    <div class="acid-empty__actions">
                                        <a href="{{ route('classes.create') }}" class="acid-btn acid-btn--primary">
                                            <i class="fas fa-plus" aria-hidden="true"></i> Creer une classe
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div data-acid-search-empty hidden>
            <div class="acid-empty">
                <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-magnifying-glass"></i></span>
                <h3 class="acid-empty__title">Aucune classe ne correspond</h3>
                <p class="acid-empty__text">Modifiez votre recherche dans la barre superieure.</p>
            </div>
        </div>
    </section>
</x-app-layout>
