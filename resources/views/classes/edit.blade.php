<x-app-layout>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="card shadow-lg mb-4 animate-fade-in">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('classes.show', $classe) }}" class="btn btn-link text-primary me-3">
                                <i class="fas fa-arrow-left fs-4"></i>
                            </a>
                            <div>
                                <h1 class="h2 mb-1">
                                    <i class="fas fa-edit text-primary me-2"></i>
                                    Modifier la Classe
                                </h1>
                                <p class="text-muted mb-0">{{ $classe->nom }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="card shadow-lg animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="card-body p-4">
                        <form action="{{ route('classes.update', $classe) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-chalkboard-teacher text-primary me-2"></i>
                                    Nom de la Classe
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="nom" 
                                       value="{{ old('nom', $classe->nom) }}" 
                                       required
                                       class="form-control form-control-lg @error('nom') is-invalid @enderror">
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-graduation-cap text-primary me-2"></i>
                                    Examen / Diplôme
                                </label>
                                <input type="text" 
                                       name="exam" 
                                       value="{{ old('exam', $classe->exam) }}"
                                       class="form-control form-control-lg"
                                       placeholder="Saisir l'examen (ex: BAC, BEPC, CEP)">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    Année Scolaire
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="annee_scolaire"
                                       value="{{ old('annee_scolaire', $classe->annee_scolaire) }}" 
                                       required 
                                       pattern="\d{4}-\d{4}"
                                       placeholder="2024-2025"
                                       class="form-control form-control-lg @error('annee_scolaire') is-invalid @enderror">
                                @error('annee_scolaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Stats (Read-only) -->
                            <div class="card bg-light border shadow-sm mb-4">
                                <div class="card-body">
                                    <h3 class="h6 fw-bold mb-3">
                                        <i class="fas fa-chart-bar text-primary me-2"></i>
                                        Statistiques actuelles
                                    </h3>
                                    <div class="row g-3 text-center">
                                        <div class="col-4">
                                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-users text-primary fs-4"></i>
                                            </div>
                                            <div class="fs-2 fw-bold text-primary">{{ $classe->effectif_total }}</div>
                                            <small class="text-muted">Total élèves</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-mars text-primary fs-4"></i>
                                            </div>
                                            <div class="fs-2 fw-bold text-primary">{{ $classe->effectif_garcons }}</div>
                                            <small class="text-muted">Garçons</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-venus text-danger fs-4"></i>
                                            </div>
                                            <div class="fs-2 fw-bold text-danger">{{ $classe->effectif_filles }}</div>
                                            <small class="text-muted">Filles</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit"
                                    class="btn btn-primary btn-lg flex-fill shadow">
                                    <i class="fas fa-save me-2"></i>
                                    Enregistrer les Modifications
                                </button>
                                <a href="{{ route('classes.show', $classe) }}"
                                    class="btn btn-secondary btn-lg shadow">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes fade-in {
            from { 
                opacity: 0; 
                transform: translateY(-10px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        
        .animate-fade-in {
            animation: fade-in 0.5s ease-out;
        }

        .bg-opacity-10 {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }

        .bg-danger.bg-opacity-10 {
            background-color: rgba(var(--bs-danger-rgb), 0.1) !important;
        }

        .shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }
    </style>
</x-app-layout>