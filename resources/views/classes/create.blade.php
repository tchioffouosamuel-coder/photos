<x-app-layout>

    <nav aria-label="Fil d'Ariane">
        <ol class="acid-breadcrumb">
            <li><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
            <li><a href="{{ route('classes.index') }}">Classes</a></li>
            <li aria-current="page">Nouvelle classe</li>
        </ol>
    </nav>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Gestion scolaire</span>
            <h1 class="acid-page-header__title">Nouvelle classe</h1>
            <p class="acid-page-header__lead">
                Declarez une classe pour y rattacher ses eleves, leurs photos et leurs documents.
            </p>
        </div>
        <div class="acid-page-header__actions">
            <a href="{{ route('classes.index') }}" class="acid-btn acid-btn--ghost">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Retour aux classes
            </a>
        </div>
    </div>

    <div class="acid-grid" style="grid-template-columns:minmax(0,2fr) minmax(260px,1fr);align-items:start;">

        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Informations de la classe</h2>
                    <p class="acid-card__subtitle">Les champs marques d'une etoile sont obligatoires.</p>
                </div>
            </div>

            <form action="{{ route('classes.store') }}" method="POST">
                @csrf

                <div class="acid-card__body">

                    <div class="acid-field">
                        <label for="nom" class="acid-label acid-label--required">Nom de la classe</label>
                        <div class="acid-input-group">
                            <i class="fas fa-chalkboard-user acid-input-group__icon" aria-hidden="true"></i>
                            <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required
                                   placeholder="Ex. TF4-BA, 2nde F6-MIPE"
                                   class="acid-input @error('nom') is-invalid @enderror">
                        </div>
                        <span class="acid-hint">Le nom doit etre unique et facilement reconnaissable.</span>
                        @error('nom')
                            <div class="acid-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="acid-field">
                        <label for="exam" class="acid-label">Examen / diplome</label>
                        <div class="acid-input-group">
                            <i class="fas fa-graduation-cap acid-input-group__icon" aria-hidden="true"></i>
                            <input type="text" id="exam" name="exam" value="{{ old('exam') }}"
                                   placeholder="Ex. BAF4-BA, PBF6, BTIH"
                                   class="acid-input @error('exam') is-invalid @enderror">
                        </div>
                        <span class="acid-hint">
                            Renseignez l'examen officiel : il conditionne le format des photos labellisees.
                        </span>
                        @error('exam')
                            <div class="acid-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="acid-field">
                        <label for="annee_scolaire" class="acid-label acid-label--required">Annee scolaire</label>
                        <div class="acid-input-group">
                            <i class="fas fa-calendar-days acid-input-group__icon" aria-hidden="true"></i>
                            <input type="text" id="annee_scolaire" name="annee_scolaire" readonly
                                   value="{{ old('annee_scolaire', date('Y') . '-' . (date('Y') + 1)) }}"
                                   required pattern="\d{4}-\d{4}" placeholder="2024-2025"
                                   class="acid-input @error('annee_scolaire') is-invalid @enderror">
                        </div>
                        <span class="acid-hint">Format AAAA-AAAA, renseigne automatiquement.</span>
                        @error('annee_scolaire')
                            <div class="acid-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="acid-card__footer">
                    <a href="{{ route('classes.index') }}" class="acid-btn acid-btn--neutral">
                        Annuler
                    </a>
                    <button type="submit" class="acid-btn acid-btn--primary" style="margin-inline-start:auto;">
                        <i class="fas fa-floppy-disk" aria-hidden="true"></i>
                        Creer la classe
                    </button>
                </div>
            </form>
        </section>

        {{-- Guide de la demarche --}}
        <aside class="acid-card">
            <div class="acid-card__header">
                <span class="acid-stat__icon" aria-hidden="true" style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                    <i class="fas fa-route"></i>
                </span>
                <div>
                    <h2 class="acid-card__title">Prochaines etapes</h2>
                    <p class="acid-card__subtitle">De la classe au document imprimable</p>
                </div>
            </div>
            <div class="acid-card__body">
                <ol class="acid-steps" style="flex-direction:column;align-items:stretch;">
                    <li class="acid-steps__item is-active">Creer la classe</li>
                    <li class="acid-steps__item">Importer la liste des eleves</li>
                    <li class="acid-steps__item">Photographier les eleves</li>
                    <li class="acid-steps__item">Generer cartes et planches</li>
                </ol>

                <div class="acid-alert acid-alert--navy" style="margin-block-start:var(--acid-space-5);">
                    <i class="fas fa-lightbulb acid-alert__icon" aria-hidden="true"></i>
                    <div class="acid-alert__body">
                        <p class="acid-alert__title">Gagnez du temps</p>
                        <p style="margin:0;font-size:var(--acid-text-sm);">
                            L'import Excel cree la classe et ses eleves en une seule operation.
                        </p>
                        <a href="{{ route('import.index') }}"
                           class="acid-btn acid-btn--outline acid-btn--sm"
                           style="margin-block-start:var(--acid-space-3);">
                            <i class="fas fa-file-import" aria-hidden="true"></i>
                            Aller a l'import
                        </a>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
