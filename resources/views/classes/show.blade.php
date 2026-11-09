@php
    $withPhotos = $classe->etudiants()->whereNotNull('photo')->where('photo', '!=', '')->count();
    $effectif   = (int) $classe->effectif_total;
    $pctPhotos  = $effectif > 0 ? round(($withPhotos / $effectif) * 100, 1) : 0;
@endphp

<x-app-layout>

    <nav aria-label="Fil d'Ariane">
        <ol class="acid-breadcrumb">
            <li><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
            <li><a href="{{ route('classes.index') }}">Classes</a></li>
            <li aria-current="page">{{ $classe->nom }}</li>
        </ol>
    </nav>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">
                {{ $classe->annee_scolaire }}
                @if ($classe->exam)
                    &middot; Examen {{ $classe->exam }}
                @endif
            </span>
            <h1 class="acid-page-header__title">{{ $classe->nom }}</h1>
            <p class="acid-page-header__lead">
                Effectif, prise de vue et production des documents de la classe.
            </p>
        </div>

        <div class="acid-page-header__actions">
            <a href="{{ route('candidats-libres.index', $classe) }}" class="acid-btn acid-btn--neutral">
                <i class="fas fa-user-tag" aria-hidden="true"></i>
                Candidats libres
            </a>
            <a href="{{ route('classes.edit', $classe) }}" class="acid-btn acid-btn--neutral">
                <i class="fas fa-pen" aria-hidden="true"></i>
                Modifier
            </a>
            <a href="{{ route('etudiants.create', $classe) }}" class="acid-btn acid-btn--secondary">
                <i class="fas fa-user-plus" aria-hidden="true"></i>
                Nouvel eleve
            </a>
            @if ($classe->etudiants->count() > 0)
                <button type="button" class="acid-btn acid-btn--secondary"
                        data-bs-toggle="modal" data-bs-target="#importPhotosModal">
                    <i class="fas fa-images" aria-hidden="true"></i>
                    Importer des photos
                </button>
            @endif
            <form action="{{ route('cards.generate', $classe) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="acid-btn acid-btn--primary">
                    <i class="fas fa-id-card" aria-hidden="true"></i>
                    Generer les cartes
                </button>
            </form>
        </div>
    </div>

    {{-- Indicateurs de la classe --}}
    <div class="acid-grid acid-grid--stats" style="margin-block-end:var(--acid-space-5);">
        <article class="acid-stat">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-users"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Total eleves</span>
                <span class="acid-stat__value">{{ $effectif }}</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--sky">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-mars"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Garcons</span>
                <span class="acid-stat__value">{{ $classe->effectif_garcons }}</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--coral">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-venus"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Filles</span>
                <span class="acid-stat__value">{{ $classe->effectif_filles }}</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--teal">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-camera-retro"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Avec photo</span>
                <span class="acid-stat__value">{{ $withPhotos }}</span>
                <span class="acid-stat__hint">{{ $pctPhotos }}% de l'effectif</span>
            </div>
        </article>
    </div>

    {{-- Progression --}}
    <div class="acid-card" style="margin-block-end:var(--acid-space-5);">
        <div class="acid-card__body acid-card__body--tight">
            <div class="acid-row acid-row--between" style="margin-block-end:var(--acid-space-2);">
                <span class="acid-stat__label">Avancement de la prise de vue</span>
                <span class="acid-badge acid-badge--teal">{{ $withPhotos }} / {{ $effectif }}</span>
            </div>
            <div class="acid-progress" role="progressbar" aria-valuenow="{{ $pctPhotos }}"
                 aria-valuemin="0" aria-valuemax="100" aria-label="Avancement de la prise de vue">
                <div class="acid-progress__bar" style="width:{{ $pctPhotos }}%;"></div>
            </div>
        </div>
    </div>

    {{-- Liste des eleves --}}
    <section class="acid-card">
        <div class="acid-card__header">
            <div>
                <h2 class="acid-card__title">Liste des eleves</h2>
                <p class="acid-card__subtitle">
                    Cliquez sur une photo pour ouvrir la prise de vue
                </p>
            </div>
            <div class="acid-card__actions">
                <span class="acid-badge acid-badge--navy" id="countDisplay">
                    {{ $classe->etudiants->count() }} eleves
                </span>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="acid-toolbar">
            <div class="acid-toolbar__grow">
                <div class="acid-input-group">
                    <i class="fas fa-magnifying-glass acid-input-group__icon" aria-hidden="true"></i>
                    <input type="search" id="searchInput" class="acid-input"
                           placeholder="Rechercher un eleve (nom, matricule...)"
                           autocomplete="off" aria-label="Rechercher un eleve">
                </div>
            </div>

            <div style="flex:0 0 11rem;">
                <select id="filterSexe" class="acid-select" aria-label="Filtrer par sexe">
                    <option value="">Tous les sexes</option>
                    <option value="M">Garcons</option>
                    <option value="F">Filles</option>
                </select>
            </div>

            <div style="flex:0 0 11rem;">
                <select id="filterStatut" class="acid-select" aria-label="Filtrer par statut">
                    <option value="">Tous les statuts</option>
                    <option value="N">Normal</option>
                    <option value="R">Redoublant</option>
                </select>
            </div>

            <div style="flex:0 0 12rem;">
                <select id="filterPhoto" class="acid-select" aria-label="Filtrer par photo">
                    <option value="">Photo : tous</option>
                    <option value="1">Avec photo</option>
                    <option value="0">Sans photo</option>
                </select>
            </div>
        </div>

        {{-- Barre d'actions groupees : apparait des qu'un eleve est selectionne --}}
        @if ($classe->etudiants->count() > 0)
            <div class="acid-bulkbar acid-no-print" id="bulkBar" hidden>
                <span class="acid-bulkbar__count">
                    <i class="fas fa-check-double" aria-hidden="true"></i>
                    <strong id="bulkCount">0</strong> eleve(s) selectionne(s)
                </span>

                <div class="acid-bulkbar__actions">
                    @if ($autresClasses->isNotEmpty())
                        <button type="button" class="acid-btn acid-btn--secondary acid-btn--sm"
                                data-bs-toggle="modal" data-bs-target="#transfertModal">
                            <i class="fas fa-right-left" aria-hidden="true"></i>
                            Transferer vers une autre classe
                        </button>
                    @else
                        <span class="acid-badge acid-badge--amber">
                            <i class="fas fa-circle-info" aria-hidden="true"></i>
                            Creez une autre classe pour pouvoir transferer
                        </span>
                    @endif

                    <button type="button" class="acid-btn acid-btn--ghost acid-btn--sm" id="bulkClear">
                        Annuler la selection
                    </button>
                </div>
            </div>
        @endif

        <div class="acid-table-wrap">
            <table class="acid-table acid-table--stack" id="studentsTable">
                <thead>
                    <tr>
                        <th scope="col" class="acid-table__select acid-no-print">
                            <label class="acid-check" style="margin:0;"
                                   title="Tout selectionner">
                                <input type="checkbox" id="selectAll"
                                       aria-label="Selectionner tous les eleves affiches">
                            </label>
                        </th>
                        <th scope="col" class="acid-table__num">N&deg;</th>
                        <th scope="col">Eleve</th>
                        <th scope="col">Matricules</th>
                        <th scope="col" class="acid-hide-md">Naissance</th>
                        <th scope="col">Sexe</th>
                        <th scope="col" class="acid-hide-md">Statut</th>
                        <th scope="col" class="acid-table__actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classe->etudiants as $index => $etudiant)
                        @php
                            $photoUrl = $etudiant->photo
                                ? photo_url('photos/' . $classe->nom . '/' . $etudiant->photo)
                                : '';
                        @endphp

                        <tr class="student-row"
                            data-nom="{{ strtolower($etudiant->nom . ' ' . $etudiant->prenoms) }}"
                            data-matricule="{{ strtolower($etudiant->matricule_national . ' ' . $etudiant->matricule_interne) }}"
                            data-sexe="{{ $etudiant->sexe }}"
                            data-statut="{{ $etudiant->statut }}"
                            data-photo="{{ $etudiant->photo ? '1' : '0' }}"
                            data-nom-complet="{{ trim(strtoupper($etudiant->nom) . ' ' . $etudiant->prenoms) }}">

                            <td data-label="Selection" class="acid-table__select acid-no-print">
                                <label class="acid-check" style="margin:0;">
                                    <input type="checkbox" class="student-select" value="{{ $etudiant->id }}"
                                           @checked(in_array((string) $etudiant->id, array_map('strval', (array) old('etudiants', [])), true))
                                           aria-label="Selectionner {{ $etudiant->nom }} {{ $etudiant->prenoms }}">
                                </label>
                            </td>

                            <td data-label="N&deg;" class="acid-table__num">{{ $index + 1 }}</td>

                            <td data-label="Eleve">
                                <div class="acid-identity">
                                    <button type="button" class="acid-avatar acid-avatar--navy"
                                            style="border:0;cursor:pointer;padding:0;"
                                            title="Gerer la photo de {{ $etudiant->nom }}"
                                            aria-label="Gerer la photo de {{ $etudiant->nom }} {{ $etudiant->prenoms }}"
                                            onclick="openPhotoModal({{ $etudiant->id }}, @js($etudiant->nom), @js($etudiant->prenoms), @js($photoUrl))">
                                        @if ($etudiant->photo)
                                            <img src="{{ $photoUrl }}" alt=""
                                                 id="student-photo-{{ $etudiant->id }}">
                                        @else
                                            <span id="student-photo-{{ $etudiant->id }}" aria-hidden="true">
                                                <i class="fas fa-camera"></i>
                                            </span>
                                        @endif
                                    </button>

                                    <span class="acid-identity__text">
                                        <strong>{{ strtoupper($etudiant->nom) }}</strong>
                                        <span>{{ $etudiant->prenoms }}</span>
                                    </span>
                                </div>
                            </td>

                            <td data-label="Matricules">
                                <span class="acid-table__primary acid-num">
                                    {{ $etudiant->matricule_national ?: '—' }}
                                </span>
                                <span class="acid-table__meta" style="display:block;">
                                    Interne : {{ $etudiant->matricule_interne ?: '—' }}
                                </span>
                            </td>

                            <td data-label="Naissance" class="acid-hide-md">
                                <span class="acid-num">
                                    {{ $etudiant->date_naissance ? $etudiant->date_naissance->format('d/m/Y') : '—' }}
                                </span>
                                <span class="acid-table__meta" style="display:block;">
                                    {{ $etudiant->lieu_naissance ? strtoupper($etudiant->lieu_naissance) : '—' }}
                                </span>
                            </td>

                            <td data-label="Sexe">
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

                            <td data-label="Statut" class="acid-hide-md">
                                @if ($etudiant->statut === 'R')
                                    <span class="acid-badge acid-badge--amber">Redoublant</span>
                                @else
                                    <span class="acid-badge acid-badge--teal">Normal</span>
                                @endif
                            </td>

                            <td data-label="Actions" class="acid-table__actions">
                                <div class="acid-btn-group" style="justify-content:flex-end;">
                                    <button type="button" class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon"
                                            title="Photo de l'eleve" aria-label="Photo de l'eleve"
                                            onclick="openPhotoModal({{ $etudiant->id }}, @js($etudiant->nom), @js($etudiant->prenoms), @js($photoUrl))">
                                        <i class="fas fa-camera-retro" aria-hidden="true"></i>
                                    </button>

                                    <a href="{{ route('etudiants.edit', [$classe, $etudiant]) }}"
                                       class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon"
                                       title="Modifier l'eleve" aria-label="Modifier l'eleve">
                                        <i class="fas fa-pen" aria-hidden="true"></i>
                                    </a>

                                    @if ($autresClasses->isNotEmpty())
                                        <button type="button"
                                                class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon js-transfert-un"
                                                data-etudiant-id="{{ $etudiant->id }}"
                                                title="Transferer vers une autre classe"
                                                aria-label="Transferer {{ $etudiant->nom }} vers une autre classe">
                                            <i class="fas fa-right-left" aria-hidden="true"></i>
                                        </button>
                                    @endif

                                    <form action="{{ route('etudiants.destroy', [$classe, $etudiant]) }}"
                                          method="POST" style="display:inline;"
                                          data-acid-confirm="Supprimer definitivement {{ $etudiant->nom }} {{ $etudiant->prenoms }} ?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="acid-btn acid-btn--outline-coral acid-btn--sm acid-btn--icon"
                                                title="Supprimer l'eleve" aria-label="Supprimer l'eleve">
                                            <i class="fas fa-trash-can" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="acid-empty">
                                    <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-users"></i></span>
                                    <h3 class="acid-empty__title">Aucun eleve dans cette classe</h3>
                                    <p class="acid-empty__text">
                                        Ajoutez les eleves un par un, ou importez la liste complete
                                        depuis un fichier Excel.
                                    </p>
                                    <div class="acid-empty__actions">
                                        <a href="{{ route('etudiants.create', $classe) }}" class="acid-btn acid-btn--primary">
                                            <i class="fas fa-user-plus" aria-hidden="true"></i> Ajouter un eleve
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

        <div id="studentsNoResults" hidden>
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

    {{-- Production de documents --}}
    @if ($classe->etudiants->count() > 0)
        <h2 class="acid-section-title">Production de documents</h2>

        <div class="acid-grid acid-grid--cards">
            <form action="{{ route('cards.generate', $classe) }}" method="POST">
                @csrf
                <button type="submit" class="acid-card acid-card--hover"
                        style="width:100%;text-align:start;padding:var(--acid-space-5);
                               display:flex;gap:var(--acid-space-4);align-items:flex-start;cursor:pointer;">
                    <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-id-card"></i></span>
                    <span>
                        <strong style="display:block;color:var(--acid-text-strong);font-weight:var(--acid-fw-bold);">
                            Cartes scolaires
                        </strong>
                        <span class="acid-stat__hint">Planche de cartes prete a imprimer</span>
                    </span>
                </button>
            </form>

            <form action="{{ route('cards.generate-processed-photos', $classe) }}" method="POST">
                @csrf
                <button type="submit" class="acid-card acid-card--hover"
                        style="width:100%;text-align:start;padding:var(--acid-space-5);
                               display:flex;gap:var(--acid-space-4);align-items:flex-start;cursor:pointer;">
                    <span class="acid-stat__icon" aria-hidden="true"
                          style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                        <i class="fas fa-crop-simple"></i>
                    </span>
                    <span>
                        <strong style="display:block;color:var(--acid-text-strong);font-weight:var(--acid-fw-bold);">
                            Photos labellisees
                        </strong>
                        <span class="acid-stat__hint">Photos normalisees pour les fiches d'examen</span>
                    </span>
                </button>
            </form>

            <form action="{{ route('cards.generate-printable-photos', $classe) }}" method="POST">
                @csrf
                <button type="submit" class="acid-card acid-card--hover"
                        style="width:100%;text-align:start;padding:var(--acid-space-5);
                               display:flex;gap:var(--acid-space-4);align-items:flex-start;cursor:pointer;">
                    <span class="acid-stat__icon" aria-hidden="true"
                          style="background:var(--acid-sky-50);color:var(--acid-sky-600);">
                        <i class="fas fa-print"></i>
                    </span>
                    <span>
                        <strong style="display:block;color:var(--acid-text-strong);font-weight:var(--acid-fw-bold);">
                            Planches imprimables
                        </strong>
                        <span class="acid-stat__hint">Photos disposees pour le tirage</span>
                    </span>
                </button>
            </form>

            <form action="{{ route('cards.generate-all', $classe) }}" method="POST">
                @csrf
                <button type="submit" class="acid-card acid-card--hover"
                        style="width:100%;text-align:start;padding:var(--acid-space-5);
                               display:flex;gap:var(--acid-space-4);align-items:flex-start;cursor:pointer;">
                    <span class="acid-stat__icon" aria-hidden="true"
                          style="background:var(--acid-coral-50);color:var(--acid-coral-700);">
                        <i class="fas fa-file-zipper"></i>
                    </span>
                    <span>
                        <strong style="display:block;color:var(--acid-text-strong);font-weight:var(--acid-fw-bold);">
                            Tout generer
                        </strong>
                        <span class="acid-stat__hint">Archive complete de la classe</span>
                    </span>
                </button>
            </form>
        </div>
    @endif

    {{-- ================== TRANSFERT VERS UNE AUTRE CLASSE ================== --}}
    @if ($autresClasses->isNotEmpty())
        <div class="modal fade" id="transfertModal" tabindex="-1"
             aria-labelledby="transfertModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('etudiants.transfert', $classe) }}" id="transfertForm">
                        @csrf

                        <div class="modal-header">
                            <span class="acid-modal-icon" aria-hidden="true">
                                <i class="fas fa-right-left"></i>
                            </span>
                            <div>
                                <h2 class="modal-title" id="transfertModalLabel">
                                    Transferer des eleves
                                </h2>
                                <p style="margin:.125rem 0 0;font-size:var(--acid-text-sm);color:var(--acid-text-muted);">
                                    Depuis la classe {{ $classe->nom }}
                                </p>
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"
                                    aria-label="Fermer"></button>
                        </div>

                        <div class="modal-body">

                            {{-- Recapitulatif de la selection --}}
                            <div class="acid-card acid-card--soft" style="padding:var(--acid-space-4);
                                        margin-block-end:var(--acid-space-5);">
                                <div class="acid-row acid-row--between" style="margin-block-end:var(--acid-space-2);">
                                    <span class="acid-stat__label">Eleves selectionnes</span>
                                    <span class="acid-badge acid-badge--solid-navy" id="transfertCount">0</span>
                                </div>
                                <p class="acid-hint" style="margin:0;max-height:7rem;overflow-y:auto;"
                                   id="transfertNoms"></p>
                            </div>

                            {{-- Classe de destination --}}
                            <div class="acid-field">
                                <label for="classe_destination_id" class="acid-label acid-label--required">
                                    Classe de destination
                                </label>
                                <select id="classe_destination_id" name="classe_destination_id" required
                                        class="acid-select @error('classe_destination_id') is-invalid @enderror">
                                    <option value="">Selectionnez une classe</option>
                                    @foreach ($autresClasses as $destination)
                                        <option value="{{ $destination->id }}"
                                                @selected(old('classe_destination_id') == $destination->id)>
                                            {{ $destination->nom }} &mdash; {{ $destination->annee_scolaire }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('classe_destination_id')
                                    <div class="acid-error" role="alert">
                                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                        <span>{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>

                            <div class="acid-alert acid-alert--navy">
                                <i class="fas fa-circle-info acid-alert__icon" aria-hidden="true"></i>
                                <div class="acid-alert__body">
                                    <p class="acid-alert__title">Ce que fait le transfert</p>
                                    <ul style="margin:0;padding-inline-start:1.125rem;font-size:var(--acid-text-sm);">
                                        <li>Les eleves changent de classe, leur fiche est conservee.</li>
                                        <li>Leurs photos sont deplacees dans le dossier de la classe d'arrivee.</li>
                                        <li>Les effectifs des deux classes sont recalcules automatiquement.</li>
                                    </ul>
                                </div>
                            </div>

                            {{-- Les identifiants selectionnes sont injectes ici --}}
                            <div id="transfertInputs" hidden></div>

                            <div class="acid-error d-none" id="transfertError" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                <span>Selectionnez au moins un eleve a transferer.</span>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="acid-btn acid-btn--neutral" data-bs-dismiss="modal">
                                Annuler
                            </button>
                            <button type="submit" class="acid-btn acid-btn--primary" id="transfertSubmit">
                                <i class="fas fa-right-left" aria-hidden="true"></i>
                                Transferer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ================== IMPORT GROUPE DE PHOTOS ================== --}}
    @if ($classe->etudiants->count() > 0)
        @php
            /*
             | Un exemple pris dans la classe rend la convention de nommage
             | concrete : l'utilisateur voit a quoi doit ressembler son fichier.
             */
            $exempleEleve = $classe->etudiants->first(fn ($e) => filled($e->matricule_national))
                ?? $classe->etudiants->first();
            $exempleCle = filled($exempleEleve->matricule_national)
                ? $exempleEleve->matricule_national
                : trim($exempleEleve->nom . '_' . $exempleEleve->prenoms);
        @endphp

        <div class="modal fade" id="importPhotosModal" tabindex="-1"
             aria-labelledby="importPhotosLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title" id="importPhotosLabel">
                                <i class="fas fa-images" aria-hidden="true"></i>
                                Importer des photos en lot
                            </h2>
                            <p class="acid-stat__hint" style="margin:0.25rem 0 0;">
                                Les photos sont rattachees automatiquement d'apres le nom du fichier.
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Fermer"></button>
                    </div>

                    <div class="modal-body">
                        {{-- Rappel de la convention de nommage --}}
                        <div class="acid-alert acid-alert--navy" style="margin-block-end:var(--acid-space-5);">
                            <span class="acid-alert__icon" aria-hidden="true"><i class="fas fa-circle-info"></i></span>
                            <div class="acid-alert__body">
                                <p class="acid-alert__title">Comment nommer les fichiers</p>
                                <p style="margin:0;">
                                    Le nom du fichier doit contenir le matricule <em>ou</em> le nom et les
                                    prenoms de l'eleve. Les accents, la casse, les espaces, les tirets et
                                    les underscores sont ignores.
                                </p>
                                <p style="margin:var(--acid-space-2) 0 0;font-size:var(--acid-text-sm);">
                                    Exemple pour cette classe :
                                    <code>{{ $exempleCle }}.jpg</code>
                                </p>
                            </div>
                        </div>

                        {{-- Zone de depot --}}
                        <input type="file" id="importPhotosInput" accept="image/jpeg,image/png"
                               multiple hidden>

                        <label class="acid-dropzone" id="importDropzone" for="importPhotosInput">
                            <span class="acid-dropzone__icon" aria-hidden="true">
                                <i class="fas fa-cloud-arrow-up"></i>
                            </span>
                            <span class="acid-dropzone__title">
                                Deposez vos photos ici ou cliquez pour les choisir
                            </span>
                            <span class="acid-dropzone__hint">
                                JPG ou PNG, 10 Mo maximum par photo. Plusieurs fichiers a la fois.
                            </span>
                        </label>

                        {{-- Apercu de la selection --}}
                        <div id="importSelection" hidden style="margin-block-start:var(--acid-space-5);">
                            <div style="display:flex;align-items:center;gap:var(--acid-space-3);
                                        margin-block-end:var(--acid-space-3);">
                                <span class="acid-badge acid-badge--solid-navy">
                                    <span id="importSelectionCount">0</span> fichier(s)
                                </span>
                                <button type="button" class="acid-btn acid-btn--ghost acid-btn--sm"
                                        id="importSelectionClear">
                                    <i class="fas fa-xmark" aria-hidden="true"></i> Vider la selection
                                </button>
                            </div>
                            <div class="acid-filegrid" id="importFileGrid"></div>
                        </div>

                        {{-- Option d'ecrasement --}}
                        <label class="acid-check" style="margin-block-start:var(--acid-space-5);">
                            <input type="checkbox" id="importRemplacer">
                            <span class="acid-check__text">
                                <strong>Remplacer les photos existantes</strong>
                                <span>
                                    Sans cette option, les eleves qui ont deja une photo sont ignores.
                                </span>
                            </span>
                        </label>

                        {{-- Progression --}}
                        <div id="importProgressWrap" hidden style="margin-block-start:var(--acid-space-5);">
                            <div style="display:flex;justify-content:space-between;
                                        font-size:var(--acid-text-sm);margin-block-end:var(--acid-space-2);">
                                <span id="importProgressLabel">Envoi en cours...</span>
                                <span id="importProgressValue" style="font-variant-numeric:tabular-nums;">0 %</span>
                            </div>
                            <div class="acid-progress">
                                <div class="acid-progress__bar" id="importProgressBar"></div>
                            </div>
                        </div>

                        {{-- Compte rendu --}}
                        <div id="importReport" hidden style="margin-block-start:var(--acid-space-5);"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="acid-btn acid-btn--neutral" data-bs-dismiss="modal"
                                id="importClose">
                            Fermer
                        </button>
                        <button type="button" class="acid-btn acid-btn--primary" id="importSubmit" disabled>
                            <i class="fas fa-upload" aria-hidden="true"></i>
                            Lancer l'import
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Module partage de prise de vue (modale camera + galerie) --}}
    @include('partials.photo-capture')

    @push('styles')
        <style>
            /* Masquage de colonnes secondaires sur petits ecrans */
            @media (max-width: 991.98px) {
                .acid-hide-md { display: none; }
            }
            @media (max-width: 767.98px) {
                /* En mode liste empilee, les colonnes masquees redeviennent utiles */
                .acid-table--stack .acid-hide-md { display: flex; }
            }

            .acid-avatar img { width: 100%; height: 100%; object-fit: cover; }
        </style>
    @endpush

    @push('scripts')
        <script>
            /* =====================================================================
             | Filtres de la liste des eleves
             * =================================================================== */
            (function () {
                const search  = document.getElementById('searchInput');
                const fSexe   = document.getElementById('filterSexe');
                const fStatut = document.getElementById('filterStatut');
                const fPhoto  = document.getElementById('filterPhoto');
                const rows    = Array.from(document.querySelectorAll('.student-row'));
                const count   = document.getElementById('countDisplay');
                const empty   = document.getElementById('studentsNoResults');
                const table   = document.getElementById('studentsTable');
                const reset   = document.getElementById('resetFilters');

                if (!search || !rows.length) return;

                const normalize = (v) => (v || '').toString()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .toLowerCase().trim();

                function apply() {
                    const needle = normalize(search.value);
                    const sexe   = fSexe.value;
                    const statut = fStatut.value;
                    const photo  = fPhoto.value;
                    let visible = 0;

                    rows.forEach((row) => {
                        const haystack = normalize(row.dataset.nom + ' ' + row.dataset.matricule);
                        const rowStatut = row.dataset.statut === 'R' ? 'R' : 'N';

                        const match =
                            (!needle || haystack.includes(needle)) &&
                            (!sexe   || row.dataset.sexe === sexe) &&
                            (!statut || rowStatut === statut) &&
                            (!photo  || row.dataset.photo === photo);

                        row.hidden = !match;
                        if (match) visible++;

                        /* Une ligne masquee ne doit pas rester selectionnee */
                        if (!match) {
                            const box = row.querySelector('.student-select');
                            if (box && box.checked) {
                                box.checked = false;
                            }
                        }
                    });

                    count.textContent = visible + (visible === 1 ? ' eleve' : ' eleves');
                    empty.hidden = visible > 0;
                    if (table) table.hidden = visible === 0;

                    if (window.AcidTransfert) window.AcidTransfert.refresh();
                }

                let timer = null;
                search.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(apply, 120);
                });
                [fSexe, fStatut, fPhoto].forEach((el) => el.addEventListener('change', apply));

                if (reset) {
                    reset.addEventListener('click', () => {
                        search.value = '';
                        fSexe.value = '';
                        fStatut.value = '';
                        fPhoto.value = '';
                        apply();
                    });
                }
            })();

            /* =====================================================================
             | Selection des eleves et transfert vers une autre classe
             * =================================================================== */
            (function () {
                const boxes     = Array.from(document.querySelectorAll('.student-select'));
                const selectAll = document.getElementById('selectAll');
                const bulkBar   = document.getElementById('bulkBar');
                const bulkCount = document.getElementById('bulkCount');
                const bulkClear = document.getElementById('bulkClear');

                const modal       = document.getElementById('transfertModal');
                const form        = document.getElementById('transfertForm');
                const inputs      = document.getElementById('transfertInputs');
                const countBadge  = document.getElementById('transfertCount');
                const nomsField   = document.getElementById('transfertNoms');
                const errorField  = document.getElementById('transfertError');

                if (!boxes.length) return;

                /* Lignes actuellement visibles (les filtres masquent via [hidden]) */
                const visibleBoxes = () => boxes.filter((b) => !b.closest('tr').hidden);
                const selected = () => boxes.filter((b) => b.checked && !b.closest('tr').hidden);

                function refresh() {
                    const picked = selected();

                    if (bulkBar) bulkBar.hidden = picked.length === 0;
                    if (bulkCount) bulkCount.textContent = picked.length;

                    if (selectAll) {
                        const visible = visibleBoxes();
                        selectAll.checked = visible.length > 0 && picked.length === visible.length;
                        selectAll.indeterminate = picked.length > 0 && picked.length < visible.length;
                    }
                }

                boxes.forEach((box) => box.addEventListener('change', refresh));

                if (selectAll) {
                    selectAll.addEventListener('change', () => {
                        visibleBoxes().forEach((box) => { box.checked = selectAll.checked; });
                        refresh();
                    });
                }

                if (bulkClear) {
                    bulkClear.addEventListener('click', () => {
                        boxes.forEach((box) => { box.checked = false; });
                        refresh();
                    });
                }

                /* Transfert d'un seul eleve : on cible sa ligne puis on ouvre la modale */
                document.querySelectorAll('.js-transfert-un').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const id = btn.getAttribute('data-etudiant-id');
                        boxes.forEach((box) => { box.checked = box.value === id; });
                        refresh();

                        if (modal && window.bootstrap) {
                            window.bootstrap.Modal.getOrCreateInstance(modal).show();
                        }
                    });
                });

                /* Remplissage de la modale a l'ouverture */
                if (modal) {
                    modal.addEventListener('show.bs.modal', () => {
                        const picked = selected();

                        countBadge.textContent = picked.length;
                        errorField.classList.add('d-none');

                        const noms = picked
                            .map((box) => box.closest('tr').dataset.nomComplet)
                            .filter(Boolean);

                        nomsField.textContent = noms.length
                            ? noms.join(' · ')
                            : 'Aucun eleve selectionne.';

                        /* Les identifiants sont (re)construits a chaque ouverture */
                        inputs.innerHTML = '';
                        picked.forEach((box) => {
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'etudiants[]';
                            hidden.value = box.value;
                            inputs.appendChild(hidden);
                        });
                    });
                }

                if (form) {
                    form.addEventListener('submit', (e) => {
                        if (!inputs.querySelector('input[name="etudiants[]"]')) {
                            e.preventDefault();
                            errorField.classList.remove('d-none');
                        }
                    });
                }

                /* Expose refresh() pour que les filtres puissent resynchroniser */
                window.AcidTransfert = { refresh };

                refresh();

                /*
                 | Retour d'une validation serveur en echec : la selection a ete
                 | restauree via old('etudiants'), on rouvre donc la modale pour
                 | que l'utilisateur voie le message d'erreur.
                 */
                @if ($errors->has('classe_destination_id') || $errors->has('etudiants'))
                    if (modal && window.bootstrap) {
                        window.bootstrap.Modal.getOrCreateInstance(modal).show();
                    }
                @endif
            })();

            @if ($classe->etudiants->count() > 0)
            /* =====================================================================
             | Import groupe de photos
             |
             | PHP limite le nombre de fichiers par requete (max_file_uploads,
             | 20 par defaut) : au-dela, les fichiers surnumeraires sont
             | silencieusement perdus. On envoie donc des lots successifs et on
             | agrege les comptes rendus.
             * =================================================================== */
            (function () {
                const modalEl   = document.getElementById('importPhotosModal');
                const input     = document.getElementById('importPhotosInput');
                const dropzone  = document.getElementById('importDropzone');
                const selection = document.getElementById('importSelection');
                const grid      = document.getElementById('importFileGrid');
                const countEl   = document.getElementById('importSelectionCount');
                const clearBtn  = document.getElementById('importSelectionClear');
                const remplacer = document.getElementById('importRemplacer');
                const submit    = document.getElementById('importSubmit');
                const closeBtn  = document.getElementById('importClose');
                const progress  = document.getElementById('importProgressWrap');
                const bar       = document.getElementById('importProgressBar');
                const pLabel    = document.getElementById('importProgressLabel');
                const pValue    = document.getElementById('importProgressValue');
                const report    = document.getElementById('importReport');

                if (!modalEl || !input) return;

                const ENDPOINT   = @js(route('etudiants.photos.bulk'));
                const CLASSE_ID  = @js($classe->id);
                const TOKEN      = @js(csrf_token());
                const TAILLE_LOT = 15;               /* marge sous max_file_uploads */
                const MAX_OCTETS = 10 * 1024 * 1024; /* 10 Mo, comme la validation serveur */

                let fichiers = [];
                let enCours  = false;
                let urlsApercu = [];

                /* ---------------- Selection de fichiers ---------------- */

                function cle(file) {
                    return file.name + '|' + file.size + '|' + file.lastModified;
                }

                function ajouter(liste) {
                    const deja = new Set(fichiers.map(cle));
                    const refuses = [];

                    Array.from(liste).forEach((file) => {
                        if (!/^image\/(jpeg|png)$/i.test(file.type)) {
                            refuses.push(file.name + ' (format non accepte)');
                            return;
                        }
                        if (file.size > MAX_OCTETS) {
                            refuses.push(file.name + ' (plus de 10 Mo)');
                            return;
                        }
                        if (deja.has(cle(file))) return;

                        deja.add(cle(file));
                        fichiers.push(file);
                    });

                    if (refuses.length && window.AcidUI) {
                        window.AcidUI.notify(
                            refuses.length + ' fichier(s) ecarte(s) : ' + refuses.slice(0, 3).join(', ')
                                + (refuses.length > 3 ? '...' : ''),
                            'warning'
                        );
                    }

                    dessinerSelection();
                }

                function libererApercus() {
                    urlsApercu.forEach((url) => URL.revokeObjectURL(url));
                    urlsApercu = [];
                }

                function dessinerSelection() {
                    libererApercus();
                    grid.innerHTML = '';

                    fichiers.forEach((file) => {
                        const url = URL.createObjectURL(file);
                        urlsApercu.push(url);

                        const item = document.createElement('div');
                        item.className = 'acid-filegrid__item';

                        const img = document.createElement('img');
                        img.src = url;
                        img.alt = '';
                        img.loading = 'lazy';

                        const name = document.createElement('span');
                        name.className = 'acid-filegrid__name';
                        name.textContent = file.name;
                        name.title = file.name;

                        item.appendChild(img);
                        item.appendChild(name);
                        grid.appendChild(item);
                    });

                    countEl.textContent = fichiers.length;
                    selection.hidden = fichiers.length === 0;
                    submit.disabled = fichiers.length === 0 || enCours;
                }

                function viderSelection() {
                    fichiers = [];
                    input.value = '';
                    dessinerSelection();
                }

                input.addEventListener('change', () => {
                    ajouter(input.files);
                    /* Permet de re-choisir les memes fichiers apres un vidage */
                    input.value = '';
                });

                clearBtn.addEventListener('click', viderSelection);

                ['dragenter', 'dragover'].forEach((evt) => {
                    dropzone.addEventListener(evt, (e) => {
                        e.preventDefault();
                        dropzone.classList.add('is-dragover');
                    });
                });

                ['dragleave', 'drop'].forEach((evt) => {
                    dropzone.addEventListener(evt, (e) => {
                        e.preventDefault();
                        dropzone.classList.remove('is-dragover');
                    });
                });

                dropzone.addEventListener('drop', (e) => {
                    if (e.dataTransfer && e.dataTransfer.files) ajouter(e.dataTransfer.files);
                });

                /* ---------------- Envoi par lots ---------------- */

                function majProgression(faits, total) {
                    const pct = total === 0 ? 0 : Math.round((faits / total) * 100);
                    bar.style.width = pct + '%';
                    pValue.textContent = pct + ' %';
                    pLabel.textContent = 'Envoi en cours... ' + faits + ' / ' + total + ' fichiers';
                }

                async function envoyerLot(lot) {
                    const data = new FormData();
                    data.append('classe_id', CLASSE_ID);
                    data.append('remplacer', remplacer.checked ? '1' : '0');
                    lot.forEach((file) => data.append('photos[]', file, file.name));

                    const reponse = await fetch(ENDPOINT, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': TOKEN,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: data,
                    });

                    let charge = null;
                    try {
                        charge = await reponse.json();
                    } catch (e) {
                        throw new Error('Reponse inattendue du serveur (HTTP ' + reponse.status + ')');
                    }

                    if (!reponse.ok && reponse.status !== 200) {
                        throw new Error(charge && charge.message ? charge.message : 'Erreur HTTP ' + reponse.status);
                    }

                    return charge;
                }

                submit.addEventListener('click', async () => {
                    if (enCours || fichiers.length === 0) return;

                    enCours = true;
                    submit.disabled = true;
                    closeBtn.disabled = true;
                    clearBtn.disabled = true;
                    input.disabled = true;
                    report.hidden = true;
                    report.innerHTML = '';
                    progress.hidden = false;
                    bar.classList.remove('acid-progress__bar--coral');
                    majProgression(0, fichiers.length);

                    const total = fichiers.length;
                    const cumul = { attribues: [], ignores: [], inconnus: [], echecs: [] };
                    let faits = 0;

                    try {
                        for (let i = 0; i < fichiers.length; i += TAILLE_LOT) {
                            const lot = fichiers.slice(i, i + TAILLE_LOT);
                            const resultat = await envoyerLot(lot);
                            const details = (resultat && resultat.details) || {};

                            ['attribues', 'ignores', 'inconnus', 'echecs'].forEach((k) => {
                                (details[k] || []).forEach((ligne) => cumul[k].push(ligne));
                            });

                            /* Mise a jour immediate des vignettes du tableau */
                            (details.attribues || []).forEach((ligne) => {
                                majVignette(ligne.etudiant_id, ligne.photo_url);
                            });

                            faits += lot.length;
                            majProgression(faits, total);
                        }

                        pLabel.textContent = 'Import termine';
                        afficherCompteRendu(cumul, total);
                        viderSelection();

                        if (window.AcidUI) {
                            const n = cumul.attribues.length;
                            window.AcidUI.notify(
                                n > 0
                                    ? n + ' photo(s) rattachee(s) avec succes'
                                    : 'Aucune photo rattachee : verifiez les noms de fichiers',
                                n > 0 ? 'success' : 'warning'
                            );
                        }
                    } catch (erreur) {
                        bar.classList.add('acid-progress__bar--coral');
                        pLabel.textContent = 'Import interrompu';
                        report.hidden = false;
                        report.innerHTML =
                            '<div class="acid-alert acid-alert--danger">'
                            + '<span class="acid-alert__icon" aria-hidden="true">'
                            + '<i class="fas fa-triangle-exclamation"></i></span>'
                            + '<div class="acid-alert__body"><p class="acid-alert__title">'
                            + 'L\'import a ete interrompu</p><p style="margin:0;">'
                            + echapper(erreur.message)
                            + '</p><p style="margin:var(--acid-space-2) 0 0;font-size:var(--acid-text-sm);">'
                            + 'Les photos deja envoyees ont bien ete enregistrees.</p></div></div>';

                        if (cumul.attribues.length > 0) {
                            afficherCompteRendu(cumul, total, true);
                        }
                    } finally {
                        enCours = false;
                        closeBtn.disabled = false;
                        clearBtn.disabled = false;
                        input.disabled = false;
                        submit.disabled = fichiers.length === 0;
                    }
                });

                /* ---------------- Compte rendu ---------------- */

                function echapper(texte) {
                    const div = document.createElement('div');
                    div.textContent = texte == null ? '' : String(texte);
                    return div.innerHTML;
                }

                function groupe(titre, icone, couleur, lignes) {
                    if (!lignes.length) return '';

                    const items = lignes.map((l) => {
                        const qui = l.eleve ? '<span class="acid-import-list__who">' + echapper(l.eleve) + '</span>' : '';
                        const pourquoi = l.raison ? '<span class="acid-import-list__why">' + echapper(l.raison) + '</span>' : '';
                        return '<li><span class="acid-import-list__file">' + echapper(l.fichier) + '</span>'
                            + qui + pourquoi + '</li>';
                    }).join('');

                    return '<div class="acid-import-group">'
                        + '<p class="acid-import-group__title">'
                        + '<i class="fas ' + icone + '" style="color:' + couleur + ';" aria-hidden="true"></i>'
                        + echapper(titre) + ' (' + lignes.length + ')</p>'
                        + '<ul class="acid-import-list">' + items + '</ul></div>';
                }

                function afficherCompteRendu(cumul, total, enPlus) {
                    const bloc =
                        groupe('Photos attribuees', 'fa-circle-check', 'var(--acid-teal-700)', cumul.attribues)
                        + groupe('Fichiers ignores', 'fa-circle-minus', 'var(--acid-amber-600)', cumul.ignores)
                        + groupe('Aucune correspondance', 'fa-circle-question', 'var(--acid-sky-600)', cumul.inconnus)
                        + groupe('Echecs', 'fa-circle-exclamation', 'var(--acid-coral-700)', cumul.echecs);

                    const entete =
                        '<p style="font-size:var(--acid-text-sm);color:var(--acid-text-muted);margin:0 0 var(--acid-space-3);">'
                        + total + ' fichier(s) traite(s) : '
                        + cumul.attribues.length + ' attribue(s), '
                        + cumul.ignores.length + ' ignore(s), '
                        + cumul.inconnus.length + ' sans correspondance, '
                        + cumul.echecs.length + ' en echec.'
                        + ' <a href="" onclick="window.location.reload();return false;">Recharger la page</a>'
                        + ' pour mettre a jour les indicateurs.</p>';

                    report.hidden = false;
                    report.insertAdjacentHTML(
                        enPlus ? 'beforeend' : 'afterbegin',
                        '<div class="acid-import-report">' + entete + bloc + '</div>'
                    );
                }

                /*
                 | Remplace la vignette d'une ligne sans recharger la page.
                 | Volontairement independant de updateStudentPhoto(), qui pilote
                 | aussi l'apercu de la modale de prise de vue.
                 */
                function majVignette(etudiantId, url) {
                    if (!etudiantId || !url) return;

                    const cible = document.getElementById('student-photo-' + etudiantId);
                    const src = url + (url.indexOf('?') === -1 ? '?t=' : '&t=') + Date.now();

                    if (cible) {
                        if (cible.tagName === 'IMG') {
                            cible.src = src;
                        } else {
                            const img = document.createElement('img');
                            img.src = src;
                            img.alt = '';
                            img.id = cible.id;
                            cible.parentNode.replaceChild(img, cible);
                        }
                    }

                    /* Le filtre "avec / sans photo" lit cet attribut */
                    const ligne = document.querySelector(
                        '.student-select[value="' + etudiantId + '"]'
                    );
                    const tr = ligne ? ligne.closest('.student-row') : null;
                    if (tr) tr.dataset.photo = '1';
                }

                /* Remise a zero a la fermeture, pour un etat propre a la reouverture */
                modalEl.addEventListener('hidden.bs.modal', () => {
                    if (enCours) return;
                    viderSelection();
                    report.hidden = true;
                    report.innerHTML = '';
                    progress.hidden = true;
                    bar.style.width = '0';
                    bar.classList.remove('acid-progress__bar--coral');
                    remplacer.checked = false;
                });
            })();
            @endif
        </script>
    @endpush
</x-app-layout>
