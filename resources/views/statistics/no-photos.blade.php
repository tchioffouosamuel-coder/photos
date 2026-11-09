<x-app-layout>

    <nav aria-label="Fil d'Ariane">
        <ol class="acid-breadcrumb">
            <li><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
            <li><a href="{{ route('statistics.index') }}">Statistiques</a></li>
            <li aria-current="page">Photos manquantes</li>
        </ol>
    </nav>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Pilotage</span>
            <h1 class="acid-page-header__title">Eleves sans photo</h1>
            <p class="acid-page-header__lead">
                Liste de travail pour les seances de photographie : chaque ligne permet
                d'ouvrir directement la prise de vue.
            </p>
        </div>
        <div class="acid-page-header__actions">
            <button type="button" class="acid-btn acid-btn--neutral" onclick="window.print()">
                <i class="fas fa-print" aria-hidden="true"></i>
                Imprimer la liste
            </button>
            <a href="{{ route('statistics.index') }}" class="acid-btn acid-btn--ghost">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Retour aux statistiques
            </a>
        </div>
    </div>

    {{-- Indicateurs --}}
    <div class="acid-grid acid-grid--stats" style="margin-block-end:var(--acid-space-5);">
        <article class="acid-stat {{ $totalWithoutPhotos > 0 ? 'acid-stat--coral' : 'acid-stat--teal' }}">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-user-slash"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Photos manquantes</span>
                <span class="acid-stat__value">{{ number_format($totalWithoutPhotos, 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">Eleves a photographier</span>
            </div>
        </article>

        <article class="acid-stat">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-school"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Classes concernees</span>
                <span class="acid-stat__value">{{ number_format($classesAffected, 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">A planifier</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--sky">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-mars"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Garcons</span>
                <span class="acid-stat__value">{{ number_format($boysWithoutPhotos, 0, ',', ' ') }}</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--coral">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-venus"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Filles</span>
                <span class="acid-stat__value">{{ number_format($girlsWithoutPhotos, 0, ',', ' ') }}</span>
            </div>
        </article>
    </div>

    {{-- Liste --}}
    <section class="acid-card">
        <div class="acid-card__header">
            <div>
                <h2 class="acid-card__title">Liste detaillee</h2>
                <p class="acid-card__subtitle">Cliquez sur &laquo; Photographier &raquo; pour ouvrir la camera</p>
            </div>
            <div class="acid-card__actions">
                <span class="acid-badge acid-badge--navy" id="countDisplay">
                    {{ $totalWithoutPhotos }} eleves
                </span>
            </div>
        </div>

        <div class="acid-toolbar acid-no-print">
            <div class="acid-toolbar__grow">
                <div class="acid-input-group">
                    <i class="fas fa-magnifying-glass acid-input-group__icon" aria-hidden="true"></i>
                    <input type="search" id="searchInput" class="acid-input"
                           placeholder="Rechercher par nom, classe ou matricule..."
                           autocomplete="off" aria-label="Rechercher un eleve">
                </div>
            </div>

            <div style="flex:0 0 13rem;">
                <select id="filterClasse" class="acid-select" aria-label="Filtrer par classe">
                    <option value="">Toutes les classes</option>
                    @foreach ($classes as $classe)
                        <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex:0 0 11rem;">
                <select id="filterSexe" class="acid-select" aria-label="Filtrer par sexe">
                    <option value="">Tous les sexes</option>
                    <option value="M">Garcons</option>
                    <option value="F">Filles</option>
                </select>
            </div>
        </div>

        <div class="acid-table-wrap" id="tableContainer">
            <table class="acid-table acid-table--tiles">
                <thead>
                    <tr>
                        <th scope="col" class="acid-table__num">N&deg;</th>
                        <th scope="col">Classe</th>
                        <th scope="col">Matricule</th>
                        <th scope="col">Eleve</th>
                        <th scope="col">Naissance</th>
                        <th scope="col">Sexe</th>
                        <th scope="col" class="acid-table__actions acid-no-print">Action</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    @forelse ($students as $index => $etudiant)
                        <tr class="student-row"
                            data-nom="{{ strtolower($etudiant->nom . ' ' . $etudiant->prenoms) }}"
                            data-classe="{{ $etudiant->classe->id ?? '' }}"
                            data-classe-nom="{{ strtolower($etudiant->classe->nom ?? '') }}"
                            data-matricule="{{ strtolower($etudiant->matricule_national ?? '') }}"
                            data-sexe="{{ $etudiant->sexe }}">

                            <td data-label="N&deg;" data-cell="rank" class="acid-table__num">{{ $index + 1 }}</td>

                            <td data-label="Classe" data-cell="meta">
                                @if ($etudiant->classe)
                                    <a href="{{ route('classes.show', $etudiant->classe) }}"
                                       class="acid-table__primary">
                                        {{ $etudiant->classe->nom }}
                                    </a>
                                @else
                                    <span class="acid-text-subtle">—</span>
                                @endif
                            </td>

                            <td data-label="Matricule" data-cell="meta">
                                <code class="acid-text-muted">{{ $etudiant->matricule_national ?: '—' }}</code>
                            </td>

                            <td data-label="Eleve" data-cell="identity">
                                <div class="acid-identity">
                                    <span class="acid-avatar acid-avatar--sky" aria-hidden="true">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <span class="acid-identity__text">
                                        <strong>{{ strtoupper($etudiant->nom) }}</strong>
                                        <span>{{ $etudiant->prenoms }}</span>
                                    </span>
                                </div>
                            </td>

                            <td data-label="Naissance" data-cell="meta" class="acid-num">
                                {{ $etudiant->date_naissance ? $etudiant->date_naissance->format('d/m/Y') : '—' }}
                            </td>

                            <td data-label="Sexe" data-cell="meta">
                                @if ($etudiant->sexe === 'M')
                                    <span class="acid-badge acid-badge--sky">
                                        <i class="fas fa-mars" aria-hidden="true"></i> M
                                    </span>
                                @else
                                    <span class="acid-badge acid-badge--coral">
                                        <i class="fas fa-venus" aria-hidden="true"></i> F
                                    </span>
                                @endif
                            </td>

                            <td data-label="Action" data-cell="actions" class="acid-table__actions acid-no-print">
                                <button type="button" class="acid-btn acid-btn--secondary acid-btn--sm"
                                        onclick="openPhotoModal({{ $etudiant->id }}, @js($etudiant->nom), @js($etudiant->prenoms), '', @js($etudiant->classe->nom ?? ''))">
                                    <i class="fas fa-camera-retro" aria-hidden="true"></i>
                                    Photographier
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="acid-empty acid-empty--teal">
                                    <span class="acid-empty__icon" aria-hidden="true">
                                        <i class="fas fa-circle-check"></i>
                                    </span>
                                    <h3 class="acid-empty__title">Campagne complete</h3>
                                    <p class="acid-empty__text">
                                        Tous les eleves enregistres disposent d'une photo.
                                        Vous pouvez generer les cartes et les documents d'examen.
                                    </p>
                                    <div class="acid-empty__actions">
                                        <a href="{{ route('cards.index') }}" class="acid-btn acid-btn--primary">
                                            <i class="fas fa-id-card" aria-hidden="true"></i>
                                            Generer les documents
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="noResults" hidden>
            <div class="acid-empty">
                <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-magnifying-glass"></i></span>
                <h3 class="acid-empty__title">Aucun eleve ne correspond</h3>
                <p class="acid-empty__text">Modifiez votre recherche ou reinitialisez les filtres.</p>
                <div class="acid-empty__actions">
                    <button type="button" class="acid-btn acid-btn--neutral" id="resetFilters">
                        <i class="fas fa-rotate-left" aria-hidden="true"></i> Reinitialiser
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Aide --}}
    <h2 class="acid-section-title acid-no-print">Deroulement d'une seance</h2>

    <div class="acid-grid acid-grid--cards acid-no-print">
        <div class="acid-card" style="padding:var(--acid-space-5);">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-hand-pointer"></i></span>
            <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">
                1. Ouvrir la prise de vue
            </h3>
            <p class="acid-card__subtitle" style="margin:0;">
                Bouton &laquo; Photographier &raquo; sur la ligne de l'eleve.
            </p>
        </div>

        <div class="acid-card" style="padding:var(--acid-space-5);">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                <i class="fas fa-camera"></i>
            </span>
            <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">
                2. Capturer ou importer
            </h3>
            <p class="acid-card__subtitle" style="margin:0;">
                Camera du poste ou du telephone, ou image existante de la galerie.
            </p>
        </div>

        <div class="acid-card" style="padding:var(--acid-space-5);">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-sky-50);color:var(--acid-sky-600);">
                <i class="fas fa-check"></i>
            </span>
            <h3 class="acid-card__title" style="margin:var(--acid-space-3) 0 .25rem;">
                3. Valider
            </h3>
            <p class="acid-card__subtitle" style="margin:0;">
                La photo est recadree au format carre et associee a l'eleve.
            </p>
        </div>
    </div>

    {{-- Module partage de prise de vue (modale camera + galerie) --}}
    @include('partials.photo-capture')

    @push('scripts')
        <script>
            /* Filtres de la liste des eleves sans photo */
            (function () {
                const search  = document.getElementById('searchInput');
                const fClasse = document.getElementById('filterClasse');
                const fSexe   = document.getElementById('filterSexe');
                const rows    = Array.from(document.querySelectorAll('.student-row'));
                const count   = document.getElementById('countDisplay');
                const empty   = document.getElementById('noResults');
                const table   = document.getElementById('tableContainer');
                const reset   = document.getElementById('resetFilters');

                if (!search || !rows.length) return;

                const normalize = (v) => (v || '').toString()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .toLowerCase().trim();

                function apply() {
                    const needle = normalize(search.value);
                    const classe = fClasse.value;
                    const sexe   = fSexe.value;
                    let visible = 0;

                    rows.forEach((row) => {
                        const haystack = normalize(
                            row.dataset.nom + ' ' + row.dataset.classeNom + ' ' + row.dataset.matricule
                        );

                        const match =
                            (!needle || haystack.includes(needle)) &&
                            (!classe || row.dataset.classe === classe) &&
                            (!sexe   || row.dataset.sexe === sexe);

                        row.hidden = !match;
                        if (match) visible++;
                    });

                    count.textContent = visible + (visible === 1 ? ' eleve' : ' eleves');
                    empty.hidden = visible > 0;
                    if (table) table.hidden = visible === 0;
                }

                let timer = null;
                search.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(apply, 120);
                });
                [fClasse, fSexe].forEach((el) => el.addEventListener('change', apply));

                if (reset) {
                    reset.addEventListener('click', () => {
                        search.value = '';
                        fClasse.value = '';
                        fSexe.value = '';
                        apply();
                    });
                }
            })();
        </script>
    @endpush
</x-app-layout>
