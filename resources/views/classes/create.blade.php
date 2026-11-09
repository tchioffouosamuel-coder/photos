<x-app-layout>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="card shadow-lg mb-4 animate-fade-in">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('classes.index') }}" class="btn btn-link text-primary me-3">
                                <i class="fas fa-arrow-left fs-4"></i>
                            </a>
                            <div>
                                <h1 class="h2 mb-1">
                                    <i class="fas fa-plus-circle text-primary me-2"></i>
                                    Nouvelle Classe
                                </h1>
                                <p class="text-muted mb-0">Créez une nouvelle classe scolaire</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="card shadow-lg animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="card-body p-4">
                        <form action="{{ route('classes.store') }}" method="POST">
                            @csrf

                            <!-- Nom de la classe -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-chalkboard-teacher text-primary me-2"></i>
                                    Nom de la Classe
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="nom" 
                                       value="{{ old('nom') }}" 
                                       required
                                       placeholder="Ex: TF4-BA, 2nde F6-MIPE ...."
                                       class="form-control form-control-lg @error('nom') is-invalid @enderror">
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Le nom doit être unique et descriptif
                                </div>
                            </div>

                            <!-- Examen -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-graduation-cap text-primary me-2"></i>
                                    Examen / Diplôme
                                </label>
                                <input type="text" 
                                       name="exam" 
                                       value="{{ old('exam') }}"
                                       class="form-control form-control-lg"
                                       placeholder="Saisir l'examen (ex: BAF4-BA, PBF6, BTIH ....)">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Sélectionnez l'examen officiel si applicable
                                </div>
                            </div>

                            <!-- Année scolaire -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    Année Scolaire
                                    <span class="text-danger">*</span>
                                </label>
                                <input readonly="" type="text" 
                                       name="annee_scolaire"
                                       value="{{ old('annee_scolaire', date('Y') . '-' . (date('Y') + 1)) }}" 
                                       required
                                       placeholder="2024-2025" 
                                       pattern="\d{4}-\d{4}"
                                       class="form-control form-control-lg @error('annee_scolaire') is-invalid @enderror">
                                @error('annee_scolaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Format: AAAA-AAAA (ex: 2024-2025)
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div class="alert alert-info border-start border-4 border-info mb-4 shadow-sm">
                                <div class="d-flex">
                                    <i class="fas fa-lightbulb fs-2 me-3"></i>
                                    <div>
                                        <p class="fw-bold mb-2">Prochaines étapes</p>
                                        <ul class="mb-0 small">
                                            <li>✓ Après création, vous pourrez importer la liste des élèves via Excel</li>
                                            <li>✓ Ajoutez les photos des élèves dans le dossier approprié</li>
                                            <li>✓ Générez les cartes scolaires et documents</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-3">
                                <button type="submit"
                                    class="btn btn-primary btn-lg flex-fill shadow">
                                    <i class="fas fa-save me-2"></i>
                                    Créer la Classe
                                </button>
                                <a href="{{ route('classes.index') }}"
                                    class="btn btn-secondary btn-lg shadow">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Guide -->
                <div class="card text-white shadow-lg mt-4 animate-fade-in" 
                     style="animation-delay: 0.2s; background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
                    <div class="card-body p-4">
                        <h2 class="h4 mb-4">
                            <i class="fas fa-question-circle me-2"></i>
                            Guide Rapide
                        </h2>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="bg-white bg-opacity-25 rounded p-4 h-100 text-center">
                                    <div class="fs-1 mb-3">1️⃣</div>
                                    <h3 class="h6 fw-bold mb-2">Créer la classe</h3>
                                    <p class="small mb-0 opacity-75">Remplissez le formulaire avec les informations de base</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-white bg-opacity-25 rounded p-4 h-100 text-center">
                                    <div class="fs-1 mb-3">2️⃣</div>
                                    <h3 class="h6 fw-bold mb-2">Importer les élèves</h3>
                                    <p class="small mb-0 opacity-75">Utilisez un fichier Excel pour ajouter tous les élèves</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-white bg-opacity-25 rounded p-4 h-100 text-center">
                                    <div class="fs-1 mb-3">3️⃣</div>
                                    <h3 class="h6 fw-bold mb-2">Générer les cartes</h3>
                                    <p class="small mb-0 opacity-75">Créez les cartes scolaires automatiquement</p>
                                </div>
                            </div>
                        </div>
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

        .bg-opacity-25 {
            background-color: rgba(255, 255, 255, 0.25) !important;
        }

        .opacity-75 {
            opacity: 0.75;
        }

        .shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }
    </style>

    <script>
        // Form validation feedback
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            if (!button) return;
            
            const originalContent = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...';
            
            // Restore button after 2 seconds in case of validation error
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalContent;
            }, 2000);
        });
    </script>
</x-app-layout>