<x-app-layout>
    <div class="container-fluid py-4">
        <!-- Welcome Header -->
        <div class="card shadow-lg mb-4 animate-fade-in" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
            <div class="card-body text-white p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-chart-line me-2"></i>
                            Tableau de Bord
                        </h1>
                        <p class="mb-0 opacity-75">
                            Bienvenue sur votre plateforme de gestion de pièces d'identité scolaire
                            <span class="ms-2">
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ now()->format('d/m/Y') }}
                            </span>
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div class="d-inline-block bg-white bg-opacity-25 rounded px-4 py-2">
                            <div class="small opacity-75">Année Scolaire</div>
                            <div class="h4 mb-0">{{ date('Y') }}-{{ date('Y') + 1 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques Principales -->
        <div class="row g-4 mb-4">
            <!-- Total Classes -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-lg border-0 h-100 animate-fade-in" 
                     style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%); animation-delay: 0.1s;">
                    <div class="card-body text-white p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="small opacity-75 text-uppercase mb-1">Total Classes</div>
                                <div class="h2 fw-bold mb-0">{{ $totalClasses ?? 0 }}</div>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-school fa-2x"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-arrow-up me-1 small"></i>
                            <small class="opacity-75">Classes actives cette année</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Élèves -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-lg border-0 h-100 animate-fade-in" 
                     style="background: linear-gradient(135deg, #198754 0%, #20c997 100%); animation-delay: 0.2s;">
                    <div class="card-body text-white p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="small opacity-75 text-uppercase mb-1">Total Élèves</div>
                                <div class="h2 fw-bold mb-0">{{ $totalStudents ?? 0 }}</div>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-users me-1 small"></i>
                            <small class="opacity-75">Inscrits cette année</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Garçons -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-lg border-0 h-100 animate-fade-in" 
                     style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); animation-delay: 0.3s;">
                    <div class="card-body text-white p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="small opacity-75 text-uppercase mb-1">Garçons</div>
                                <div class="h2 fw-bold mb-0">{{ $totalBoys ?? 0 }}</div>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-mars fa-2x"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-percent me-1 small"></i>
                            <small class="opacity-75">
                                {{ $totalStudents > 0 ? number_format(($totalBoys / $totalStudents) * 100, 1) : 0 }}% du total
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filles -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-lg border-0 h-100 animate-fade-in" 
                     style="background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%); animation-delay: 0.4s;">
                    <div class="card-body text-white p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="small opacity-75 text-uppercase mb-1">Filles</div>
                                <div class="h2 fw-bold mb-0">{{ $totalGirls ?? 0 }}</div>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-venus fa-2x"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-percent me-1 small"></i>
                            <small class="opacity-75">
                                {{ $totalStudents > 0 ? number_format(($totalGirls / $totalStudents) * 100, 1) : 0 }}% du total
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques et Informations -->
        <div class="row g-4 mb-4">
            <!-- Répartition par Classe -->
            <div class="col-lg-8">
                <div class="card shadow-lg h-100 animate-fade-in" style="animation-delay: 0.5s;">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            Répartition des Élèves par Classe
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Classe</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Garçons</th>
                                        <th class="text-center">Filles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($classes ?? [] as $classe)
                                    <tr>
                                        <td class="fw-semibold">
                                            <i class="fas fa-chalkboard-teacher text-primary me-2"></i>
                                            {{ $classe->nom }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary rounded-pill">{{ $classe->etudiants_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info rounded-pill">{{ $classe->effectif_garcons }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger rounded-pill">{{ $classe->effectif_filles }}</span>
                                        </td>
                                       
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            Aucune classe disponible
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques Supplémentaires -->
            <div class="col-lg-4">
                <!-- Ratio Garçons/Filles -->
                <div class="card shadow-lg mb-4 animate-fade-in" style="animation-delay: 0.6s;">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-balance-scale text-primary me-2"></i>
                            Ratio Garçons/Filles
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="position-relative d-inline-block">
                                <svg width="150" height="150" viewBox="0 0 150 150">
                                    <circle cx="75" cy="75" r="65" fill="none" stroke="#e5e7eb" stroke-width="15"/>
                                    <circle cx="75" cy="75" r="65" fill="none" stroke="#6366f1" stroke-width="15"
                                            stroke-dasharray="{{ $totalStudents > 0 ? (($totalBoys / $totalStudents) * 408) : 0 }} 408"
                                            stroke-dashoffset="102"
                                            transform="rotate(-90 75 75)"/>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle text-center">
                                    <div class="h4 mb-0">{{ $totalStudents > 0 ? number_format(($totalBoys / $totalStudents) * 100, 0) : 0 }}%</div>
                                    <small class="text-muted">Garçons</small>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="bg-primary bg-opacity-10 rounded p-3">
                                    <i class="fas fa-mars text-primary fa-2x mb-2"></i>
                                    <div class="h5 mb-0">{{ $totalBoys ?? 0 }}</div>
                                    <small class="text-muted">Garçons</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-danger bg-opacity-10 rounded p-3">
                                    <i class="fas fa-venus text-danger fa-2x mb-2"></i>
                                    <div class="h5 mb-0">{{ $totalGirls ?? 0 }}</div>
                                    <small class="text-muted">Filles</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cartes Générées -->
                <div class="card shadow-lg animate-fade-in" style="animation-delay: 0.7s;">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-id-card text-success me-2"></i>
                            Cartes Générées
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Photos disponibles</span>
                            <span class="badge bg-success rounded-pill fs-6">{{ $totalWithPhotos ?? 0 }}</span>
                        </div>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $totalStudents > 0 ? (($totalWithPhotos / $totalStudents) * 100) : 0 }}%">
                            </div>
                        </div>
                        <div class="text-center">
                            <small class="text-muted">
                                {{ $totalStudents > 0 ? number_format(($totalWithPhotos / $totalStudents) * 100, 1) : 0 }}% 
                                des élèves ont une photo
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Rapides -->
        <div class="row g-4">
            <div class="col-12">
                <div class="card shadow-lg animate-fade-in" style="animation-delay: 0.8s;">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt text-warning me-2"></i>
                            Actions Rapides
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Nouvelle Classe -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <a href="{{ route('classes.create') }}" 
                                   class="card border-0 text-decoration-none h-100 quick-action-card"
                                   style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
                                    <div class="card-body text-white text-center p-4">
                                        <i class="fas fa-plus-circle fa-3x mb-3"></i>
                                        <h6 class="fw-bold mb-2">Nouvelle Classe</h6>
                                        <small class="opacity-75">Créer une nouvelle classe</small>
                                    </div>
                                </a>
                            </div>

                            <!-- Importer Excel -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <a href="{{ route('import.index') }}" 
                                   class="card border-0 text-decoration-none h-100 quick-action-card"
                                   style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                                    <div class="card-body text-white text-center p-4">
                                        <i class="fas fa-file-excel fa-3x mb-3"></i>
                                        <h6 class="fw-bold mb-2">Importer Excel</h6>
                                        <small class="opacity-75">Importer des listes d'élèves</small>
                                    </div>
                                </a>
                            </div>

                            <!-- Générer Cartes -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <a href="{{ route('cards.index') }}" 
                                   class="card border-0 text-decoration-none h-100 quick-action-card"
                                   style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                                    <div class="card-body text-white text-center p-4">
                                        <i class="fas fa-id-card fa-3x mb-3"></i>
                                        <h6 class="fw-bold mb-2">Générer Cartes</h6>
                                        <small class="opacity-75">Créer les cartes scolaires</small>
                                    </div>
                                </a>
                            </div>

                            <!-- Statistiques -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <a href="{{ route('statistics.index') }}" 
                                   class="card border-0 text-decoration-none h-100 quick-action-card"
                                   style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);">
                                    <div class="card-body text-white text-center p-4">
                                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                                        <h6 class="fw-bold mb-2">Statistiques</h6>
                                        <small class="opacity-75">Voir les rapports détaillés</small>
                                    </div>
                                </a>
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
            animation: fade-in 0.5s ease-out forwards;
        }

        .bg-opacity-25 {
            background-color: rgba(255, 255, 255, 0.25) !important;
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

        .quick-action-card {
            transition: all 0.3s ease;
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1.5rem 4rem rgba(0, 0, 0, 0.2) !important;
        }

        .table thead {
            background: #f8f9fa;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .progress {
            background-color: #e9ecef;
        }

        @media (max-width: 768px) {
            .h2 {
                font-size: 1.5rem;
            }
            
            .quick-action-card .fa-3x {
                font-size: 2rem;
            }
        }
    </style>

    <script>
        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);

        // Observer tous les éléments avec la classe animate-fade-in
        document.querySelectorAll('.animate-fade-in').forEach(el => {
            observer.observe(el);
        });
    </script>
</x-app-layout>