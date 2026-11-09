<x-app-layout>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('classes.show', $classe) }}" class="text-primary me-3">
                            <i class="fas fa-arrow-left fs-3"></i>
                        </a>
                        <div>
                            <h1 class="h2 mb-1">
                                <i class="fas fa-user-graduate text-warning"></i>
                                Candidats Libres
                            </h1>
                            <p class="text-muted mb-0">{{ $classe->nom }} - {{ $classe->exam }}</p>
                        </div>
                    </div>
                    <a href="{{ route('candidats-libres.create', $classe) }}" class="btn btn-warning btn-lg shadow">
                        <i class="fas fa-plus me-2"></i> Nouveau Candidat Libre
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        @if(session('success'))
        <div class="alert alert-success shadow-lg mb-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger shadow-lg mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
        @endif

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="card-body text-white text-center p-4">
                        <i class="fas fa-user-graduate fa-3x mb-3"></i>
                        <div class="fs-2 fw-bold">{{ $candidats->count() }}</div>
                        <div class="opacity-75">Total Candidats</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
                    <div class="card-body text-white text-center p-4">
                        <i class="fas fa-mars fa-3x mb-3"></i>
                        <div class="fs-2 fw-bold">{{ $candidats->where('sexe', 'M')->count() }}</div>
                        <div class="opacity-75">Garçons</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                    <div class="card-body text-white text-center p-4">
                        <i class="fas fa-venus fa-3x mb-3"></i>
                        <div class="fs-2 fw-bold">{{ $candidats->where('sexe', 'F')->count() }}</div>
                        <div class="opacity-75">Filles</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                    <div class="card-body text-white text-center p-4">
                        <i class="fas fa-camera fa-3x mb-3"></i>
                        <div class="fs-2 fw-bold">{{ $candidats->whereNotNull('photo')->count() }}</div>
                        <div class="opacity-75">Avec Photo</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-bolt text-warning me-2"></i>
                    Actions rapides
                </h5>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('candidats-libres.photos-4x4', $classe) }}" 
                       class="btn btn-primary"
                       @if($candidats->whereNotNull('photo')->isEmpty()) disabled @endif>
                        <i class="fas fa-print me-2"></i>Photos 4x4
                    </a>
                    <a href="{{ route('candidats-libres.photos-pdf', $classe) }}" 
                       class="btn btn-info"
                       @if($candidats->whereNotNull('photo')->isEmpty()) disabled @endif>
                        <i class="fas fa-file-pdf me-2"></i>Photos PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Liste des candidats -->
        @if($candidats->isEmpty())
        <div class="card shadow-lg text-center py-5">
            <div class="card-body">
                <i class="fas fa-user-graduate text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3 text-muted">Aucun candidat libre</h4>
                <p class="text-muted">Ajoutez votre premier candidat libre</p>
                <a href="{{ route('candidats-libres.create', $classe) }}" class="btn btn-warning mt-2">
                    <i class="fas fa-plus me-2"></i>Ajouter un candidat
                </a>
            </div>
        </div>
        @else
        <div class="card shadow-lg">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Photo</th>
                                <th>Nom</th>
                                <th>Date Naissance</th>
                                <th>Lieu</th>
                                <th>Sexe</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($candidats as $candidat)
                            <tr>
                                <td>
                                    @if($candidat->photo)
                                        <img src="{{ $candidat->photo_url }}" 
                                             alt="{{ $candidat->nom }}"
                                             class="rounded"
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                             style="width: 60px; height: 60px;">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <div class="fw-bold">{{ $candidat->nom }}</div>
                                    <small class="text-muted">Code: CL</small>
                                </td>
                                <td class="align-middle">
                                    {{ $candidat->date_naissance->format('d/m/Y') }}
                                </td>
                                <td class="align-middle">{{ $candidat->lieu_naissance }}</td>
                                <td class="align-middle">
                                    @if($candidat->sexe === 'M')
                                        <span class="badge bg-primary">Masculin</span>
                                    @else
                                        <span class="badge bg-danger">Féminin</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('candidats-libres.edit', [$classe, $candidat]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('candidats-libres.destroy', [$classe, $candidat]) }}" 
                                              method="POST"
                                              onsubmit="return confirm('Supprimer ce candidat ?');"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>