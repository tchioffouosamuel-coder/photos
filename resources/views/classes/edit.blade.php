<x-app-layout>

    <nav aria-label="Fil d'Ariane">
        <ol class="acid-breadcrumb">
            <li><a href="{{ route('classes.index') }}">Classes</a></li>
            <li><a href="{{ route('classes.show', $classe) }}">{{ $classe->nom }}</a></li>
            <li aria-current="page">Modifier</li>
        </ol>
    </nav>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Gestion scolaire</span>
            <h1 class="acid-page-header__title">Modifier la classe</h1>
            <p class="acid-page-header__lead">{{ $classe->nom }} &middot; {{ $classe->annee_scolaire }}</p>
        </div>
        <div class="acid-page-header__actions">
            <a href="{{ route('classes.show', $classe) }}" class="acid-btn acid-btn--ghost">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Retour a la classe
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

            <form action="{{ route('classes.update', $classe) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="acid-card__body">

                    <div class="acid-field">
                        <label for="nom" class="acid-label acid-label--required">Nom de la classe</label>
                        <div class="acid-input-group">
                            <i class="fas fa-chalkboard-user acid-input-group__icon" aria-hidden="true"></i>
                            <input type="text" id="nom" name="nom" value="{{ old('nom', $classe->nom) }}" required
                                   class="acid-input @error('nom') is-invalid @enderror">
                        </div>
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
                            <input type="text" id="exam" name="exam" value="{{ old('exam', $classe->exam) }}"
                                   placeholder="Ex. BAC, BEPC, CEP"
                                   class="acid-input @error('exam') is-invalid @enderror">
                        </div>
                        <span class="acid-hint">
                            Conditionne le format des photos labellisees pour les fiches d'examen.
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
                            <input type="text" id="annee_scolaire" name="annee_scolaire"
                                   value="{{ old('annee_scolaire', $classe->annee_scolaire) }}"
                                   required pattern="\d{4}-\d{4}" placeholder="2024-2025"
                                   class="acid-input @error('annee_scolaire') is-invalid @enderror">
                        </div>
                        <span class="acid-hint">Format AAAA-AAAA.</span>
                        @error('annee_scolaire')
                            <div class="acid-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="acid-card__footer">
                    <a href="{{ route('classes.show', $classe) }}" class="acid-btn acid-btn--neutral">
                        Annuler
                    </a>
                    <button type="submit" class="acid-btn acid-btn--primary" style="margin-inline-start:auto;">
                        <i class="fas fa-floppy-disk" aria-hidden="true"></i>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </section>

        <aside class="acid-stack">
            <section class="acid-card">
                <div class="acid-card__header">
                    <div>
                        <h2 class="acid-card__title">Effectifs actuels</h2>
                        <p class="acid-card__subtitle">Donnees en lecture seule</p>
                    </div>
                </div>
                <div class="acid-card__body">
                    <div class="acid-stack" style="gap:var(--acid-space-3);">
                        <div class="acid-row acid-row--between">
                            <span class="acid-stat__label">Total eleves</span>
                            <strong class="acid-num" style="font-size:var(--acid-text-lg);color:var(--acid-navy);">
                                {{ $classe->effectif_total }}
                            </strong>
                        </div>
                        <hr class="acid-divider" style="margin:0;">
                        <div class="acid-row acid-row--between">
                            <span class="acid-stat__label">Garcons</span>
                            <strong class="acid-num" style="font-size:var(--acid-text-lg);color:var(--acid-sky-600);">
                                {{ $classe->effectif_garcons }}
                            </strong>
                        </div>
                        <hr class="acid-divider" style="margin:0;">
                        <div class="acid-row acid-row--between">
                            <span class="acid-stat__label">Filles</span>
                            <strong class="acid-num" style="font-size:var(--acid-text-lg);color:var(--acid-coral-600);">
                                {{ $classe->effectif_filles }}
                            </strong>
                        </div>
                    </div>

                    <a href="{{ route('classes.show', $classe) }}"
                       class="acid-btn acid-btn--outline acid-btn--sm acid-btn--block"
                       style="margin-block-start:var(--acid-space-5);">
                        <i class="fas fa-users" aria-hidden="true"></i>
                        Gerer les eleves
                    </a>
                </div>
            </section>

            <section class="acid-card" style="border-color:var(--acid-coral-100);">
                <div class="acid-card__body">
                    <h3 class="acid-card__title" style="margin-block-end:.375rem;">Zone sensible</h3>
                    <p class="acid-card__subtitle" style="margin-block-end:var(--acid-space-4);">
                        La suppression retire la classe et tous ses eleves, photos incluses.
                    </p>
                    <form action="{{ route('classes.destroy', $classe) }}" method="POST"
                          data-acid-confirm="Supprimer la classe {{ $classe->nom }} et tous ses eleves ? Cette action est irreversible.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="acid-btn acid-btn--outline-coral acid-btn--sm acid-btn--block">
                            <i class="fas fa-trash-can" aria-hidden="true"></i>
                            Supprimer la classe
                        </button>
                    </form>
                </div>
            </section>
        </aside>
    </div>
</x-app-layout>
