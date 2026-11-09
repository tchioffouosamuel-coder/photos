<x-app-layout>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Production</span>
            <h1 class="acid-page-header__title">Cartes &amp; documents</h1>
            <p class="acid-page-header__lead">
                Generez les cartes scolaires, les photos labellisees d'examen et les planches
                imprimables, par classe ou pour tout l'etablissement.
            </p>
        </div>
    </div>

    {{-- Generation globale --}}
    <section class="acid-hero" style="margin-block-end:var(--acid-space-6);">
        <div class="acid-row acid-row--between" style="gap:var(--acid-space-6);align-items:flex-start;">
            <div style="min-width:0;flex:1 1 320px;">
                <h2 class="h3" style="margin:0 0 .5rem;">Generation globale</h2>
                <p style="max-width:52ch;">
                    Produit en une seule operation les cartes scolaires de toutes les classes,
                    regroupees dans un document PDF unique.
                </p>

                <form action="{{ route('cards.generate-all-global') }}" method="POST" id="globalGenerationForm"
                      style="margin-block-start:var(--acid-space-5);">
                    @csrf
                    <button type="submit" class="acid-btn acid-btn--secondary acid-btn--lg">
                        <i class="fas fa-file-pdf" aria-hidden="true"></i>
                        Generer toutes les cartes
                    </button>
                </form>
            </div>

            <div class="acid-row" style="gap:var(--acid-space-3);flex:0 1 auto;">
                <div class="acid-hero__pill">
                    <span>Classes</span>
                    <strong>{{ $classes->count() }}</strong>
                </div>
                <div class="acid-hero__pill">
                    <span>Eleves</span>
                    <strong>{{ number_format($classes->sum('etudiants_count'), 0, ',', ' ') }}</strong>
                </div>
                <div class="acid-hero__pill">
                    <span>Avec photo</span>
                    <strong>{{ number_format($totalWithPhotos, 0, ',', ' ') }}</strong>
                </div>
            </div>
        </div>
    </section>

    {{-- Recherche --}}
    <div class="acid-card" style="margin-block-end:var(--acid-space-5);">
        <div class="acid-toolbar" style="border-block-end:0;">
            <div class="acid-toolbar__grow">
                <div class="acid-input-group">
                    <i class="fas fa-magnifying-glass acid-input-group__icon" aria-hidden="true"></i>
                    <input type="search" id="searchInput" class="acid-input"
                           placeholder="Rechercher une classe..." autocomplete="off"
                           aria-label="Rechercher une classe">
                </div>
            </div>
            <span class="acid-toolbar__count">
                <strong data-acid-search-count>{{ $classes->count() }}</strong> classe(s)
            </span>
        </div>
    </div>

    {{-- Classes --}}
    <div class="acid-grid acid-grid--cards" id="cardsGrid">
        @forelse ($classes as $classe)
            @php
                $pct = (float) $classe->photo_percentage;
                $complete = $classe->students_without_photos === 0 && $classe->etudiants_count > 0;
            @endphp

            <article class="acid-card acid-card--hover classe-card"
                     data-acid-searchable="{{ $classe->nom }} {{ $classe->annee_scolaire }}">

                <div class="acid-card__header" style="align-items:flex-start;">
                    <span class="acid-stat__icon" aria-hidden="true">
                        <i class="fas fa-chalkboard-user"></i>
                    </span>
                    <div style="min-width:0;">
                        <h2 class="acid-card__title acid-truncate">{{ $classe->nom }}</h2>
                        <p class="acid-card__subtitle">{{ $classe->annee_scolaire }}</p>
                    </div>
                    <span class="acid-badge {{ $complete ? 'acid-badge--teal' : 'acid-badge--amber' }}"
                          style="margin-inline-start:auto;">
                        <span class="acid-badge__dot" aria-hidden="true"></span>
                        {{ $complete ? 'Complet' : 'En cours' }}
                    </span>
                </div>

                <div class="acid-card__body">
                    {{-- Effectifs --}}
                    <div class="acid-form-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));
                                                       gap:var(--acid-space-2);text-align:center;
                                                       margin-block-end:var(--acid-space-4);">
                        <div>
                            <div class="acid-stat__value" style="font-size:var(--acid-text-xl);">
                                {{ $classe->etudiants_count }}
                            </div>
                            <span class="acid-stat__label">Eleves</span>
                        </div>
                        <div>
                            <div class="acid-stat__value" style="font-size:var(--acid-text-xl);color:var(--acid-teal-700);">
                                {{ $classe->students_with_photos }}
                            </div>
                            <span class="acid-stat__label">Photos</span>
                        </div>
                        <div>
                            <div class="acid-stat__value" style="font-size:var(--acid-text-xl);color:var(--acid-coral-600);">
                                {{ $classe->students_without_photos }}
                            </div>
                            <span class="acid-stat__label">Manquantes</span>
                        </div>
                    </div>

                    {{-- Progression --}}
                    <div class="acid-row acid-row--between" style="margin-block-end:var(--acid-space-2);">
                        <span class="acid-stat__label">Couverture photo</span>
                        <strong style="font-size:var(--acid-text-sm);color:var(--acid-teal-700);">
                            {{ number_format($pct, 0) }}%
                        </strong>
                    </div>
                    <div class="acid-progress" role="progressbar" aria-valuenow="{{ $pct }}"
                         aria-valuemin="0" aria-valuemax="100"
                         aria-label="Couverture photo de {{ $classe->nom }}">
                        <div class="acid-progress__bar" style="width:{{ $pct }}%;"></div>
                    </div>

                    @if ($classe->students_without_photos > 0)
                        <a href="{{ route('classes.show', $classe) }}"
                           class="acid-btn acid-btn--outline-coral acid-btn--sm acid-btn--block"
                           style="margin-block-start:var(--acid-space-4);">
                            <i class="fas fa-camera-retro" aria-hidden="true"></i>
                            Completer les photos manquantes
                        </a>
                    @endif
                </div>

                {{-- Documents --}}
                <div class="acid-card__footer" style="flex-direction:column;align-items:stretch;
                            gap:var(--acid-space-2);">

                    <form action="{{ route('cards.generate', $classe) }}" method="POST">
                        @csrf
                        <button type="submit" class="acid-btn acid-btn--primary acid-btn--sm acid-btn--block">
                            <i class="fas fa-id-card" aria-hidden="true"></i>
                            Cartes scolaires
                        </button>
                    </form>

                    <div class="acid-row" style="gap:var(--acid-space-2);flex-wrap:nowrap;">
                        <form action="{{ route('cards.generate-processed-photos', $classe) }}" method="POST"
                              style="flex:1 1 0;">
                            @csrf
                            <button type="submit" class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--block"
                                    title="Archive ZIP des photos labellisees">
                                <i class="fas fa-images" aria-hidden="true"></i>
                                Photos ZIP
                            </button>
                        </form>

                        <form action="{{ route('cards.generate-printable-photos', $classe) }}" method="POST"
                              style="flex:1 1 0;">
                            @csrf
                            <button type="submit" class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--block"
                                    title="Planche de photos au format 4x4">
                                <i class="fas fa-print" aria-hidden="true"></i>
                                Photos 4x4
                            </button>
                        </form>
                    </div>

                    <form action="{{ route('cards.generate-all', $classe) }}" method="POST">
                        @csrf
                        <button type="submit" class="acid-btn acid-btn--outline-teal acid-btn--sm acid-btn--block">
                            <i class="fas fa-file-zipper" aria-hidden="true"></i>
                            Tout generer (ZIP)
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div style="grid-column:1/-1;">
                <div class="acid-card">
                    <div class="acid-empty">
                        <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-id-card"></i></span>
                        <h2 class="acid-empty__title">Aucune classe a traiter</h2>
                        <p class="acid-empty__text">
                            Creez une classe et importez ses eleves pour pouvoir generer
                            cartes et documents.
                        </p>
                        <div class="acid-empty__actions">
                            <a href="{{ route('classes.create') }}" class="acid-btn acid-btn--primary">
                                <i class="fas fa-plus" aria-hidden="true"></i> Creer une classe
                            </a>
                            <a href="{{ route('import.index') }}" class="acid-btn acid-btn--neutral">
                                <i class="fas fa-file-import" aria-hidden="true"></i> Importer une liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="acid-card" id="cardsNoResults" hidden>
        <div class="acid-empty">
            <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-magnifying-glass"></i></span>
            <h2 class="acid-empty__title">Aucune classe ne correspond</h2>
            <p class="acid-empty__text">Essayez un autre nom de classe.</p>
        </div>
    </div>

    {{-- Legende des documents --}}
    <h2 class="acid-section-title">Documents disponibles</h2>

    <div class="acid-grid acid-grid--cards">
        <div class="acid-card" style="padding:var(--acid-space-5);">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-id-card"></i></span>
            <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">Cartes scolaires</h3>
            <p class="acid-card__subtitle" style="margin:0;">
                PDF prerempli avec une carte par eleve, prete a decouper.
            </p>
        </div>

        <div class="acid-card" style="padding:var(--acid-space-5);">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                <i class="fas fa-images"></i>
            </span>
            <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">Photos labellisees</h3>
            <p class="acid-card__subtitle" style="margin:0;">
                Archive ZIP de photos normalisees et annotees pour les fiches d'examen.
            </p>
        </div>

        <div class="acid-card" style="padding:var(--acid-space-5);">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-sky-50);color:var(--acid-sky-600);">
                <i class="fas fa-print"></i>
            </span>
            <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">Photos 4x4</h3>
            <p class="acid-card__subtitle" style="margin:0;">
                PDF optimise pour le tirage au format 4x4 chez le photographe.
            </p>
        </div>

        <div class="acid-card" style="padding:var(--acid-space-5);">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-coral-50);color:var(--acid-coral-700);">
                <i class="fas fa-file-zipper"></i>
            </span>
            <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">Tout generer</h3>
            <p class="acid-card__subtitle" style="margin:0;">
                Archive complete regroupant l'ensemble des documents de la classe.
            </p>
        </div>
    </div>

    @push('scripts')
        <script>
            /* Filtre local des classes */
            (function () {
                const input = document.getElementById('searchInput');
                if (!input) return;

                const cards     = Array.from(document.querySelectorAll('.classe-card'));
                const grid      = document.getElementById('cardsGrid');
                const noResults = document.getElementById('cardsNoResults');
                const counters  = document.querySelectorAll('[data-acid-search-count]');

                const normalize = (v) => (v || '').toString()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .toLowerCase().trim();

                function filter() {
                    const needle = normalize(input.value);
                    let visible = 0;

                    cards.forEach((card) => {
                        const match = !needle ||
                            normalize(card.getAttribute('data-acid-searchable')).includes(needle);
                        card.hidden = !match;
                        if (match) visible++;
                    });

                    counters.forEach((el) => { el.textContent = visible; });
                    noResults.hidden = !(needle && visible === 0);
                    if (grid) grid.hidden = Boolean(needle) && visible === 0;
                }

                let timer = null;
                input.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(filter, 120);
                });
            })();

            /* Retour visuel pendant la generation (operations longues) */
            document.querySelectorAll('form[action*="cards/"]').forEach((form) => {
                form.addEventListener('submit', function () {
                    window.AcidUI.notify(
                        'Generation lancee. Le telechargement demarrera automatiquement.',
                        'info',
                        6000
                    );
                });
            });
        </script>
    @endpush
</x-app-layout>
