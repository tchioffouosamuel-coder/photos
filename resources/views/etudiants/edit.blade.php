<x-app-layout>

    <nav aria-label="Fil d'Ariane">
        <ol class="acid-breadcrumb">
            <li><a href="{{ route('classes.index') }}">Classes</a></li>
            <li><a href="{{ route('classes.show', $classe) }}">{{ $classe->nom }}</a></li>
            <li aria-current="page">{{ strtoupper($etudiant->nom) }}</li>
        </ol>
    </nav>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">
                {{ $classe->nom }} &middot; {{ $classe->annee_scolaire }}
            </span>
            <h1 class="acid-page-header__title">Modifier l'eleve</h1>
            <p class="acid-page-header__lead">
                {{ strtoupper($etudiant->nom) }} {{ $etudiant->prenoms }}
            </p>
        </div>
        <div class="acid-page-header__actions">
            <a href="{{ route('classes.show', $classe) }}" class="acid-btn acid-btn--ghost">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Retour a la classe
            </a>
            <form action="{{ route('etudiants.destroy', [$classe, $etudiant]) }}" method="POST"
                  data-acid-confirm="Supprimer definitivement {{ $etudiant->nom }} {{ $etudiant->prenoms }} ?">
                @csrf
                @method('DELETE')
                <button type="submit" class="acid-btn acid-btn--outline-coral">
                    <i class="fas fa-trash-can" aria-hidden="true"></i>
                    Supprimer
                </button>
            </form>
        </div>
    </div>

    <form action="{{ route('etudiants.update', [$classe, $etudiant]) }}" method="POST"
          enctype="multipart/form-data" style="max-width:62rem;">
        @csrf
        @method('PUT')

        <div class="acid-stack">

            {{-- Identification --}}
            <section class="acid-card">
                <div class="acid-card__header">
                    <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-id-badge"></i></span>
                    <div>
                        <h2 class="acid-card__title">Identification</h2>
                        <p class="acid-card__subtitle">Matricules de l'eleve</p>
                    </div>
                </div>
                <div class="acid-card__body">
                    <div class="acid-form-grid acid-form-grid--2">
                        <div class="acid-field" style="margin:0;">
                            <label for="matricule_national" class="acid-label">Matricule national</label>
                            <input type="text" id="matricule_national" name="matricule_national"
                                   value="{{ old('matricule_national', $etudiant->matricule_national) }}"
                                   class="acid-input @error('matricule_national') is-invalid @enderror">
                            @error('matricule_national')
                                <div class="acid-error" role="alert">
                                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <div class="acid-field" style="margin:0;">
                            <label for="matricule_interne" class="acid-label">Matricule interne</label>
                            <input type="text" id="matricule_interne" name="matricule_interne"
                                   value="{{ old('matricule_interne', $etudiant->matricule_interne) }}"
                                   class="acid-input @error('matricule_interne') is-invalid @enderror">
                            @error('matricule_interne')
                                <div class="acid-error" role="alert">
                                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- Etat civil --}}
            <section class="acid-card">
                <div class="acid-card__header">
                    <span class="acid-stat__icon" aria-hidden="true"
                          style="background:var(--acid-sky-50);color:var(--acid-sky-600);">
                        <i class="fas fa-user"></i>
                    </span>
                    <div>
                        <h2 class="acid-card__title">Etat civil</h2>
                        <p class="acid-card__subtitle">Informations portees sur la carte et les documents</p>
                    </div>
                </div>
                <div class="acid-card__body">

                    <div class="acid-field">
                        <label for="nom" class="acid-label acid-label--required">Nom et prenoms</label>
                        <input type="text" id="nom" name="nom" value="{{ old('nom', $etudiant->nom) }}" required
                               class="acid-input @error('nom') is-invalid @enderror">
                        @error('nom')
                            <div class="acid-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    {{-- Champ conserve : alimente par le controleur / l'import --}}
                    <div class="d-none">
                        <label for="prenoms" class="acid-label">Prenoms</label>
                        <input type="text" id="prenoms" name="prenoms"
                               value="{{ old('prenoms', $etudiant->prenoms) }}" class="acid-input">
                        @error('prenoms')
                            <div class="acid-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="acid-form-grid acid-form-grid--2">
                        <div class="acid-field" style="margin:0;">
                            <label for="date_naissance" class="acid-label acid-label--required">Date de naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance" required
                                   value="{{ old('date_naissance', $etudiant->date_naissance ? $etudiant->date_naissance->format('Y-m-d') : '') }}"
                                   class="acid-input @error('date_naissance') is-invalid @enderror">
                            @error('date_naissance')
                                <div class="acid-error" role="alert">
                                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <div class="acid-field" style="margin:0;">
                            <label for="lieu_naissance" class="acid-label acid-label--required">Lieu de naissance</label>
                            <input type="text" id="lieu_naissance" name="lieu_naissance" required
                                   value="{{ old('lieu_naissance', $etudiant->lieu_naissance) }}"
                                   class="acid-input @error('lieu_naissance') is-invalid @enderror">
                            @error('lieu_naissance')
                                <div class="acid-error" role="alert">
                                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <div class="acid-field" style="margin:0;">
                            <label for="sexe" class="acid-label acid-label--required">Sexe</label>
                            <select id="sexe" name="sexe" required
                                    class="acid-select @error('sexe') is-invalid @enderror">
                                <option value="">Selectionner...</option>
                                <option value="M" {{ old('sexe', $etudiant->sexe) === 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('sexe', $etudiant->sexe) === 'F' ? 'selected' : '' }}>Feminin</option>
                            </select>
                            @error('sexe')
                                <div class="acid-error" role="alert">
                                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <div class="acid-field" style="margin:0;">
                            <label for="statut" class="acid-label acid-label--required">Statut</label>
                            <select id="statut" name="statut" required
                                    class="acid-select @error('statut') is-invalid @enderror">
                                <option value="">Selectionner...</option>
                                <option value="N" {{ old('statut', $etudiant->statut) === 'N' ? 'selected' : '' }}>Normal</option>
                                <option value="R" {{ old('statut', $etudiant->statut) === 'R' ? 'selected' : '' }}>Redoublant</option>
                            </select>
                            @error('statut')
                                <div class="acid-error" role="alert">
                                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- Photo --}}
            <section class="acid-card">
                <div class="acid-card__header">
                    <span class="acid-stat__icon" aria-hidden="true"
                          style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                        <i class="fas fa-camera-retro"></i>
                    </span>
                    <div>
                        <h2 class="acid-card__title">Photographie</h2>
                        <p class="acid-card__subtitle">Remplacer la photo de l'eleve</p>
                    </div>
                </div>
                <div class="acid-card__body">

                    @if ($etudiant->photo)
                        <div class="acid-card acid-card--soft" style="padding:var(--acid-space-4);
                                    margin-block-end:var(--acid-space-4);display:flex;gap:var(--acid-space-4);
                                    align-items:center;flex-wrap:wrap;">
                            <img src="{{ photo_url('photos/' . $classe->nom . '/' . $etudiant->photo) }}"
                                 alt="Photo de {{ $etudiant->nom }}"
                                 style="width:104px;height:104px;object-fit:cover;flex:0 0 auto;
                                        border-radius:var(--acid-radius);border:1px solid var(--acid-border);">
                            <div style="min-width:0;">
                                <p style="margin:0;font-weight:var(--acid-fw-semibold);color:var(--acid-text-strong);">
                                    Photo actuelle
                                </p>
                                <p class="acid-hint" style="margin:0;">
                                    Selectionnez une nouvelle image ci-dessous pour la remplacer.
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="acid-dropzone" id="photoZone">
                        <input type="file" id="photoInput" name="photo" accept="image/*"
                               class="d-none" onchange="previewPhoto(event)">
                        <label for="photoInput" style="cursor:pointer;display:block;margin:0;">
                            <span id="photoPreviewContainer" class="d-none"
                                  style="display:block;margin-block-end:var(--acid-space-3);">
                                <img id="photoPreview" alt="Apercu de la nouvelle photo"
                                     style="max-width:220px;max-height:220px;object-fit:cover;
                                            border-radius:var(--acid-radius);border:1px solid var(--acid-border);">
                            </span>
                            <span class="acid-dropzone__icon" aria-hidden="true">
                                <i class="fas fa-cloud-arrow-up"></i>
                            </span>
                            <span class="acid-dropzone__title" style="display:block;margin-block-start:var(--acid-space-3);">
                                {{ $etudiant->photo ? 'Changer la photo' : 'Selectionner une photo' }}
                            </span>
                            <span class="acid-dropzone__hint" style="display:block;">
                                JPG ou PNG &middot; 5 Mo maximum
                            </span>
                        </label>
                    </div>
                    @error('photo')
                        <div class="acid-error" role="alert">
                            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>
            </section>
        </div>

        <div class="acid-form-actions">
            <a href="{{ route('classes.show', $classe) }}" class="acid-btn acid-btn--neutral">
                Annuler
            </a>
            <div class="acid-form-actions__end">
                <button type="submit" class="acid-btn acid-btn--primary acid-btn--lg">
                    <i class="fas fa-floppy-disk" aria-hidden="true"></i>
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>

    {{-- Transfert de classe : formulaire distinct de la mise a jour de la fiche --}}
    <section class="acid-card" style="max-width:62rem;margin-block-start:var(--acid-space-6);">
        <div class="acid-card__header">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-sky-50);color:var(--acid-sky-600);">
                <i class="fas fa-right-left"></i>
            </span>
            <div>
                <h2 class="acid-card__title">Transferer vers une autre classe</h2>
                <p class="acid-card__subtitle">
                    La fiche et la photo de l'eleve suivent le transfert
                </p>
            </div>
        </div>

        <div class="acid-card__body">
            @if ($autresClasses->isEmpty())
                <div class="acid-alert acid-alert--warning">
                    <i class="fas fa-circle-info acid-alert__icon" aria-hidden="true"></i>
                    <div class="acid-alert__body">
                        <p style="margin:0;font-size:var(--acid-text-sm);">
                            Aucune autre classe n'est enregistree. Creez-en une pour pouvoir
                            transferer cet eleve.
                        </p>
                        <a href="{{ route('classes.create') }}"
                           class="acid-btn acid-btn--outline acid-btn--sm"
                           style="margin-block-start:var(--acid-space-3);">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            Creer une classe
                        </a>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('etudiants.transfert', $classe) }}"
                      data-acid-confirm="Transferer {{ $etudiant->nom }} {{ $etudiant->prenoms }} vers la classe selectionnee ?">
                    @csrf
                    <input type="hidden" name="etudiants[]" value="{{ $etudiant->id }}">

                    <div class="acid-row" style="align-items:flex-end;gap:var(--acid-space-3);">
                        <div class="acid-toolbar__grow">
                            <label for="transfert_destination" class="acid-label acid-label--required">
                                Classe de destination
                            </label>
                            <select id="transfert_destination" name="classe_destination_id" required
                                    class="acid-select @error('classe_destination_id') is-invalid @enderror">
                                <option value="">Selectionnez une classe</option>
                                @foreach ($autresClasses as $destination)
                                    <option value="{{ $destination->id }}">
                                        {{ $destination->nom }} &mdash; {{ $destination->annee_scolaire }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="acid-btn acid-btn--secondary">
                            <i class="fas fa-right-left" aria-hidden="true"></i>
                            Transferer
                        </button>
                    </div>

                    @error('classe_destination_id')
                        <div class="acid-error" role="alert">
                            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <p class="acid-hint" style="margin-block-start:var(--acid-space-3);">
                        Classe actuelle : <strong>{{ $classe->nom }}</strong> ({{ $classe->annee_scolaire }}).
                        Pour transferer plusieurs eleves a la fois, utilisez la selection
                        depuis <a href="{{ route('classes.show', $classe) }}">la liste de la classe</a>.
                    </p>
                </form>
            @endif
        </div>
    </section>

    @push('scripts')
        <script>
            function previewPhoto(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('photoPreview').src = e.target.result;
                    document.getElementById('photoPreviewContainer').classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }

            (function () {
                const zone = document.getElementById('photoZone');
                const input = document.getElementById('photoInput');
                if (!zone || !input) return;

                const prevent = (e) => { e.preventDefault(); e.stopPropagation(); };
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((n) => zone.addEventListener(n, prevent));
                ['dragenter', 'dragover'].forEach((n) =>
                    zone.addEventListener(n, () => zone.classList.add('is-dragover')));
                ['dragleave', 'drop'].forEach((n) =>
                    zone.addEventListener(n, () => zone.classList.remove('is-dragover')));

                zone.addEventListener('drop', (e) => {
                    if (e.dataTransfer.files.length) {
                        input.files = e.dataTransfer.files;
                        previewPhoto({ target: { files: e.dataTransfer.files } });
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>
