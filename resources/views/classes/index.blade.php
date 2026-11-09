<x-app-layout>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="card shadow-lg mb-4 animate-fade-in">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h1 class="h2 mb-1">
                            <i class="fas fa-school text-primary"></i>
                            Gestion des Classes
                        </h1>
                        <p class="text-muted mb-0">Gérez vos classes et leurs élèves</p>
                    </div>
                    <a href="{{ route('classes.create') }}" class="btn btn-primary btn-lg shadow">
                        <i class="fas fa-plus me-2"></i> Nouvelle Classe
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages de succès -->
        @if(session('success'))
        <div class="alert alert-success shadow-lg animate-fade-in mb-4" id="successAlert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-3"></i>
                <span class="fw-semibold">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        <!-- Barre de recherche -->
        <div class="card shadow-lg mb-4 animate-fade-in">
            <div class="card-body">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute text-muted" 
                       style="left: 25px; top: 50%; transform: translateY(-50%);"></i>
                    <input type="text" 
                           class="form-control form-control-lg ps-5" 
                           id="searchInput"
                           placeholder="Rechercher une classe par nom, année scolaire ou examen...">
                </div>
                <div class="mt-2 text-muted small d-none" id="searchInfo">
                    <i class="fas fa-info-circle me-1"></i>
                    <span id="resultCount"></span>
                </div>
            </div>
        </div>

        <!-- Cartes de statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card shadow-lg border-0 animate-fade-in" style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
                    <div class="card-body text-white text-center p-4">
                        <i class="fas fa-school fa-3x mb-3"></i>
                        <div class="fs-2 fw-bold">{{ $classes->count() }}</div>
                        <div class="opacity-75">Total Classes</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card shadow-lg border-0 animate-fade-in" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                    <div class="card-body text-white text-center p-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <div class="fs-2 fw-bold">{{ $classes->sum('etudiants_count') }}</div>
                        <div class="opacity-75">Total Élèves</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card shadow-lg border-0 animate-fade-in" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                    <div class="card-body text-white text-center p-4">
                        <i class="fas fa-mars fa-3x mb-3"></i>
                        <div class="fs-2 fw-bold">{{ $classes->sum('effectif_garcons') }}</div>
                        <div class="opacity-75">Garçons</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card shadow-lg border-0 animate-fade-in" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                    <div class="card-body text-white text-center p-4">
                        <i class="fas fa-venus fa-3x mb-3"></i>
                        <div class="fs-2 fw-bold">{{ $classes->sum('effectif_filles') }}</div>
                        <div class="opacity-75">Filles</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message "Aucun résultat" -->
        <div class="card shadow-lg text-center py-5 d-none" id="noResults">
            <div class="card-body">
                <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3 text-muted">Aucune classe trouvée</h4>
                <p class="text-muted">Essayez avec d'autres termes de recherche</p>
            </div>
        </div>

        <!-- Grille des classes -->
        <div class="row g-4" id="classesGrid">
            @forelse($classes as $classe)
            <div class="col-12 col-md-6 col-lg-4 classe-card" 
                 data-nom="{{ strtolower($classe->nom) }}" 
                 data-annee="{{ strtolower($classe->annee_scolaire) }}"
                 data-exam="{{ strtolower($classe->exam ?? '') }}">
                <div class="card shadow-lg h-100 animate-fade-in" style="transition: all 0.3s ease;">
                    <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #16a34a 0%, #14b8a6 100%);">
                        <h3 class="h5 mb-1">
                            <i class="fas fa-chalkboard-teacher me-2"></i>
                            {{ $classe->nom }}
                        </h3>
                        <small class="opacity-75">{{ $classe->annee_scolaire }}</small>
                    </div>
                    <div class="card-body">
                        <!-- Stats -->
                        <div class="row g-3 mb-3">
                            <div class="col-4 text-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                     style="width: 48px; height: 48px;">
                                    <i class="fas fa-users text-primary"></i>
                                </div>
                                <div class="fw-bold fs-4">{{ $classe->etudiants_count }}</div>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                     style="width: 48px; height: 48px;">
                                    <i class="fas fa-mars text-primary"></i>
                                </div>
                                <div class="fw-bold fs-4">{{ $classe->effectif_garcons }}</div>
                                <small class="text-muted">Garçons</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                     style="width: 48px; height: 48px;">
                                    <i class="fas fa-venus text-danger"></i>
                                </div>
                                <div class="fw-bold fs-4">{{ $classe->effectif_filles }}</div>
                                <small class="text-muted">Filles</small>
                            </div>
                        </div>

                        <!-- Examen -->
                        @if($classe->exam)
                        <div class="alert alert-warning mb-3 py-2">
                            <small>
                                <i class="fas fa-graduation-cap me-2"></i>
                                <strong>Examen:</strong> {{ $classe->exam }}
                            </small>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="d-grid gap-2">
                            <a href="{{ route('classes.show', $classe->id) }}" class="btn btn-primary shadow">
                                <i class="fas fa-eye me-2"></i> Voir les élèves
                            </a>
                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="{{ route('classes.edit', $classe) }}" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="fas fa-edit me-1"></i> Modifier
                                    </a>
                                </div>
                                <div class="col-6">
                                    <form action="{{ route('classes.destroy', $classe) }}" method="POST" 
                                          onsubmit="return confirm('Supprimer cette classe et tous ses élèves ?');" class="w-100">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                            <i class="fas fa-trash me-1"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card shadow-lg h-100 text-center animate-fade-in">
                    <div class="card-body d-flex flex-column justify-content-center py-5">
                        <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">Aucune classe disponible</h5>
                        <p class="text-muted">Créez votre première classe pour commencer</p>
                        <a href="{{ route('classes.create') }}" class="btn btn-primary mt-2 shadow">
                            <i class="fas fa-plus me-2"></i> Créer votre première classe
                        </a>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Actions rapides -->
        <div class="mt-5 animate-fade-in">
            <h2 class="h4 mb-4 text-light">
                <i class="fas fa-bolt me-2 text-light"></i> Actions rapides
            </h2>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <a href="{{ route('import.index') }}" class="card shadow-lg text-decoration-none h-100" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                        <div class="card-body text-white p-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-excel fa-3x me-3"></i>
                                <div>
                                    <div class="fw-bold fs-5">Importer Excel</div>
                                    <small class="opacity-75">Ajoutez des élèves par lots</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
              
                <div class="col-12 col-md-6">
                    <a href="{{ route('statistics.index') }}" class="card shadow-lg text-decoration-none h-100" style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
                        <div class="card-body text-white p-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chart-line fa-3x me-3"></i>
                                <div>
                                    <div class="fw-bold fs-5">Statistiques</div>
                                    <small class="opacity-75">Voir les rapports</small>
                                </div>
                            </div>
                        </div>
                    </a>
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

        .classe-card .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1.5rem 4rem rgba(0, 0, 0, 0.2) !important;
        }

        .bg-opacity-10 {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }

        .bg-danger.bg-opacity-10 {
            background-color: rgba(var(--bs-danger-rgb), 0.1) !important;
        }

        .opacity-75 {
            opacity: 0.75;
        }

        .shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }
    </style>

    <script>
        const searchInput = document.getElementById('searchInput');
        const searchInfo = document.getElementById('searchInfo');
        const resultCount = document.getElementById('resultCount');
        const noResults = document.getElementById('noResults');
        const classesGrid = document.getElementById('classesGrid');
        const classeCards = document.querySelectorAll('.classe-card');

        function filterClasses() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            classeCards.forEach(card => {
                const nom = card.dataset.nom;
                const annee = card.dataset.annee;
                const exam = card.dataset.exam;

                const matches = nom.includes(searchTerm) || 
                              annee.includes(searchTerm) || 
                              exam.includes(searchTerm);

                if (matches) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (searchTerm !== '') {
                searchInfo.classList.remove('d-none');
                resultCount.textContent = `${visibleCount} classe${visibleCount !== 1 ? 's' : ''} trouvée${visibleCount !== 1 ? 's' : ''}`;
                
                noResults.classList.toggle('d-none', visibleCount > 0);
                classesGrid.classList.toggle('d-none', visibleCount === 0);
            } else {
                searchInfo.classList.add('d-none');
                noResults.classList.add('d-none');
                classesGrid.classList.remove('d-none');
            }
        }

        searchInput.addEventListener('input', filterClasses);

        // Auto-hide success message
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 300);
            }, 5000);
        }
    </script>
</x-app-layout>