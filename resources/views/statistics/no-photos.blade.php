<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Toast Container -->
    <div class="toast-container"></div>

    <div class="container py-4">
        <!-- Header -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-user-times text-danger me-2"></i>
                            Élèves Sans Photo
                        </h1>
                        <p class="text-muted mb-0">Liste complète des élèves n'ayant pas encore de photo</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('statistics.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Retour
                        </a>
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print me-2"></i>
                            Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow border-start border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small mb-1">Total Sans Photo</p>
                                <h3 class="text-danger mb-0">{{ $totalWithoutPhotos }}</h3>
                            </div>
                            <i class="fas fa-user-times fa-3x text-danger opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small mb-1">Classes Concernées</p>
                                <h3 class="text-primary mb-0">{{ $classesAffected }}</h3>
                            </div>
                            <i class="fas fa-school fa-3x text-primary opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow border-start border-4" style="border-color: #7c3aed !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small mb-1">Garçons</p>
                                <h3 class="mb-0" style="color: #7c3aed;">{{ $boysWithoutPhotos }}</h3>
                            </div>
                            <i class="fas fa-mars fa-3x opacity-75" style="color: #7c3aed;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow border-start border-4" style="border-color: #ec4899 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small mb-1">Filles</p>
                                <h3 class="mb-0" style="color: #ec4899;">{{ $girlsWithoutPhotos }}</h3>
                            </div>
                            <i class="fas fa-venus fa-3x opacity-75" style="color: #ec4899;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="position-relative">
                            <input type="text" 
                                   id="searchInput" 
                                   class="form-control form-control-lg ps-5" 
                                   placeholder="Rechercher par nom, classe, matricule...">
                            <i class="fas fa-search position-absolute text-muted" 
                               style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <select id="filterClasse" class="form-select form-select-lg">
                            <option value="">Toutes les classes</option>
                            @foreach($classes as $classe)
                                <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <select id="filterSexe" class="form-select form-select-lg">
                            <option value="">Tous les sexes</option>
                            <option value="M">Garçons</option>
                            <option value="F">Filles</option>
                        </select>
                    </div>
                </div>
                <div id="searchInfo" class="mt-3 text-muted small d-none">
                    <i class="fas fa-info-circle me-1"></i>
                    <span id="resultCount"></span>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="card shadow-lg">
            <div class="card-header text-white py-4" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                <h5 class="mb-0 d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-list me-2"></i>
                        Liste Détaillée
                    </span>
                    <span class="fs-6" id="countDisplay">{{ $totalWithoutPhotos }} élèves</span>
                </h5>
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="d-none text-center py-5">
                <i class="fas fa-search display-1 text-muted mb-4"></i>
                <h5 class="text-muted mb-2">Aucun élève trouvé</h5>
                <p class="text-muted">Essayez avec d'autres critères de recherche</p>
            </div>

            <div class="table-responsive" id="tableContainer">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N°</th>
                            <th>Classe</th>
                            <th>Matricule</th>
                            <th>Nom & Prénoms</th>
                            <th>Date Naissance</th>
                            <th>Sexe</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        @forelse($students as $index => $etudiant)
                        <tr class="student-row" 
                            data-nom="{{ strtolower($etudiant->nom . ' ' . $etudiant->prenoms) }}"
                            data-classe="{{ $etudiant->classe->id }}"
                            data-classe-nom="{{ strtolower($etudiant->classe->nom) }}"
                            data-matricule="{{ strtolower($etudiant->matricule_national) }}"
                            data-sexe="{{ $etudiant->sexe }}">
                            <td>
                                <strong>{{ $index + 1 }}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-chalkboard-teacher text-primary me-2"></i>
                                    <strong class="text-primary">{{ $etudiant->classe->nom }}</strong>
                                </div>
                            </td>
                            <td>
                                <code class="text-muted">{{ $etudiant->matricule_national ?: 'N/A' }}</code>
                            </td>
                            <td>
                                <div class="fw-bold">{{ strtoupper($etudiant->nom) }}</div>
                                <small class="text-muted">{{ $etudiant->prenoms }}</small>
                            </td>
                            <td>
                                <small>{{ $etudiant->date_naissance ? $etudiant->date_naissance->format('d/m/Y') : 'N/A' }}</small>
                            </td>
                            <td>
                                @if($etudiant->sexe === 'M')
                                    <span class="badge rounded-pill" style="background-color: #e9d5ff; color: #7c3aed;">
                                        <i class="fas fa-mars me-1"></i> M
                                    </span>
                                @else
                                    <span class="badge rounded-pill" style="background-color: #fce7f3; color: #ec4899;">
                                        <i class="fas fa-venus me-1"></i> F
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" 
                                        onclick="openPhotoModal({{ $etudiant->id }}, '{{ $etudiant->nom }}', '{{ $etudiant->prenoms }}', '{{ $etudiant->classe->nom }}')" 
                                        class="btn btn-primary btn-sm">
                                    <i class="fas fa-camera me-1"></i>
                                    Ajouter Photo
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-check-circle display-1 text-success mb-3"></i>
                                    <h5 class="text-success fw-bold">Excellent !</h5>
                                    <p class="text-muted mt-2">Tous les élèves ont une photo</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Help Section -->
        <div class="card text-white shadow-lg mt-4" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="fas fa-lightbulb me-2"></i>
                    Comment ajouter les photos ?
                </h5>
                <div class="row g-4">
                    <div class="col-12 col-md-4">
                        <div class="bg-white bg-opacity-25 rounded p-4">
                            <i class="fas fa-mouse-pointer fa-3x mb-3"></i>
                            <h6 class="fw-bold mb-2">1. Cliquez sur "Ajouter Photo"</h6>
                            <p class="small mb-0 opacity-75">Dans la colonne Action de chaque élève</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="bg-white bg-opacity-25 rounded p-4">
                            <i class="fas fa-upload fa-3x mb-3"></i>
                            <h6 class="fw-bold mb-2">2. Importez ou capturez</h6>
                            <p class="small mb-0 opacity-75">Depuis votre ordinateur ou webcam</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="bg-white bg-opacity-25 rounded p-4">
                            <i class="fas fa-save fa-3x mb-3"></i>
                            <h6 class="fw-bold mb-2">3. Enregistrez</h6>
                            <p class="small mb-0 opacity-75">La photo sera automatiquement associée</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-mobile">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <div>
                        <h5 class="modal-title" id="modalStudentName">Photo de l'élève</h5>
                        <small class="opacity-75" id="modalClassName">Classe</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
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
                        <!-- CAMÉRA -->
                        <div class="tab-pane fade show active" id="captureSection">
                            <div class="camera-container mb-3">
                                <video id="videoElement" autoplay playsinline muted></video>
                                <canvas id="canvasElement" class="d-none"></canvas>
                                <img id="capturedPhoto" class="d-none" alt="Photo capturée">

                                <button type="button" id="switchCameraBtn" class="switch-camera-btn d-none"
                                    onclick="switchCamera()" title="Changer de caméra">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>

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
        <div class="spinner-border text-light mb-3" style="width: 4rem; height: 4rem;"></div>
        <h4 id="loadingText" class="mb-2">Traitement...</h4>
        <p id="loadingSubtext" class="text-muted">Veuillez patienter</p>
    </div>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        }

        .bg-gradient-primary {
            background: var(--primary-gradient);
        }

        .bg-opacity-25 {
            background-color: rgba(255, 255, 255, 0.25) !important;
        }

        .opacity-75 {
            opacity: 0.75;
        }

        /* Upload Zone */
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

        /* Photo Action Buttons */
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

        /* Camera Container */
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

        /* Switch Camera Button */
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

        /* Loading Overlay */
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

        /* Toast Container */
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

        .compression-info {
            font-size: 0.85rem;
            color: #64748b;
            padding: 0.5rem;
            background: #f1f5f9;
            border-radius: 0.5rem;
            margin-top: 0.75rem;
        }

        #uploadPreview {
            max-height: 320px;
            width: auto;
            max-width: 100%;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .nav-pills .nav-link {
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .nav-pills .nav-link:active {
            transform: scale(0.96);
        }

        @keyframes fade-in {
            from { 
                opacity: 0; 
                transform: translateY(-5px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

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

        @media print {
            body {
                background: white;
            }
            .no-print, 
            .btn, 
            .form-select, 
            .form-control,
            .card-header {
                display: none !important;
            }
        }
    </style>

    <script>
        // ========== VARIABLES GLOBALES ==========
        let currentStudentId = null;
        let currentClassName = null;
        let photoModal = null;
        let stream = null;
        let currentFacingMode = 'environment';
        let availableCameras = [];

        // ========== MODAL FUNCTIONS ==========
        function openPhotoModal(id, nom, prenoms, classe) {
            currentStudentId = id;
            currentClassName = classe;
            document.getElementById('modalStudentName').textContent = `${nom} ${prenoms}`;
            document.getElementById('modalClassName').textContent = `Classe: ${classe}`;
            document.getElementById('studentId').value = id;
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

        // ========== COMPRESSION D'IMAGE ==========
        async function compressImage(file, maxWidth = 1024, quality = 0.85) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (e) => {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;

                        if (width > maxWidth) {
                            height = (height * maxWidth) / width;
                            width = maxWidth;
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.imageSmoothingEnabled = true;
                        ctx.imageSmoothingQuality = 'high';
                        ctx.drawImage(img, 0, 0, width, height);

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

        // ========== SEARCH AND FILTERS ==========
        const searchInput = document.getElementById('searchInput');
        const filterClasse = document.getElementById('filterClasse');
        const filterSexe = document.getElementById('filterSexe');
        const studentRows = document.querySelectorAll('.student-row');
        const countDisplay = document.getElementById('countDisplay');
        const noResults = document.getElementById('noResults');
        const searchInfo = document.getElementById('searchInfo');
        const resultCount = document.getElementById('resultCount');
        const tableContainer = document.getElementById('tableContainer');

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const classeFilter = filterClasse.value;
            const sexeFilter = filterSexe.value;
            let visibleCount = 0;

            studentRows.forEach((row, index) => {
                const nom = row.dataset.nom;
                const classeId = row.dataset.classe;
                const classeNom = row.dataset.classeNom;
                const matricule = row.dataset.matricule;
                const sexe = row.dataset.sexe;

                const matchesSearch = nom.includes(searchTerm) || 
                                    classeNom.includes(searchTerm) || 
                                    matricule.includes(searchTerm);
                const matchesClasse = !classeFilter || classeId === classeFilter;
                const matchesSexe = !sexeFilter || sexe === sexeFilter;

                if (matchesSearch && matchesClasse && matchesSexe) {
                    row.style.display = '';
                    row.classList.add('animate-fade-in');
                    row.querySelector('td:first-child strong').textContent = ++visibleCount;
                } else {
                    row.style.display = 'none';
                }
            });

            countDisplay.textContent = `${visibleCount} élève${visibleCount > 1 ? 's' : ''}`;
            
            if (searchTerm !== '' || classeFilter || sexeFilter) {
                searchInfo.classList.remove('d-none');
                resultCount.textContent = `${visibleCount} résultat${visibleCount > 1 ? 's' : ''} trouvé${visibleCount > 1 ? 's' : ''}`;
            } else {
                searchInfo.classList.add('d-none');
            }

            if (visibleCount === 0) {
                noResults.classList.remove('d-none');
                tableContainer.classList.add('d-none');
            } else {
                noResults.classList.add('d-none');
                tableContainer.classList.remove('d-none');
            }
        }

        searchInput.addEventListener('input', applyFilters);
        filterClasse.addEventListener('change', applyFilters);
        filterSexe.addEventListener('change', applyFilters);

        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
        });
    </script>
</x-app-layout>