<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Détails de la Classe</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            --success-gradient: linear-gradient(135deg, #16a34a 0%, #14b8a6 100%);
        }

        body {
            background-color: #f8f9fa;
            -webkit-tap-highlight-color: transparent;
        }

        /* Zone d'upload améliorée */
        .upload-zone {
            min-height: 180px;
            border: 3px dashed #cbd5e1;
            border-radius: 1rem;
            padding: 2rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .upload-zone:active {
            transform: scale(0.98);
        }

        .upload-zone.drag-over {
            border-color: #4f46e5;
            border-style: solid;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            transform: scale(1.02);
        }

        /* Boutons d'action photo optimisés mobile */
        .photo-action-btn {
            min-height: 54px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
            border: none;
        }

        .photo-action-btn:active {
            transform: scale(0.96);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        /* Vidéo/Photo responsive */
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            border-radius: 1rem;
            overflow: hidden;
            background: #000;
        }

        #videoElement,
        #capturedPhoto {
            width: 100%;
            height: auto;
            min-height: 280px;
            max-height: 400px;
            object-fit: cover;
            display: block;
        }
 .stat-box {
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
        }
        
        .stat-box i {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }
        
        .stat-box .value {
            font-size: 1.75rem;
            font-weight: bold;
            color: #1f2937;
        }
        
        .stat-box .label {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .bg-gradient-primary {
            background: var(--primary-gradient);
        }
        
        .bg-gradient-success {
            background: var(--success-gradient);
        }
        
        .table-hover tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .photo-preview {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .photo-preview:hover {
            transform: scale(1.1);
        }
        
        .photo-placeholder {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .badge-custom {
            padding: 0.375rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .action-btn {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        
        /* Bouton switch caméra flottant */
        .switch-camera-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 10;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #4f46e5;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .switch-camera-btn:active {
            transform: scale(0.9);
        }

        /* Loading overlay fullscreen */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            color: white;
        }

        .loading-overlay .spinner-border {
            width: 4rem;
            height: 4rem;
            border-width: 0.4rem;
        }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            width: calc(100% - 2rem);
            max-width: 400px;
        }

        .custom-toast {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Info compression */
        .compression-info {
            font-size: 0.85rem;
            color: #64748b;
            padding: 0.5rem;
            background: #f1f5f9;
            border-radius: 0.5rem;
            margin-top: 0.75rem;
        }

        /* Modal fullscreen sur mobile */
        @media (max-width: 768px) {
            .modal-fullscreen-mobile {
                max-width: 100%;
                margin: 0;
                height: 100vh;
            }

            .modal-fullscreen-mobile .modal-content {
                height: 100vh;
                border-radius: 0;
            }

            .modal-fullscreen-mobile .modal-body {
                overflow-y: auto;
                max-height: calc(100vh - 80px);
            }

            #videoElement,
            #capturedPhoto {
                min-height: 250px;
                max-height: 350px;
            }
        }

        /* Preview photo dans modal */
        #uploadPreview {
            max-height: 320px;
            width: auto;
            max-width: 100%;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Tabs améliorés pour mobile */
        .nav-pills .nav-link {
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .nav-pills .nav-link:active {
            transform: scale(0.96);
        }

        /* Autres styles existants gardés */
        .stat-box {
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-box:hover {
            transform: translateY(-5px);
        }

        .photo-preview {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .photo-preview:hover {
            transform: scale(1.1);
        }

        .photo-placeholder {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <!-- Toast Container -->
    <div class="toast-container"></div>

    <div class="container-fluid px-3 px-md-4 py-4">
        <!-- Header -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('classes.index') }}" class="btn btn-link text-primary me-3 p-0">
                                <i class="fas fa-arrow-left fs-4"></i>
                            </a>
                            <div>
                                <h1 class="h2 mb-1">{{ $classe->nom }}</h1>
                                <p class="text-muted mb-0">{{ $classe->annee_scolaire }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <a href="{{ route('etudiants.create', $classe) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-user-plus me-1"></i> Nouvel Élève
                            </a>
                            <a href="{{ route('classes.edit', $classe) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                            <form action="{{ route('cards.generate', $classe) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-info btn-sm text-white">
                                    <i class="fas fa-id-card me-1"></i> Générer Cartes
                                </button>
                            </form>
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg">
                <div class="stat-box bg-primary bg-opacity-10 border border-primary">
                    <i class="fas fa-users text-primary"></i>
                    <div class="value">{{ $classe->effectif_total }}</div>
                    <div class="label">Total élèves</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <div class="stat-box bg-info bg-opacity-10 border border-info">
                    <i class="fas fa-mars text-info"></i>
                    <div class="value">{{ $classe->effectif_garcons }}</div>
                    <div class="label">Garçons</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <div class="stat-box bg-danger bg-opacity-10 border border-danger">
                    <i class="fas fa-venus text-danger"></i>
                    <div class="value">{{ $classe->effectif_filles }}</div>
                    <div class="label">Filles</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <div class="stat-box bg-success bg-opacity-10 border border-success">
                    <i class="fas fa-camera text-success"></i>
                    <div class="value">{{ $classe->etudiants()->whereNotNull('photo')->count() }}</div>
                    <div class="label">Avec photo</div>
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg">
                <div class="stat-box bg-warning bg-opacity-10 border border-warning">
                    <i class="fas fa-graduation-cap text-warning"></i>
                    <div class="value fs-6">{{ $classe->exam ?? 'N/A' }}</div>
                    <div class="label">Examen</div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>{{ session('success') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Search and Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <input type="text" id="searchInput" placeholder="Rechercher un élève (nom, matricule...)"
                            class="form-control">
                    </div>
                    <div class="col-6 col-md-3">
                        <select id="filterSexe" class="form-select">
                            <option value="">Tous les sexes</option>
                            <option value="M">Garçons</option>
                            <option value="F">Filles</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select id="filterStatut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="N">Normal</option>
                            <option value="R">Redoublant</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-list me-2"></i>
                        Liste des Élèves
                    </h2>
                    <span id="countDisplay">{{ $classe->etudiants->count() }} élèves</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="studentsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">N°</th>
                            <th>Photo</th>
                            <th>Matricules</th>
                            <th>Nom & Prénoms</th>
                            <th class="d-none d-md-table-cell">Date Naissance</th>
                            <th class="d-none d-lg-table-cell">Lieu Naissance</th>
                            <th>Sexe</th>
                            <th class="d-none d-md-table-cell">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classe->etudiants as $index => $etudiant)
                        <tr class="student-row" data-nom="{{ strtolower($etudiant->nom . ' ' . $etudiant->prenoms) }}"
                            data-matricule="{{ $etudiant->matricule_national }}" data-sexe="{{ $etudiant->sexe }}"
                            data-statut="{{ $etudiant->statut }}">
                            <td class="text-center fw-bold">{{ $index + 1 }}</td>
                            <td>
                                <div class="photo-preview-container"
                                    onclick="openPhotoModal({{ $etudiant->id }}, '{{ $etudiant->nom }}', '{{ $etudiant->prenoms }}', '{{ $etudiant->photo ? photo_url('photos/' . $classe->nom . '/' . $etudiant->photo) : '' }}')">
                                    @if ($etudiant->photo)
                                    <img src="{{ photo_url('photos/' . $classe->nom . '/' . $etudiant->photo) }}"
                                        alt="{{ $etudiant->nom }}" class="photo-preview border border-2 border-primary"
                                        id="student-photo-{{ $etudiant->id }}">
                                    @else
                                    <div class="photo-placeholder border border-2"
                                        id="student-photo-{{ $etudiant->id }}">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $etudiant->matricule_national ?: 'N/A' }}</div>
                                <small class="text-muted">Int: {{ $etudiant->matricule_interne ?: 'N/A' }}</small>
                            </td>
                            <td>
                                <div class="fw-bold">{{ strtoupper($etudiant->nom) }}</div>
                                <small class="text-muted">{{ $etudiant->prenoms }}</small>
                            </td>
                            <td class="d-none d-md-table-cell">
                                {{ $etudiant->date_naissance ? $etudiant->date_naissance->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td class="d-none d-lg-table-cell">
                                {{ strtoupper($etudiant->lieu_naissance) }}
                            </td>
                            <td>
                                @if ($etudiant->sexe === 'M')
                                <span class="badge bg-primary badge-custom">
                                    <i class="fas fa-mars me-1"></i> M
                                </span>
                                @else
                                <span class="badge bg-danger badge-custom">
                                    <i class="fas fa-venus me-1"></i> F
                                </span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if ($etudiant->statut === 'R')
                                <span class="badge bg-warning text-dark badge-custom">Redoublant</span>
                                @else
                                <span class="badge bg-success badge-custom">Normal</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('etudiants.edit', [$classe, $etudiant]) }}"
                                        class="btn btn-primary btn-sm action-btn" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('etudiants.destroy', [$classe, $etudiant]) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet élève ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm action-btn"
                                            title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                                <p class="text-muted mt-3 mb-3">Aucun élève dans cette classe</p>
                                <a href="{{ route('etudiants.create', $classe) }}" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Ajouter un élève
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export Actions -->
        @if ($classe->etudiants->count() > 0)
        <div class="card bg-gradient-success text-white shadow-lg mt-4">
            <div class="card-body p-4">
                <h2 class="h4 mb-4">
                    <i class="fas fa-download me-2"></i>
                    Exporter les données
                </h2>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <a href="#" onclick="alert('Fonctionnalité bientôt disponible'); return false;"
                            class="btn btn-light w-100 text-success fw-bold py-3">
                            <i class="fas fa-file-excel fs-4 d-block mb-2"></i>
                            Export Excel
                        </a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="#" onclick="alert('Fonctionnalité bientôt disponible'); return false;"
                            class="btn btn-light w-100 text-success fw-bold py-3">
                            <i class="fas fa-file-pdf fs-4 d-block mb-2"></i>
                            Export PDF
                        </a>
                    </div>
                    <div class="col-12 col-md-4">
                        
                        <!-- APRÈS (✅ Correct) -->
<form action="{{ route('cards.generate', $classe) }}" method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-light w-100 text-success fw-bold py-3">
        <i class="fas fa-id-card fs-4 d-block mb-2"></i> Générer Cartes
    </button>
</form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Photo Modal OPTIMISÉ -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-mobile">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <div>
                        <h5 class="modal-title" id="modalStudentName">Photo de l'élève</h5>
                        <small class="opacity-75">Caméra ou Galerie</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Photo actuelle -->
                    <div class="text-center mb-3">
                        <div class="d-inline-block border border-3 rounded-3 shadow-sm"
                            style="width: 120px; height: 120px; overflow: hidden;">
                            <img id="currentPhotoPreview" src="" alt="Photo actuelle"
                                class="w-100 h-100 object-fit-cover d-none">
                            <div id="noPhotoPlaceholder"
                                class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light">
                                <i class="fas fa-user text-muted" style="font-size: 2.5rem;"></i>
                                <p class="text-muted mt-1 small mb-0">Pas de photo</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-pills nav-fill mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="capture-tab" data-bs-toggle="tab"
                                data-bs-target="#captureSection" type="button">
                                <i class="fas fa-camera me-2"></i>Caméra
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="upload-tab" data-bs-toggle="tab"
                                data-bs-target="#uploadSection" type="button">
                                <i class="fas fa-images me-2"></i>Galerie
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- CAMÉRA EN PREMIER (priorité mobile) -->
                        <div class="tab-pane fade show active" id="captureSection">
                            <div class="camera-container mb-3">
                                <video id="videoElement" autoplay playsinline muted></video>
                                <canvas id="canvasElement" class="d-none"></canvas>
                                <img id="capturedPhoto" class="d-none" alt="Photo capturée">

                                <!-- Bouton switch caméra -->
                                <button type="button" id="switchCameraBtn" class="switch-camera-btn d-none"
                                    onclick="switchCamera()" title="Changer de caméra">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>

                            <!-- Actions caméra -->
                            <div class="d-grid gap-2">
                                <button type="button" id="startCameraBtn" onclick="startCamera()"
                                    class="btn btn-success photo-action-btn">
                                    <i class="fas fa-video me-2"></i>
                                    Démarrer la Caméra
                                </button>

                                <button type="button" id="captureBtn" onclick="capturePhoto()"
                                    class="btn btn-primary photo-action-btn d-none">
                                    <i class="fas fa-camera me-2"></i>
                                    📸 Prendre la Photo
                                </button>

                                <div id="captureActions" class="d-none">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <button type="button" onclick="retakePhoto()"
                                                class="btn btn-warning photo-action-btn w-100">
                                                <i class="fas fa-redo me-1"></i>
                                                Reprendre
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" onclick="saveCapturedPhoto()"
                                                class="btn btn-success photo-action-btn w-100">
                                                <i class="fas fa-check me-1"></i>
                                                Valider
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GALERIE/UPLOAD -->
                        <div class="tab-pane fade" id="uploadSection">
                            <form id="uploadPhotoForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="studentId" name="etudiant_id">

                                <div class="upload-zone" id="uploadZone">
                                    <input type="file" id="photoInput" name="photo" accept="image/*"
                                        capture="environment" class="d-none" onchange="previewUploadedPhoto(event)">
                                    <label for="photoInput" class="d-block m-0 p-2" style="cursor: pointer;">
                                        <i class="fas fa-cloud-upload-alt text-primary mb-2"
                                            style="font-size: 3rem;"></i>
                                        <p class="fw-semibold mb-1">Toucher pour choisir</p>
                                        <small class="text-muted d-block">JPG, PNG • Max 10MB</small>
                                        <div class="compression-info mt-2">
                                            <i class="fas fa-compress-alt me-1"></i>
                                            Compression auto activée
                                        </div>
                                    </label>
                                </div>

                                <div id="uploadPreviewSection" class="d-none mt-3">
                                    <div class="text-center mb-3">
                                        <img id="uploadPreview" alt="Aperçu">
                                        <div id="compressionInfo" class="compression-info"></div>
                                    </div>
                                    <button type="button" onclick="uploadPhoto()"
                                        class="btn btn-primary photo-action-btn w-100">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Enregistrer cette Photo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Alert erreur -->
                    <div id="errorAlert" class="alert alert-danger d-none mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="errorMessage"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay d-none">
        <div class="spinner-border text-light mb-3"></div>
        <h4 id="loadingText" class="mb-2">Traitement...</h4>
        <p id="loadingSubtext" class="text-muted">Veuillez patienter</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ========== VARIABLES GLOBALES ==========
        let currentStudentId = null;
        let photoModal = null;
        let stream = null;
        let currentFacingMode = 'environment'; // CAMÉRA ARRIÈRE PAR DÉFAUT
        let availableCameras = [];

        // ========== MODAL FUNCTIONS ==========
        function openPhotoModal(id, nom, prenoms, photoUrl) {
            currentStudentId = id;
            document.getElementById('modalStudentName').textContent = `${nom} ${prenoms}`;
            document.getElementById('studentId').value = id;

            const preview = document.getElementById('currentPhotoPreview');
            const placeholder = document.getElementById('noPhotoPlaceholder');

            if (photoUrl) {
                preview.src = photoUrl;
                preview.classList.remove('d-none');
                placeholder.classList.add('d-none');
            } else {
                preview.classList.add('d-none');
                placeholder.classList.remove('d-none');
            }

            document.getElementById('errorAlert').classList.add('d-none');

            if (!photoModal) {
                photoModal = new bootstrap.Modal(document.getElementById('photoModal'));
            }
            photoModal.show();

            detectCameras();
        }

        // ========== TOAST NOTIFICATIONS ==========
        function showToast(message, type = 'success') {
            const iconMap = {
                success: 'check-circle',
                error: 'exclamation-circle',
                info: 'info-circle'
            };

            const colorMap = {
                success: '#16a34a',
                error: '#dc2626',
                info: '#2563eb'
            };

            const toast = document.createElement('div');
            toast.className = 'custom-toast';
            toast.innerHTML = `
            <i class="fas fa-${iconMap[type]}" style="color: ${colorMap[type]}; font-size: 1.5rem;"></i>
            <span style="flex: 1; font-weight: 500;">${message}</span>
        `;

            const container = document.querySelector('.toast-container');
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideUp 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ========== LOADING OVERLAY ==========
        function showLoading(text = 'Traitement...', subtext = 'Veuillez patienter') {
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingSubtext').textContent = subtext;
            document.getElementById('loadingOverlay').classList.remove('d-none');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('d-none');
        }

        // ========== ERROR HANDLING ==========
        function showError(message) {
            const errorAlert = document.getElementById('errorAlert');
            document.getElementById('errorMessage').textContent = message;
            errorAlert.classList.remove('d-none');
            setTimeout(() => errorAlert.classList.add('d-none'), 5000);
        }

        function hideError() {
            document.getElementById('errorAlert').classList.add('d-none');
        }


        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        // ========== UPLOAD PHOTO ==========
        async function previewUploadedPhoto(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 10 * 1024 * 1024) {
                showError('Fichier trop volumineux (max 10MB)');
                event.target.value = '';
                return;
            }

            try {
                showLoading('Compression...', 'Optimisation de la photo');

                const { blob, compressionRatio, originalSize, compressedSize } =
                    await compressImage(file, 1024, 0.85);

                hideLoading();

                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('uploadPreview').src = e.target.result;
                    document.getElementById('uploadPreviewSection').classList.remove('d-none');

                    document.getElementById('compressionInfo').innerHTML = `
                    <i class="fas fa-check-circle text-success me-1"></i>
                    ${formatFileSize(originalSize)} → ${formatFileSize(compressedSize)} 
                    (${compressionRatio}% réduit)
                `;

                    hideError();
                };
                reader.readAsDataURL(blob);

                event.target.compressedBlob = blob;

            } catch (error) {
                hideLoading();
                showError('Erreur de compression');
                console.error(error);
            }
        }

        async function uploadPhoto() {
            const photoInput = document.getElementById('photoInput');

            if (!photoInput.files || !photoInput.files[0]) {
                showError('Veuillez sélectionner une photo');
                return;
            }

            hideError();
            showLoading('Envoi...', 'Upload vers le serveur');

            try {
                const formData = new FormData();

                if (photoInput.compressedBlob) {
                    formData.append('photo', photoInput.compressedBlob, 'photo.jpg');
                } else {
                    const { blob } = await compressImage(photoInput.files[0]);
                    formData.append('photo', blob, 'photo.jpg');
                }

                const response = await fetch(`/etudiants/${currentStudentId}/upload-photo`, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                hideLoading();

                if (response.ok && data.success) {
                    updateStudentPhoto(currentStudentId, data.photo_url);
                    showToast('✅ Photo enregistrée !', 'success');

                    setTimeout(() => {
                        photoModal.hide();
                        location.reload();
                    }, 1200);
                } else {
                    showError(data.message || 'Erreur lors de l\'upload');
                }
            } catch (error) {
                console.error('Upload error:', error);
                hideLoading();
                showError('Erreur: ' + error.message);
            }
        }

        function updateStudentPhoto(studentId, photoUrl) {
            const photoElement = document.getElementById(`student-photo-${studentId}`);
            if (photoElement) {
                const newUrl = photoUrl + '?t=' + Date.now();
                if (photoElement.tagName === 'IMG') {
                    photoElement.src = newUrl;
                } else {
                    const img = document.createElement('img');
                    img.src = newUrl;
                    img.alt = 'Photo';
                    img.className = 'photo-preview border border-2 border-primary';
                    img.id = `student-photo-${studentId}`;
                    photoElement.parentNode.replaceChild(img, photoElement);
                }
            }

            const preview = document.getElementById('currentPhotoPreview');
            preview.src = photoUrl + '?t=' + Date.now();
            preview.classList.remove('d-none');
            document.getElementById('noPhotoPlaceholder').classList.add('d-none');
        }

        // ========== CAMÉRA FUNCTIONS ==========
        async function detectCameras() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                availableCameras = devices.filter(d => d.kind === 'videoinput');

                if (availableCameras.length > 1) {
                    document.getElementById('switchCameraBtn').classList.remove('d-none');
                }
            } catch (err) {
                console.log('Impossible de détecter les caméras:', err);
            }
        }

        function switchCamera() {
            currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            startCamera();
            // showToast(currentFacingMode === 'environment' ? '📷 Caméra arrière' : '🤳 Caméra frontale', 'info');
        }

// ========== CAMÉRA FUNCTIONS AVEC RATIO 1:1 ==========

async function startCamera() {
    if (!navigator.mediaDevices?.getUserMedia) {
        showError('Caméra non supportée par ce navigateur');
        return;
    }

    try {
        const constraints = {
            video: {
                facingMode: { ideal: currentFacingMode },
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };

        stream = await navigator.mediaDevices.getUserMedia(constraints);
        const videoElement = document.getElementById('videoElement');
        videoElement.srcObject = stream;
        await videoElement.play();

        document.getElementById('startCameraBtn').classList.add('d-none');
        document.getElementById('captureBtn').classList.remove('d-none');

        if (availableCameras.length > 1) {
            document.getElementById('switchCameraBtn').classList.remove('d-none');
        }

        hideError();
        // showToast('Caméra prête !', 'success');

    } catch (err) {
        console.error('Camera error:', err);
        let errorMsg = 'Erreur d\'accès à la caméra';

        if (err.name === 'NotAllowedError') {
            errorMsg = 'Autorisation refusée. Vérifiez les paramètres.';
        } else if (err.name === 'NotFoundError') {
            errorMsg = 'Aucune caméra trouvée.';
        } else if (err.name === 'NotReadableError') {
            errorMsg = 'Caméra déjà utilisée.';
        }

        showError(errorMsg);
    }
}

function capturePhoto() {
    const video = document.getElementById('videoElement');
    const canvas = document.getElementById('canvasElement');
    const context = canvas.getContext('2d');

    // ✅ CALCUL POUR RATIO 1:1 (CARRÉ)
    const videoWidth = video.videoWidth;
    const videoHeight = video.videoHeight;
    
    // Déterminer la taille du carré (le côté le plus petit)
    const size = Math.min(videoWidth, videoHeight);
    
    // Calculer les offsets pour centrer le carré
    const offsetX = (videoWidth - size) / 2;
    const offsetY = (videoHeight - size) / 2;

    // ✅ Définir le canvas en format carré
    canvas.width = size;
    canvas.height = size;

    context.imageSmoothingEnabled = true;
    context.imageSmoothingQuality = 'high';
    
    // ✅ Dessiner la partie centrale carrée de la vidéo
    context.drawImage(
        video,
        offsetX, offsetY,  // Position source (centrage)
        size, size,         // Taille source (carré)
        0, 0,              // Position destination
        size, size         // Taille destination (carré)
    );

    const capturedPhoto = document.getElementById('capturedPhoto');
    capturedPhoto.src = canvas.toDataURL('image/jpeg', 0.9);

    video.classList.add('d-none');
    capturedPhoto.classList.remove('d-none');

    document.getElementById('captureBtn').classList.add('d-none');
    document.getElementById('captureActions').classList.remove('d-none');
    document.getElementById('switchCameraBtn').classList.add('d-none');

    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }

    // showToast('📸 Photo capturée en format carré !', 'success');
}

function retakePhoto() {
    document.getElementById('videoElement').classList.remove('d-none');
    document.getElementById('capturedPhoto').classList.add('d-none');
    document.getElementById('captureActions').classList.add('d-none');
    startCamera();
}

async function saveCapturedPhoto() {
    const canvas = document.getElementById('canvasElement');

    hideError();
    showLoading('Enregistrement...', 'Traitement de la photo');

    try {
        const blob = await new Promise(resolve => {
            canvas.toBlob(resolve, 'image/jpeg', 0.9);
        });

        if (!blob) throw new Error('Erreur création image');

        const formData = new FormData();
        formData.append('photo', blob, 'capture.jpg');

        const response = await fetch(`/etudiants/${currentStudentId}/upload-photo`, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();
        hideLoading();

        if (response.ok && data.success) {
            updateStudentPhoto(currentStudentId, data.photo_url);
            showToast('✅ Photo enregistrée !', 'success');

            setTimeout(() => {
                photoModal.hide();
                location.reload();
            }, 1200);
        } else {
            showError(data.message || 'Erreur enregistrement');
        }
    } catch (error) {
        console.error('Save error:', error);
        hideLoading();
        showError('Erreur: ' + error.message);
    }
}

// ========== COMPRESSION D'IMAGE AVEC RATIO 1:1 ==========
async function compressImage(file, maxWidth = 1024, quality = 0.85) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (e) => {
            const img = new Image();
            img.src = e.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                
                // ✅ FORCER RATIO 1:1 (CARRÉ)
                const size = Math.min(img.width, img.height);
                const offsetX = (img.width - size) / 2;
                const offsetY = (img.height - size) / 2;
                
                // Adapter la taille finale
                let finalSize = size;
                if (finalSize > maxWidth) {
                    finalSize = maxWidth;
                }
                
                canvas.width = finalSize;
                canvas.height = finalSize;
                
                const ctx = canvas.getContext('2d');
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';
                
                // ✅ Dessiner la partie centrale carrée
                ctx.drawImage(
                    img,
                    offsetX, offsetY,     // Position source (centrage)
                    size, size,           // Taille source (carré)
                    0, 0,                 // Position destination
                    finalSize, finalSize  // Taille destination (carré redimensionné)
                );

                canvas.toBlob((blob) => {
                    const compressionRatio = ((1 - blob.size / file.size) * 100).toFixed(0);
                    resolve({
                        blob,
                        compressionRatio,
                        originalSize: file.size,
                        compressedSize: blob.size
                    });
                }, 'image/jpeg', quality);
            };
            img.onerror = reject;
        };
        reader.onerror = reject;
    });
}
        // ========== CLEANUP ==========
        document.getElementById('photoModal').addEventListener('hidden.bs.modal', function () {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }

            // Reset tout
            document.getElementById('videoElement').classList.remove('d-none');
            document.getElementById('capturedPhoto').classList.add('d-none');
            document.getElementById('startCameraBtn').classList.remove('d-none');
            document.getElementById('captureBtn').classList.add('d-none');
            document.getElementById('captureActions').classList.add('d-none');
            document.getElementById('switchCameraBtn').classList.add('d-none');
            document.getElementById('photoInput').value = '';
            document.getElementById('uploadPreviewSection').classList.add('d-none');
        });

        // ========== DRAG & DROP ==========
        const uploadZone = document.getElementById('uploadZone');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.remove('drag-over');
            });
        });

        uploadZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('photoInput').files = files;
                previewUploadedPhoto({ target: { files: files } });
            }
        });
    </script>
</body>

</html>