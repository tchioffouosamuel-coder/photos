<x-app-layout>

    <nav aria-label="Fil d'Ariane">
        <ol class="acid-breadcrumb">
            <li><a href="{{ route('classes.show', $classe) }}">{{ $classe->nom }}</a></li>
            <li><a href="{{ route('candidats-libres.index', $classe) }}">Candidats libres</a></li>
            <li aria-current="page">Nouveau</li>
        </ol>
    </nav>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">
                {{ $classe->nom }}
                @if ($classe->exam)
                    &middot; {{ $classe->exam }}
                @endif
            </span>
            <h1 class="acid-page-header__title">Nouveau candidat libre</h1>
            <p class="acid-page-header__lead">
                Les informations saisies alimentent directement les photos labellisees d'examen.
            </p>
        </div>
        <div class="acid-page-header__actions">
            <a href="{{ route('candidats-libres.index', $classe) }}" class="acid-btn acid-btn--ghost">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Retour a la liste
            </a>
        </div>
    </div>

    <form action="{{ route('candidats-libres.store', $classe) }}" method="POST"
          enctype="multipart/form-data" style="max-width:62rem;">
        @csrf

        <div class="acid-stack">

            <section class="acid-card">
                <div class="acid-card__header">
                    <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-user-tag"></i></span>
                    <div>
                        <h2 class="acid-card__title">Identite du candidat</h2>
                        <p class="acid-card__subtitle">Telle qu'elle figurera sur la fiche d'examen</p>
                    </div>
                </div>
                <div class="acid-card__body">

                    <div class="acid-field">
                        <label for="nom" class="acid-label acid-label--required">Nom complet</label>
                        <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required
                               class="acid-input @error('nom') is-invalid @enderror">
                        @error('nom')
                            <div class="acid-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="acid-form-grid acid-form-grid--2">
                        <div class="acid-field" style="margin:0;">
                            <label for="date_naissance" class="acid-label acid-label--required">Date de naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance"
                                   value="{{ old('date_naissance') }}" required
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
                            <input type="text" id="lieu_naissance" name="lieu_naissance"
                                   value="{{ old('lieu_naissance') }}" required
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
                                <option value="M" {{ old('sexe') === 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('sexe') === 'F' ? 'selected' : '' }}>Feminin</option>
                            </select>
                            @error('sexe')
                                <div class="acid-error" role="alert">
                                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="acid-card">
                <div class="acid-card__header">
                    <span class="acid-stat__icon" aria-hidden="true"
                          style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                        <i class="fas fa-camera-retro"></i>
                    </span>
                    <div>
                        <h2 class="acid-card__title">Photographie</h2>
                        <p class="acid-card__subtitle">Facultatif a cette etape</p>
                    </div>
                </div>
                <div class="acid-card__body">
                    <div class="acid-dropzone" id="photoZone">
                        <input type="file" id="photoInput" name="photo" accept="image/*"
                               class="d-none" onchange="previewPhoto(event)">
                        <label for="photoInput" style="cursor:pointer;display:block;margin:0;">
                            <span id="photoPreviewContainer" class="d-none"
                                  style="display:block;margin-block-end:var(--acid-space-3);">
                                <img id="photoPreview" alt="Apercu de la photo"
                                     style="max-width:220px;max-height:220px;object-fit:cover;
                                            border-radius:var(--acid-radius);border:1px solid var(--acid-border);">
                            </span>
                            <span class="acid-dropzone__icon" aria-hidden="true">
                                <i class="fas fa-cloud-arrow-up"></i>
                            </span>
                            <span class="acid-dropzone__title" style="display:block;margin-block-start:var(--acid-space-3);">
                                Selectionner une photo
                            </span>
                            <span class="acid-dropzone__hint" style="display:block;">
                                JPG ou PNG &middot; 10 Mo maximum
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
            <a href="{{ route('candidats-libres.index', $classe) }}" class="acid-btn acid-btn--neutral">
                Annuler
            </a>
            <div class="acid-form-actions__end">
                <button type="submit" class="acid-btn acid-btn--primary acid-btn--lg">
                    <i class="fas fa-floppy-disk" aria-hidden="true"></i>
                    Enregistrer le candidat
                </button>
            </div>
        </div>
    </form>

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
