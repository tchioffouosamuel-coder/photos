<x-app-layout>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Gestion scolaire</span>
            <h1 class="acid-page-header__title">Classes &amp; eleves</h1>
            <p class="acid-page-header__lead">
                Organisez vos classes, suivez leurs effectifs et accedez a la prise de vue.
            </p>
        </div>
        <div class="acid-page-header__actions">
            <a href="{{ route('import.index') }}" class="acid-btn acid-btn--neutral">
                <i class="fas fa-file-import" aria-hidden="true"></i>
                Importer une liste
            </a>
            <a href="{{ route('classes.create') }}" class="acid-btn acid-btn--primary">
                <i class="fas fa-plus" aria-hidden="true"></i>
                Nouvelle classe
            </a>
        </div>
    </div>

    {{-- Indicateurs --}}
    <div class="acid-grid acid-grid--stats" style="margin-block-end:var(--acid-space-6);">
        <article class="acid-stat">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-school"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Classes</span>
                <span class="acid-stat__value">{{ $classes->count() }}</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--teal">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-users"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Eleves</span>
                <span class="acid-stat__value">{{ number_format($classes->sum('etudiants_count'), 0, ',', ' ') }}</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--sky">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-mars"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Garcons</span>
                <span class="acid-stat__value">{{ number_format($classes->sum('effectif_garcons'), 0, ',', ' ') }}</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--coral">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-venus"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Filles</span>
                <span class="acid-stat__value">{{ number_format($classes->sum('effectif_filles'), 0, ',', ' ') }}</span>
            </div>
        </article>
    </div>

    {{-- Recherche locale --}}
    <div class="acid-card" style="margin-block-end:var(--acid-space-5);">
        <div class="acid-toolbar" style="border-block-end:0;">
            <div class="acid-toolbar__grow">
                <div class="acid-input-group">
                    <i class="fas fa-magnifying-glass acid-input-group__icon" aria-hidden="true"></i>
                    <input type="search" id="classeSearch" class="acid-input"
                           placeholder="Rechercher par nom, annee scolaire ou examen..."
                           autocomplete="off" aria-label="Rechercher une classe">
                </div>
            </div>
            <span class="acid-toolbar__count" id="classeSearchInfo" hidden>
                <strong id="classeSearchCount">0</strong> classe(s) trouvee(s)
            </span>
        </div>
    </div>

    {{-- Grille des classes --}}
    <div class="acid-grid acid-grid--cards" id="classesGrid">
        @forelse ($classes as $classe)
            @php
                $effectif = (int) $classe->etudiants_count;
            @endphp

            <article class="acid-card acid-card--hover classe-card"
                     data-acid-searchable="{{ $classe->nom }} {{ $classe->annee_scolaire }} {{ $classe->exam ?? '' }}">

                <div class="acid-card__header" style="align-items:flex-start;">
                    <span class="acid-stat__icon" aria-hidden="true">
                        <i class="fas fa-chalkboard-user"></i>
                    </span>
                    <div style="min-width:0;">
                        <h2 class="acid-card__title acid-truncate">{{ $classe->nom }}</h2>
                        <p class="acid-card__subtitle">{{ $classe->annee_scolaire }}</p>
                    </div>
                    @if ($classe->exam)
                        <span class="acid-badge acid-badge--amber" style="margin-inline-start:auto;"
                              title="Examen officiel : {{ $classe->exam }}">
                            <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                            {{ $classe->exam }}
                        </span>
                    @endif
                </div>

                <div class="acid-card__body">
                    <div class="acid-form-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));
                                                       gap:var(--acid-space-2);text-align:center;">
                        <div>
                            <div class="acid-stat__value" style="font-size:var(--acid-text-xl);">{{ $effectif }}</div>
                            <span class="acid-stat__label">Total</span>
                        </div>
                        <div>
                            <div class="acid-stat__value" style="font-size:var(--acid-text-xl);color:var(--acid-sky-600);">
                                {{ $classe->effectif_garcons }}
                            </div>
                            <span class="acid-stat__label">Garcons</span>
                        </div>
                        <div>
                            <div class="acid-stat__value" style="font-size:var(--acid-text-xl);color:var(--acid-coral-600);">
                                {{ $classe->effectif_filles }}
                            </div>
                            <span class="acid-stat__label">Filles</span>
                        </div>
                    </div>
                </div>

                <div class="acid-card__footer" style="flex-wrap:wrap;">
                    <a href="{{ route('classes.show', $classe->id) }}"
                       class="acid-btn acid-btn--primary acid-btn--sm" style="flex:1 1 auto;">
                        <i class="fas fa-camera-retro" aria-hidden="true"></i>
                        Eleves &amp; photos
                    </a>

                    <a href="{{ route('classes.edit', $classe) }}"
                       class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon"
                       title="Modifier la classe" aria-label="Modifier la classe">
                        <i class="fas fa-pen" aria-hidden="true"></i>
                    </a>

                    <form action="{{ route('classes.destroy', $classe) }}" method="POST"
                          data-acid-confirm="Supprimer la classe {{ $classe->nom }} et tous ses eleves ? Cette action est irreversible."
                          style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="acid-btn acid-btn--outline-coral acid-btn--sm acid-btn--icon"
                                title="Supprimer la classe" aria-label="Supprimer la classe">
                            <i class="fas fa-trash-can" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div style="grid-column:1/-1;">
                <div class="acid-card">
                    <div class="acid-empty">
                        <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-school"></i></span>
                        <h2 class="acid-empty__title">Aucune classe enregistree</h2>
                        <p class="acid-empty__text">
                            Creez votre premiere classe, ou importez directement une liste d'eleves
                            depuis un fichier Excel.
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

    {{-- Aucun resultat de recherche --}}
    <div class="acid-card" id="classeNoResults" hidden>
        <div class="acid-empty">
            <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-magnifying-glass"></i></span>
            <h2 class="acid-empty__title">Aucune classe ne correspond</h2>
            <p class="acid-empty__text">Essayez un autre nom, une autre annee scolaire ou un autre examen.</p>
        </div>
    </div>

    @push('scripts')
        <script>
            /* Filtre local de la grille des classes */
            (function () {
                const input = document.getElementById('classeSearch');
                if (!input) return;

                const cards     = Array.from(document.querySelectorAll('.classe-card'));
                const grid      = document.getElementById('classesGrid');
                const noResults = document.getElementById('classeNoResults');
                const info      = document.getElementById('classeSearchInfo');
                const count     = document.getElementById('classeSearchCount');

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

                    info.hidden = !needle;
                    count.textContent = visible;
                    noResults.hidden = !(needle && visible === 0);
                    if (grid) grid.hidden = Boolean(needle) && visible === 0;
                }

                let timer = null;
                input.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(filter, 120);
                });
            })();
        </script>
    @endpush
</x-app-layout>
