<x-app-layout>
    <div class="container py-4">
        <!-- Header -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="h2 mb-2">
                            <i class="fas fa-id-card text-success me-2"></i>
                            Génération des Cartes Scolaires
                        </h1>
                        <p class="text-muted mb-0">Générez les cartes et documents d'identification</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Generation Card -->
        <div class="card text-white shadow-lg mb-4" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <div class="col-12">
                        <h2 class="h3 mb-3 d-flex align-items-center">
                            <i class="fas fa-globe fa-3x me-3"></i>
                            <span>Génération Globale</span>
                        </h2>
                        <p class="mb-4 fs-5 opacity-75">
                            Générez toutes les cartes scolaires de toutes les classes en un seul fichier PDF
                        </p>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="bg-white bg-opacity-25 rounded p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-school fa-2x me-3"></i>
                                        <div>
                                            <h4 class="mb-0">{{ $classes->count() }}</h4>
                                            <small class="opacity-75">Classes</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="bg-white bg-opacity-25 rounded p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-users fa-2x me-3"></i>
                                        <div>
                                            <h4 class="mb-0">{{ $classes->sum('etudiants_count') }}</h4>
                                            <small class="opacity-75">Élèves</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="bg-white bg-opacity-25 rounded p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-camera fa-2x me-3"></i>
                                        <div>
                                            <h4 class="mb-0">{{ $totalWithPhotos }}</h4>
                                            <small class="opacity-75">Avec Photo</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('cards.generate-all-global') }}" method="POST" id="globalGenerationForm">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-light btn-lg text-primary fw-bold shadow">
                                <i class="fas fa-file-pdf fs-4 me-2"></i>
                                Générer Toutes les Cartes (PDF Global)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow animate-fade-in" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-3"></i>
                <strong>{{ session('success') }}</strong>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow animate-fade-in" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                <strong>{{ session('error') }}</strong>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Search Bar -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <div class="position-relative">
                    <input 
                        type="text" 
                        id="searchInput"
                        placeholder="Rechercher une classe..."
                        class="form-control form-control-lg ps-5"
                    >
                    <i class="fas fa-search position-absolute text-muted" 
                       style="left: 25px; top: 50%; transform: translateY(-50%);"></i>
                </div>
            </div>
        </div>

        <!-- Classes Grid -->
        <div class="row g-4">
            @forelse($classes as $classe)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card classe-card shadow-lg h-100 border-0" 
                     data-nom="{{ strtolower($classe->nom) }}"
                     style="transition: all 0.3s ease;">
                    <!-- Header -->
                    <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #16a34a 0%, #14b8a6 100%);">
                        <h5 class="mb-1 d-flex align-items-center">
                            <i class="fas fa-chalkboard-teacher me-2"></i>
                            {{ $classe->nom }}
                        </h5>
                        <small class="opacity-75">{{ $classe->annee_scolaire }}</small>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <!-- Stats -->
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                         style="width: 48px; height: 48px;">
                                        <i class="fas fa-users text-primary"></i>
                                    </div>
                                    <h4 class="mb-0">{{ $classe->etudiants_count }}</h4>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                         style="width: 48px; height: 48px;">
                                        <i class="fas fa-camera text-success"></i>
                                    </div>
                                    <h4 class="mb-0">{{ $classe->students_with_photos }}</h4>
                                    <small class="text-muted">Photos</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                         style="width: 48px; height: 48px;">
                                        <i class="fas fa-user-times text-danger"></i>
                                    </div>
                                    <h4 class="mb-0">{{ $classe->students_without_photos }}</h4>
                                    <small class="text-muted">Sans</small>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="fw-semibold text-muted">Photos complètes</small>
                                <small class="fw-bold text-success">{{ number_format($classe->photo_percentage, 0) }}%</small>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $classe->photo_percentage }}%"
                                     aria-valuenow="{{ $classe->photo_percentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2">
                            <form action="{{ route('classes.show', $classe) }}" method="GET">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100 fw-semibold">
                                    <i class="fas fa-camera me-2"></i>
                                    Filmer et Gerer
                                </button>
                            </form>
                            <!-- Generate Cards -->
                            <form action="{{ route('cards.generate', $classe) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 fw-semibold">
                                    <i class="fas fa-id-card me-2"></i>
                                    Cartes Scolaires
                                </button>
                            </form>

                            <!-- Additional Actions -->
                            <div class="row g-2">
                                <div class="col-6">
                                    <form action="{{ route('cards.generate-processed-photos', $classe) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-images me-1"></i>
                                            Photos ZIP
                                        </button>
                                    </form>
                                </div>
                                <div class="col-6">
                                    <form action="{{ route('cards.generate-printable-photos', $classe) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm w-100" style="background-color: #7c3aed; border-color: #7c3aed;">
                                            <i class="fas fa-print me-1"></i>
                                            Photos 4x4
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Generate All for Class -->
                            <form action="{{ route('cards.generate-all', $classe) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm w-100 fw-semibold" style="background-color: #4f46e5; border-color: #4f46e5; color: white;">
                                    <i class="fas fa-download me-1"></i>
                                    Tout Générer (ZIP)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card shadow-lg text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-inbox display-1 text-muted mb-4"></i>
                        <h4 class="text-muted mb-4">Aucune classe disponible</h4>
                        <a href="{{ route('classes.create') }}" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-plus me-2"></i>
                            Créer une classe
                        </a>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Info Section -->
        <div class="card text-white shadow-lg mt-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    Types de génération disponibles
                </h5>
                <div class="row g-3">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="bg-white bg-opacity-25 rounded p-4 text-center">
                            <i class="fas fa-id-card fa-3x mb-3"></i>
                            <h6 class="fw-bold mb-2">Cartes Scolaires</h6>
                            <small class="opacity-75">PDF avec cartes individuelles pour chaque élève</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="bg-white bg-opacity-25 rounded p-4 text-center">
                            <i class="fas fa-images fa-3x mb-3"></i>
                            <h6 class="fw-bold mb-2">Photos Traitées</h6>
                            <small class="opacity-75">ZIP avec photos formatées et annotées</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="bg-white bg-opacity-25 rounded p-4 text-center">
                            <i class="fas fa-print fa-3x mb-3"></i>
                            <h6 class="fw-bold mb-2">Photos 4x4</h6>
                            <small class="opacity-75">PDF optimisé pour impression format 4x4</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="bg-white bg-opacity-25 rounded p-4 text-center">
                            <i class="fas fa-download fa-3x mb-3"></i>
                            <h6 class="fw-bold mb-2">Tout Générer</h6>
                            <small class="opacity-75">ZIP complet avec tous les documents</small>
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

        .classe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        .bg-opacity-10 {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }

        .bg-success.bg-opacity-10 {
            background-color: rgba(var(--bs-success-rgb), 0.1) !important;
        }

        .bg-danger.bg-opacity-10 {
            background-color: rgba(var(--bs-danger-rgb), 0.1) !important;
        }

        .bg-opacity-25 {
            background-color: rgba(255, 255, 255, 0.25) !important;
        }

        .opacity-75 {
            opacity: 0.75;
        }
    </style>

    <script>
        const searchInput = document.getElementById('searchInput');
        const classeCards = document.querySelectorAll('.classe-card');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            classeCards.forEach(card => {
                const nom = card.dataset.nom;
                const matches = nom.includes(searchTerm);
                const colParent = card.closest('.col-12, .col-md-6, .col-lg-4');
                
                if (colParent) {
                    colParent.style.display = matches ? '' : 'none';
                }
                
                if (matches) {
                    card.classList.add('animate-fade-in');
                }
            });
        });

        // Show loading on form submit
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const button = this.querySelector('button[type="submit"]');
                if (!button) return;
                
                const originalContent = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Génération...';
                
                // Restore button after 2 seconds (in case of quick redirect)
                setTimeout(() => {
                    button.disabled = false;
                    button.innerHTML = originalContent;
                }, 2000);
            });
        });
    </script>
</x-app-layout>