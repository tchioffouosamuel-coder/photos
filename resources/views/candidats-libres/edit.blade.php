<x-app-layout>
    <div class="container py-4" style="max-width: 960px;">
        <!-- Header -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <a href="{{ route('candidats-libres.index', $classe) }}" class="text-warning me-3">
                            <i class="fas fa-arrow-left fs-3"></i>
                        </a>
                        <div>
                            <h1 class="h2 mb-1">Modifier Candidat Libre</h1>
                            <p class="text-muted mb-0">{{ $candidatLibre->nom }}</p>
                        </div>
                    </div>
                    <form action="{{ route('candidats-libres.destroy', [$classe, $candidatLibre]) }}" 
                          method="POST" 
                          onsubmit="return confirm('Supprimer ce candidat ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="card shadow-lg">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('candidats-libres.update', [$classe, $candidatLibre]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Nom -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user text-warning me-2"></i>
                            Nom Complet <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="nom" 
                               value="{{ old('nom', $candidatLibre->nom) }}" 
                               required
                               class="form-control form-control-lg @error('nom') is-invalid @enderror">
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Date et Lieu -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-calendar text-warning me-2"></i>
                                Date de naissance <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   name="date_naissance" 
                                   value="{{ old('date_naissance', $candidatLibre->date_naissance?->format('Y-m-d')) }}" 
                                   required
                                   class="form-control form-control-lg @error('date_naissance') is-invalid @enderror">
                            @error('date_naissance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-map-marker-alt text-warning me-2"></i>
                                Lieu de naissance <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="lieu_naissance" 
                                   value="{{ old('lieu_naissance', $candidatLibre->lieu_naissance) }}" 
                                   required
                                   class="form-control form-control-lg @error('lieu_naissance') is-invalid @enderror">
                            @error('lieu_naissance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Sexe -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-venus-mars text-warning me-2"></i>
                            Sexe <span class="text-danger">*</span>
                        </label>
                        <select name="sexe" 
                                required
                                class="form-select form-select-lg @error('sexe') is-invalid @enderror">
                            <option value="">Sélectionner...</option>
                            <option value="M" {{ old('sexe', $candidatLibre->sexe) === 'M' ? 'selected' : '' }}>Masculin</option>
                            <option value="F" {{ old('sexe', $candidatLibre->sexe) === 'F' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('sexe')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Photo -->
                    <div class="mb-5">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-camera text-warning me-2"></i>
                            Photo
                        </label>
                        
                        @if($candidatLibre->photo)
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $candidatLibre->photo_url }}" 
                                     alt="{{ $candidatLibre->nom }}"
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
                                <p class="small text-muted mb-0">Formats: JPG, PNG (Max: 10MB)</p>
                            </label>
                        </div>
                        @error('photo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('candidats-libres.index', $classe) }}" 
                               class="btn btn-secondary btn-lg w-100 fw-bold">
                                <i class="fas fa-times me-2"></i>Annuler
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
            border-color: #f59e0b !important;
            background-color: #fffbeb;
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