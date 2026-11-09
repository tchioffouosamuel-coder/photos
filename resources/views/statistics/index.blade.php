<x-app-layout>
    <div class="container py-4">
        <!-- Header -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h2 mb-2">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Statistiques Générales
                        </h1>
                        <p class="text-muted mb-0">Vue d'ensemble de tous les établissements</p>
                    </div>
                
                </div>
            </div>
        </div>

        <!-- Global Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card text-white bg-primary shadow-lg">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <i class="fas fa-school fa-3x opacity-75"></i>
                            <div class="text-end">
                                <h3 class="mb-0">{{ $totalClasses }}</h3>
                                <small>Classes</small>
                            </div>
                        </div>
                        <hr class="border-white opacity-50">
                        <small class="opacity-75">Total dans le système</small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card text-white bg-success shadow-lg">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <i class="fas fa-users fa-3x opacity-75"></i>
                            <div class="text-end">
                                <h3 class="mb-0">{{ $totalStudents }}</h3>
                                <small>Élèves</small>
                            </div>
                        </div>
                        <hr class="border-white opacity-50">
                        <small class="opacity-75">Effectif total</small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card text-white shadow-lg" style="background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <i class="fas fa-camera fa-3x opacity-75"></i>
                            <div class="text-end">
                                <h3 class="mb-0">{{ $studentsWithPhotos }}</h3>
                                <small>Avec Photo</small>
                            </div>
                        </div>
                        <hr class="border-white opacity-50">
                        <small class="opacity-75">{{ number_format($photoPercentage, 1) }}% complété</small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card text-white bg-danger shadow-lg">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <i class="fas fa-user-times fa-3x opacity-75"></i>
                            <div class="text-end">
                                <h3 class="mb-0">{{ $studentsWithoutPhotos }}</h3>
                                <small>Sans Photo</small>
                            </div>
                        </div>
                        <hr class="border-white opacity-50">
                        <a href="{{ route('statistics.no-photos') }}" class="text-white text-decoration-none opacity-75">
                            Voir la liste <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <!-- Gender Distribution -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-venus-mars text-primary me-2"></i>
                            Répartition par Sexe
                        </h5>
                      
                        <div class="row g-3 mt-3">
                            <div class="col-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center">
                                        <i class="fas fa-mars fa-2x text-primary mb-2"></i>
                                        <h4 class="mb-0">{{ $totalBoys }}</h4>
                                        <small class="text-muted">Garçons ({{ number_format($totalBoys / $totalStudents * 100, 1) }}%)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center">
                                        <i class="fas fa-venus fa-2x text-danger mb-2"></i>
                                        <h4 class="mb-0">{{ $totalGirls }}</h4>
                                        <small class="text-muted">Filles ({{ number_format($totalGirls / $totalStudents * 100, 1) }}%)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="col-12 col-lg-6">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-chart-pie text-primary me-2"></i>
                            Répartition par Statut
                        </h5>
                     
                        <div class="row g-3 mt-3">
                            <div class="col-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                        <h4 class="mb-0">{{ $normalStudents }}</h4>
                                        <small class="text-muted">Normal</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center">
                                        <i class="fas fa-redo fa-2x text-warning mb-2"></i>
                                        <h4 class="mb-0">{{ $redoublants }}</h4>
                                        <small class="text-muted">Redoublants</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes Table -->
        <div class="card shadow-lg mb-4">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Détails par Classe
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Classe</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Garçons</th>
                                <th class="text-center">Filles</th>
                                <th class="text-center">Avec Photo</th>
                                <th class="text-center">Sans Photo</th>
                                <th class="text-center">% Photos</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classes as $classe)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-chalkboard-teacher text-primary me-2"></i>
                                        <div>
                                            <div class="fw-bold">{{ $classe->nom }}</div>
                                            <small class="text-muted">{{ $classe->annee_scolaire }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <strong>{{ $classe->effectif_total }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $classe->effectif_garcons }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ $classe->effectif_filles }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $classe->students_with_photos }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ $classe->students_without_photos }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="progress me-2" style="width: 100px; height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $classe->photo_percentage }}%"
                                                 aria-valuenow="{{ $classe->photo_percentage }}" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small class="fw-bold">{{ number_format($classe->photo_percentage, 0) }}%</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('classes.show', $classe) }}" class="text-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Photo Completion Progress -->
        <div class="card text-white shadow-lg" style="background: linear-gradient(135deg, #059669 0%, #14b8a6 100%);">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fas fa-tasks me-2"></i>
                    Progression Photos
                </h5>
                <div class="bg-white bg-opacity-25 rounded p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fs-5 fw-bold">Complétion globale</span>
                        <span class="fs-3 fw-bold">{{ number_format($photoPercentage, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-white text-success fw-bold" 
                             role="progressbar" 
                             style="width: {{ $photoPercentage }}%"
                             aria-valuenow="{{ $photoPercentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $studentsWithPhotos }}/{{ $totalStudents }}
                        </div>
                    </div>
                    <small class="d-block mt-2 opacity-75">
                        Il reste {{ $studentsWithoutPhotos }} photo(s) à collecter
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Garçons', 'Filles'],
                datasets: [{
                    data: [{{ $totalBoys }}, {{ $totalGirls }}],
                    backgroundColor: ['#0d6efd', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Normal', 'Redoublant'],
                datasets: [{
                    data: [{{ $normalStudents }}, {{ $redoublants }}],
                    backgroundColor: ['#198754', '#ffc107'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>

    <style>
        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none;
            }
        }
        
        .bg-opacity-25 {
            background-color: rgba(255, 255, 255, 0.25) !important;
        }
        
        .opacity-75 {
            opacity: 0.75;
        }
    </style>
</x-app-layout>