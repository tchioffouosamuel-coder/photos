<x-app-layout>
    <div class="container py-4">
        <!-- Header -->
        <div class="card shadow-lg mb-4 animate-fade-in" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body text-white p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-3 mb-lg-0">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-file-excel me-2"></i>
                            Importation des Listes Excel
                        </h1>
                        <p class="mb-0 opacity-75">Importez les listes d'élèves depuis vos fichiers Excel</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="{{ route('import.template') }}" class="btn btn-light btn-lg shadow">
                            <i class="fas fa-download me-2"></i>
                            Télécharger Modèle
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages de succès -->
        @if(session('success'))
        <div class="alert alert-success shadow-lg alert-dismissible fade show animate-fade-in" id="successAlert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-3"></i>
                <div>
                    <span class="fw-semibold">{{ session('success') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Messages d'erreur -->
        @if(session('error'))
        <div class="alert alert-danger shadow-lg alert-dismissible fade show animate-fade-in" id="errorAlert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                <div>
                    <span class="fw-semibold">{{ session('error') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Erreurs de validation -->
        @if($errors->any())
        <div class="alert alert-danger shadow-lg alert-dismissible fade show animate-fade-in">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fs-4 me-3 mt-1"></i>
                <div class="flex-grow-1">
                    <span class="fw-semibold d-block mb-2">Erreurs de validation :</span>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row g-4">
            <!-- Formulaire d'upload -->
            <div class="col-lg-6">
                <div class="card shadow-lg p-4 animate-fade-in">
                    <h2 class="h4 mb-4">
                        <i class="fas fa-upload text-success me-2"></i>
                        Importer un fichier
                    </h2>

                    <form id="uploadForm" action="{{ route('import.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Sélection de la classe -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-chalkboard me-2"></i>Classe
                            </label>
                            <select name="classe_id" class="form-select form-select-lg @error('classe_id') is-invalid @enderror" required>
                                <option value="">Sélectionnez une classe</option>
                                @foreach($classes as $classe)
                                    <option value="{{ $classe->id }}" {{ old('classe_id') == $classe->id ? 'selected' : '' }}>
                                        {{ $classe->nom }}
                                        @if($classe->eleves_count)
                                            ({{ $classe->eleves_count }} élèves)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('classe_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Zone de drop -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-file-excel me-2"></i>Fichier Excel
                            </label>
                            <div class="dropzone" id="dropzone">
                                <input type="file" name="excel_file" id="fileInput" accept=".xlsx,.xls" hidden required>
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <p class="fw-semibold text-secondary mb-2">
                                    Cliquez pour sélectionner ou glissez-déposez
                                </p>
                                <p class="small text-muted mb-0">
                                    Fichiers Excel acceptés (.xlsx, .xls) - Max 10MB
                                </p>
                                <div id="fileNameDisplay" class="d-none">
                                    <span class="file-name-display">
                                        <i class="fas fa-file-excel me-2"></i>
                                        <span id="fileName"></span>
                                    </span>
                                </div>
                            </div>
                            @error('excel_file')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Bouton d'upload -->
                        <button type="submit" class="btn btn-success btn-lg w-100 shadow" id="submitBtn">
                            <i class="fas fa-upload me-2"></i>
                            <span id="btnText">Importer maintenant</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Instructions -->
            <div class="col-lg-6">
                <!-- Format requis -->
                <div class="card shadow-lg p-4 mb-4 animate-fade-in">
                    <h2 class="h4 mb-4">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Format requis
                    </h2>

                    <div class="alert alert-info border-start border-4 border-info mb-3">
                        <h3 class="h6 fw-bold mb-3">Compatible avec les listes générées par SYGEST</h3>
                        <h3 class="h6 fw-bold mb-3">Colonnes attendues :</h3>
                        <div class="row g-2">
                            <div class="col-12">
                                <span class="column-badge me-2">A</span>
                                <strong>N°</strong> - Numéro de ligne
                            </div>
                            <div class="col-12">
                                <span class="column-badge me-2">B</span>
                                <strong>Matricule National</strong>
                            </div>
                            <div class="col-12">
                                <span class="column-badge me-2">C</span>
                                <strong>Matricule Interne</strong>
                            </div>
                            <div class="col-12">
                                <span class="column-badge me-2">D</span>
                                <strong>Noms et Prénoms</strong>
                            </div>
                            <div class="col-12">
                                <span class="column-badge me-2">E</span>
                                <strong>Date de Naissance</strong> (JJ/MM/AAAA)
                            </div>
                            <div class="col-12">
                                <span class="column-badge me-2">F</span>
                                <strong>Lieu de Naissance</strong>
                            </div>
                            <div class="col-12">
                                <span class="column-badge me-2">G</span>
                                <strong>Sexe</strong> (M ou F)
                            </div>
                            <div class="col-12">
                                <span class="column-badge me-2">H</span>
                                <strong>Statut</strong> (N = Normal, R = Redoublant)
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning border-start border-4 border-warning">
                        <h3 class="h6 fw-bold mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Points importants
                        </h3>
                        <ul class="small mb-0 ps-3">
                            <li>L'en-tête de la classe peut commencer en ligne 1</li>
                            <li>Les données doivent commencer après la ligne "N°"</li>
                            <li>Les colonnes doivent être dans l'ordre exact</li>
                            <li>Les dates au format JJ/MM/AAAA (ex: 15/08/2008)</li>
                            <li>Les doublons seront mis à jour automatiquement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Erreurs -->
    <div class="modal fade" id="errorsModal" tabindex="-1" aria-labelledby="errorsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                    <h5 class="modal-title" id="errorsModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Détails des erreurs
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorsContent">
                    <!-- Le contenu sera injecté dynamiquement -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dropzone {
            border: 3px dashed #d1d5db;
            border-radius: 0.75rem;
            padding: 3rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f9fafb;
        }

        .dropzone:hover,
        .dropzone.drag-over {
            border-color: #10b981;
            background: #ecfdf5;
        }

        .dropzone.file-selected {
            border-color: #10b981;
            background: #d1fae5;
        }

        .upload-icon {
            font-size: 4rem;
            color: #9ca3af;
            margin-bottom: 1rem;
        }

        .file-name-display {
            background: #10b981;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            display: inline-block;
            margin-top: 1rem;
            font-weight: 600;
        }

        .column-badge {
            background: #4f46e5;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-block;
            min-width: 2rem;
            text-align: center;
        }

        .error-list {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
        }

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

        .shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        .opacity-75 {
            opacity: 0.75;
        }

        .spinner-border-custom {
            width: 1.5rem;
            height: 1.5rem;
            border-width: 0.2rem;
        }

        @media (max-width: 768px) {
            .dropzone {
                padding: 2rem 1rem;
            }

            .upload-icon {
                font-size: 3rem;
            }
        }
    </style>

    <script>
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('fileInput');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const fileName = document.getElementById('fileName');
        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');

        // Click to upload
        dropzone.addEventListener('click', () => {
            fileInput.click();
        });

        // File selection
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        // Drag and drop
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('drag-over');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('drag-over');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('drag-over');
            handleFiles(e.dataTransfer.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                
                // Vérifier la taille du fichier (10MB max)
                if (file.size > 10 * 1024 * 1024) {
                    alert('Le fichier est trop volumineux. Taille maximum : 10MB');
                    return;
                }
                
                if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
                    fileName.textContent = file.name;
                    fileNameDisplay.classList.remove('d-none');
                    dropzone.classList.add('file-selected');
                    
                    // Créer un DataTransfer pour assigner le fichier à l'input
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                } else {
                    alert('Veuillez sélectionner un fichier Excel (.xlsx ou .xls)');
                }
            }
        }

        // Form submission avec indication de chargement
        uploadForm.addEventListener('submit', (e) => {
            // Vérifier qu'un fichier est sélectionné
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Veuillez sélectionner un fichier Excel');
                return;
            }

            submitBtn.disabled = true;
            btnText.innerHTML = '<span class="spinner-border spinner-border-custom me-2"></span>Importation en cours...';
        });

        // Fonction pour afficher les erreurs d'importation
        function showErrors(errors) {
            const errorsContent = document.getElementById('errorsContent');
            
            if (typeof errors === 'string') {
                try {
                    errors = JSON.parse(errors);
                } catch (e) {
                    errors = [errors];
                }
            }
            
            let html = '<div class="error-list">';
            
            if (Array.isArray(errors)) {
                html += '<ul class="mb-0">';
                errors.forEach(error => {
                    html += `<li>${error}</li>`;
                });
                html += '</ul>';
            } else if (typeof errors === 'object') {
                for (const [key, value] of Object.entries(errors)) {
                    html += `<div class="mb-3">`;
                    html += `<strong>Ligne ${key}:</strong>`;
                    html += `<ul class="mb-0 mt-1">`;
                    if (Array.isArray(value)) {
                        value.forEach(error => {
                            html += `<li>${error}</li>`;
                        });
                    } else {
                        html += `<li>${value}</li>`;
                    }
                    html += `</ul></div>`;
                }
            }
            
            html += '</div>';
            errorsContent.innerHTML = html;
            
            const errorsModal = new bootstrap.Modal(document.getElementById('errorsModal'));
            errorsModal.show();
        }

        // Auto-hide alerts après 5 secondes
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</x-app-layout>