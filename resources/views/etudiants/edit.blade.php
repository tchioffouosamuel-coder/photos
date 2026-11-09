<x-app-layout>
    <div class="container py-4" style="max-width: 960px;">
        <!-- Header -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <a href="{{ route('classes.show', $classe) }}" 
                           class="text-primary me-3">
                            <i class="fas fa-arrow-left fs-3"></i>
                        </a>
                        <div>
                            <h1 class="h2 mb-1">Modifier l'élève</h1>
                            <p class="text-muted mb-0">{{ $etudiant->nom }} - {{ $classe->nom }}</p>
                        </div>
                    </div>
                    <form action="{{ route('etudiants.destroy', [$classe, $etudiant]) }}" 
                          method="POST" 
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet élève ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="card shadow-lg">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('etudiants.update', [$classe, $etudiant]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Matricules -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-id-badge text-primary me-2"></i>
                                Matricule National
                            </label>
                            <input type="text" 
                                   name="matricule_national" 
                                   value="{{ old('matricule_national', $etudiant->matricule_national) }}"
                                   class="form-control form-control-lg @error('matricule_national') is-invalid @enderror">
                            @error('matricule_national')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-hashtag text-primary me-2"></i>
                                Matricule Interne
                            </label>
                            <input type="text" 
                                   name="matricule_interne" 
                                   value="{{ old('matricule_interne', $etudiant->matricule_interne) }}"
                                   class="form-control form-control-lg @error('matricule_interne') is-invalid @enderror">
                            @error('matricule_interne')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Nom -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user text-primary me-2"></i>
                            Nom <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="nom" 
                               value="{{ old('nom', $etudiant->nom) }}" 
                               required
                               class="form-control form-control-lg @error('nom') is-invalid @enderror">
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Prénoms (hidden) -->
                    <div class="d-none">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user-tag text-primary me-2"></i>
                            Prénoms <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="prenoms" 
                               value="" 
                               required
                               class="form-control form-control-lg @error('prenoms') is-invalid @enderror">
                        @error('prenoms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Date et Lieu de naissance -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                Date de naissance <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   name="date_naissance" 
                                   value="{{ old('date_naissance', $etudiant->date_naissance ? $etudiant->date_naissance->format('Y-m-d') : '') }}" 
                                   required
                                   class="form-control form-control-lg @error('date_naissance') is-invalid @enderror">
                            @error('date_naissance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                Lieu de naissance <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="lieu_naissance" 
                                   value="{{ old('lieu_naissance', $etudiant->lieu_naissance) }}" 
                                   required
                                   class="form-control form-control-lg @error('lieu_naissance') is-invalid @enderror">
                            @error('lieu_naissance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Sexe et Statut -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-venus-mars text-primary me-2"></i>
                                Sexe <span class="text-danger">*</span>
                            </label>
                            <select name="sexe" 
                                    required
                                    class="form-select form-select-lg @error('sexe') is-invalid @enderror">
                                <option value="">Sélectionner...</option>
                                <option value="M" {{ old('sexe', $etudiant->sexe) === 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('sexe', $etudiant->sexe) === 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                            @error('sexe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Statut <span class="text-danger">*</span>
                            </label>
                            <select name="statut" 
                                    required
                                    class="form-select form-select-lg @error('statut') is-invalid @enderror">
                                <option value="">Sélectionner...</option>
                                <option value="N" {{ old('statut', $etudiant->statut) === 'N' ? 'selected' : '' }}>Normal</option>
                                <option value="R" {{ old('statut', $etudiant->statut) === 'R' ? 'selected' : '' }}>Redoublant</option>
                            </select>
                            @error('statut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Photo -->
                    <div class="mb-5">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-camera text-primary me-2"></i>
                            Photo
                        </label>
                        
                        @if($etudiant->photo)
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ asset('storage/photos/' . $classe->nom . '/' . $etudiant->photo) }}" 
                                     alt="{{ $etudiant->nom }}"
                                     class="img-thumbnail"
                                     style="width: 128px; height: 128px; object-fit: cover;">
                                <div>
                                    <p class="small text-muted mb-1">Photo actuelle</p>
                                    <p class="small text-muted mb-0">Sélectionnez une nouvelle photo pour la remplacer</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="border border-2 border-dashed rounded p-5 text-center upload-zone">
                            <input type="file" 
                                   id="photoInput" 
                                   name="photo" 
                                   accept="image/*" 
                                   class="d-none" 
                                   onchange="previewPhoto(event)">
                            <label for="photoInput" class="d-block" style="cursor: pointer;">
                                <div id="photoPreviewContainer" class="d-none mb-3">
                                    <img id="photoPreview" 
                                         class="img-thumbnail mx-auto d-block" 
                                         style="max-width: 300px; max-height: 200px; object-fit: contain;">
                                </div>
                                <i class="fas fa-cloud-upload-alt text-muted mb-3" style="font-size: 4rem;"></i>
                                <p class="fs-5 fw-semibold text-dark mb-2">Cliquez pour changer la photo</p>
                                <p class="small text-muted mb-0">Formats acceptés: JPG, PNG (Max: 5MB)</p>
                            </label>
                        </div>
                        @error('photo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <button type="submit" 
                                    class="btn btn-lg w-100 text-white fw-bold" 
                                    style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                                <i class="fas fa-save me-2"></i>
                                Mettre à jour
                            </button>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('classes.show', $classe) }}" 
                               class="btn btn-secondary btn-lg w-100 fw-bold">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .upload-zone {
            transition: all 0.3s ease;
        }

        .upload-zone:hover {
            border-color: #4f46e5 !important;
            background-color: #f8f9fa;
        }
    </style>

    <script>
        function previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoPreview').src = e.target.result;
                    document.getElementById('photoPreviewContainer').classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</x-app-layout>