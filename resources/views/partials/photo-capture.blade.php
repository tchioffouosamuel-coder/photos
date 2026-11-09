{{--
  | Module de prise de vue ACID — partial partage.
  |
  | Fournit la modale (camera + import galerie), la superposition de chargement
  | et toute la logique JS. A inclure une seule fois par page :
  |
  |     @include('partials.photo-capture')
  |
  | Ouverture depuis la page :
  |     openPhotoModal(id, nom, prenoms, photoUrl = '', classeNom = '')
  |
  | Endpoint utilise : POST /etudiants/{id}/upload-photo (inchange).
  | La photo est recadree au format carre 1:1 et compressee avant envoi.
--}}

<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="modalStudentName" aria-hidden="true">
    {{--
      | Exception assumee au comportement en feuille du bas : un viseur camera
      | a besoin de toute la hauteur. modal-dialog--plain neutralise donc les
      | regles de feuille d'acid.css, et modal-fullscreen-md-down passe la
      | modale en plein ecran sur exactement la meme plage (sous 768px).
    --}}
    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-md-down modal-dialog--plain">
        <div class="modal-content">

            <div class="modal-header">
                <span class="acid-modal-icon" aria-hidden="true"><i class="fas fa-camera-retro"></i></span>
                <div style="min-width:0;">
                    <h2 class="modal-title" id="modalStudentName">Photo de l'eleve</h2>
                    <p id="modalClassName"
                       style="margin:.125rem 0 0;font-size:var(--acid-text-sm);color:var(--acid-text-muted);">
                        Camera en direct ou import depuis la galerie
                    </p>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
            </div>

            <div class="modal-body">

                {{-- Photo actuelle + specifications --}}
                <div class="acid-row" style="gap:var(--acid-space-4);margin-block-end:var(--acid-space-5);">
                    <div class="acid-card-preview" style="width:104px;height:104px;max-width:104px;
                                aspect-ratio:1/1;border-radius:var(--acid-radius);flex:0 0 auto;">
                        <img id="currentPhotoPreview" src="" alt="Photo actuelle" class="d-none"
                             style="width:100%;height:100%;object-fit:cover;">
                        <div id="noPhotoPlaceholder" class="acid-photo__empty" style="position:static;height:100%;">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            <span>Pas de photo</span>
                        </div>
                    </div>

                    <div class="acid-spec" style="flex:1 1 auto;">
                        <div class="acid-spec__item">
                            <span>Format</span>
                            <strong>Carre 1:1</strong>
                        </div>
                        <div class="acid-spec__item">
                            <span>Compression</span>
                            <strong>Auto</strong>
                        </div>
                        <div class="acid-spec__item">
                            <span>Poids max</span>
                            <strong>10 Mo</strong>
                        </div>
                    </div>
                </div>

                {{-- Onglets --}}
                <ul class="nav nav-pills nav-fill" style="margin-block-end:var(--acid-space-4);" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="capture-tab" data-bs-toggle="tab"
                                data-bs-target="#captureSection" type="button" role="tab">
                            <i class="fas fa-camera" aria-hidden="true"></i> Camera
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="upload-tab" data-bs-toggle="tab"
                                data-bs-target="#uploadSection" type="button" role="tab">
                            <i class="fas fa-images" aria-hidden="true"></i> Galerie
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    {{-- Camera --}}
                    <div class="tab-pane fade show active" id="captureSection" role="tabpanel">
                        <div class="acid-capture__stage" style="aspect-ratio:1/1;max-width:420px;margin-inline:auto;">
                            <video id="videoElement" autoplay playsinline muted></video>
                            <canvas id="canvasElement" class="d-none"></canvas>
                            <img id="capturedPhoto" class="d-none" alt="Photo capturee">

                            <div class="acid-capture__guide" id="captureGuide"
                                 data-label="Cadrez le visage"
                                 style="aspect-ratio:1/1;inset-block:10%;"></div>

                            <button type="button" id="switchCameraBtn" class="acid-icon-btn d-none"
                                    onclick="switchCamera()" title="Changer de camera"
                                    aria-label="Changer de camera"
                                    style="position:absolute;top:.75rem;right:.75rem;z-index:2;
                                           background:rgba(255,255,255,.92);">
                                <i class="fas fa-camera-rotate" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="acid-capture__toolbar" style="justify-content:center;">
                            <span class="acid-capture__status" id="cameraStatus">
                                <span class="acid-badge__dot" aria-hidden="true"></span>
                                Camera a l'arret
                            </span>
                        </div>

                        <div class="acid-stack" style="gap:var(--acid-space-2);margin-block-start:var(--acid-space-3);">
                            <button type="button" id="startCameraBtn" onclick="startCamera()"
                                    class="acid-btn acid-btn--secondary acid-btn--lg acid-btn--block">
                                <i class="fas fa-video" aria-hidden="true"></i>
                                Demarrer la camera
                            </button>

                            <button type="button" id="captureBtn" onclick="capturePhoto()"
                                    class="acid-btn acid-btn--primary acid-btn--lg acid-btn--block d-none">
                                <i class="fas fa-camera" aria-hidden="true"></i>
                                Prendre la photo
                            </button>

                            <div id="captureActions" class="d-none">
                                <div class="acid-row" style="gap:var(--acid-space-2);">
                                    <button type="button" onclick="retakePhoto()"
                                            class="acid-btn acid-btn--neutral acid-btn--lg" style="flex:1 1 0;">
                                        <i class="fas fa-rotate-left" aria-hidden="true"></i>
                                        Reprendre
                                    </button>
                                    <button type="button" onclick="saveCapturedPhoto()"
                                            class="acid-btn acid-btn--secondary acid-btn--lg" style="flex:1 1 0;">
                                        <i class="fas fa-check" aria-hidden="true"></i>
                                        Valider
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Galerie --}}
                    <div class="tab-pane fade" id="uploadSection" role="tabpanel">
                        <form id="uploadPhotoForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="studentId" name="etudiant_id">

                            <div class="acid-dropzone" id="uploadZone">
                                <input type="file" id="photoInput" name="photo" accept="image/*"
                                       capture="environment" class="d-none"
                                       onchange="previewUploadedPhoto(event)">
                                <label for="photoInput" style="cursor:pointer;display:block;margin:0;">
                                    <span class="acid-dropzone__icon" aria-hidden="true">
                                        <i class="fas fa-cloud-arrow-up"></i>
                                    </span>
                                    <span class="acid-dropzone__title" style="display:block;margin-block-start:var(--acid-space-3);">
                                        Choisir une photo
                                    </span>
                                    <span class="acid-dropzone__hint" style="display:block;">
                                        JPG ou PNG &middot; 10 Mo maximum &middot; glisser-deposer accepte
                                    </span>
                                    <span class="acid-badge acid-badge--teal" style="margin-block-start:var(--acid-space-3);">
                                        <i class="fas fa-compress" aria-hidden="true"></i>
                                        Compression et recadrage automatiques
                                    </span>
                                </label>
                            </div>

                            <div id="uploadPreviewSection" class="d-none" style="margin-block-start:var(--acid-space-4);">
                                <div style="text-align:center;margin-block-end:var(--acid-space-4);">
                                    <img id="uploadPreview" alt="Apercu"
                                         style="max-height:260px;max-width:100%;border-radius:var(--acid-radius);
                                                border:1px solid var(--acid-border);">
                                    <div id="compressionInfo" class="acid-hint"></div>
                                </div>
                                <button type="button" onclick="uploadPhoto()"
                                        class="acid-btn acid-btn--primary acid-btn--lg acid-btn--block">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                                    Enregistrer cette photo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="errorAlert" class="acid-alert acid-alert--danger d-none"
                     style="margin-block-start:var(--acid-space-4);" role="alert">
                    <i class="fas fa-triangle-exclamation acid-alert__icon" aria-hidden="true"></i>
                    <div class="acid-alert__body">
                        <span id="errorMessage"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Superposition de chargement --}}
<div id="loadingOverlay" class="d-none"
     style="position:fixed;inset:0;z-index:1100;display:flex;flex-direction:column;
            align-items:center;justify-content:center;gap:var(--acid-space-3);
            background:rgba(4,22,46,.78);color:#fff;">
    <div class="acid-spinner" style="border-color:rgba(255,255,255,.25);
                                     border-top-color:var(--acid-teal-500);width:2.75rem;height:2.75rem;"></div>
    <h2 id="loadingText" style="color:#fff;margin:0;font-size:var(--acid-text-lg);">Traitement...</h2>
    <p id="loadingSubtext" style="margin:0;color:rgba(255,255,255,.7);font-size:var(--acid-text-sm);">
        Veuillez patienter
    </p>
</div>

@push('styles')
    <style>
        #videoElement,
        #capturedPhoto {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
@endpush

@push('scripts')
<script>
/* =========================================================================
 | Prise de vue et import de photos
 | Logique metier conservee : endpoint, recadrage 1:1, compression.
 * ======================================================================= */

let currentStudentId = null;
let currentClassName = null;
let photoModal = null;
let stream = null;
let currentFacingMode = 'environment';   /* camera arriere par defaut */
let availableCameras = [];

/* ---------------- Ouverture de la modale ---------------- */
function openPhotoModal(id, nom, prenoms, photoUrl = '', classeNom = '') {
    currentStudentId = id;
    currentClassName = classeNom || null;

    document.getElementById('modalStudentName').textContent = `${nom} ${prenoms}`.trim();

    const classLabel = document.getElementById('modalClassName');
    classLabel.textContent = classeNom
        ? `Classe : ${classeNom}`
        : 'Camera en direct ou import depuis la galerie';

    document.getElementById('studentId').value = id;

    const preview = document.getElementById('currentPhotoPreview');
    const placeholder = document.getElementById('noPhotoPlaceholder');

    if (photoUrl) {
        preview.src = photoUrl;
        preview.classList.remove('d-none');
        placeholder.classList.add('d-none');
    } else {
        preview.removeAttribute('src');
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
    }

    hideError();

    if (!photoModal) {
        photoModal = new bootstrap.Modal(document.getElementById('photoModal'));
    }
    photoModal.show();

    detectCameras();
}

/* ---------------- Notifications ---------------- */
function showToast(message, type = 'success') {
    window.AcidUI.notify(message, type === 'error' ? 'error' : type);
}

function setCameraStatus(live) {
    const el = document.getElementById('cameraStatus');
    if (!el) return;
    el.classList.toggle('is-live', live);
    el.lastChild.textContent = live ? ' Camera active' : ' Camera a l\'arret';
}

/* ---------------- Chargement ---------------- */
function showLoading(text = 'Traitement...', subtext = 'Veuillez patienter') {
    document.getElementById('loadingText').textContent = text;
    document.getElementById('loadingSubtext').textContent = subtext;
    document.getElementById('loadingOverlay').classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('d-none');
}

/* ---------------- Erreurs ---------------- */
function showError(message) {
    const errorAlert = document.getElementById('errorAlert');
    document.getElementById('errorMessage').textContent = message;
    errorAlert.classList.remove('d-none');
    setTimeout(() => errorAlert.classList.add('d-none'), 6000);
}

function hideError() {
    document.getElementById('errorAlert').classList.add('d-none');
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' o';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' Ko';
    return (bytes / (1024 * 1024)).toFixed(1) + ' Mo';
}

/* ---------------- Import depuis la galerie ---------------- */
async function previewUploadedPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) {
        showError('Fichier trop volumineux (10 Mo maximum).');
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

            document.getElementById('compressionInfo').innerHTML =
                '<i class="fas fa-circle-check" style="color:var(--acid-teal-600);"></i> ' +
                formatFileSize(originalSize) + ' &rarr; ' + formatFileSize(compressedSize) +
                ' (' + compressionRatio + '% de reduction)';

            hideError();
        };
        reader.readAsDataURL(blob);

        event.target.compressedBlob = blob;

    } catch (error) {
        hideLoading();
        showError('Erreur de compression de l\'image.');
        console.error(error);
    }
}

/* ---------------- Envoi au serveur ---------------- */
async function sendPhoto(blob, filename) {
    const formData = new FormData();
    formData.append('photo', blob, filename);

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
    return { ok: response.ok, data };
}

function onPhotoSaved(data) {
    updateStudentPhoto(currentStudentId, data.photo_url);
    showToast('Photo enregistree.', 'success');

    setTimeout(() => {
        photoModal.hide();
        location.reload();
    }, 1200);
}

async function uploadPhoto() {
    const photoInput = document.getElementById('photoInput');

    if (!photoInput.files || !photoInput.files[0]) {
        showError('Veuillez selectionner une photo.');
        return;
    }

    hideError();
    showLoading('Envoi...', 'Transfert vers le serveur');

    try {
        let blob = photoInput.compressedBlob;
        if (!blob) {
            blob = (await compressImage(photoInput.files[0])).blob;
        }

        const { ok, data } = await sendPhoto(blob, 'photo.jpg');
        hideLoading();

        if (ok && data.success) {
            onPhotoSaved(data);
        } else {
            showError(data.message || 'Erreur lors de l\'envoi.');
        }
    } catch (error) {
        console.error('Upload error:', error);
        hideLoading();
        showError('Erreur : ' + error.message);
    }
}

async function saveCapturedPhoto() {
    const canvas = document.getElementById('canvasElement');

    hideError();
    showLoading('Enregistrement...', 'Traitement de la photo');

    try {
        const blob = await new Promise((resolve) => {
            canvas.toBlob(resolve, 'image/jpeg', 0.9);
        });

        if (!blob) throw new Error('Erreur de creation de l\'image');

        const { ok, data } = await sendPhoto(blob, 'capture.jpg');
        hideLoading();

        if (ok && data.success) {
            onPhotoSaved(data);
        } else {
            showError(data.message || 'Erreur lors de l\'enregistrement.');
        }
    } catch (error) {
        console.error('Save error:', error);
        hideLoading();
        showError('Erreur : ' + error.message);
    }
}

/* Met a jour la vignette dans la page, si elle existe */
function updateStudentPhoto(studentId, photoUrl) {
    if (!photoUrl) return;
    const newUrl = photoUrl + '?t=' + Date.now();

    const photoElement = document.getElementById(`student-photo-${studentId}`);
    if (photoElement) {
        if (photoElement.tagName === 'IMG') {
            photoElement.src = newUrl;
        } else {
            const img = document.createElement('img');
            img.src = newUrl;
            img.alt = '';
            img.id = `student-photo-${studentId}`;
            photoElement.parentNode.replaceChild(img, photoElement);
        }
    }

    const preview = document.getElementById('currentPhotoPreview');
    preview.src = newUrl;
    preview.classList.remove('d-none');
    document.getElementById('noPhotoPlaceholder').classList.add('d-none');
}

/* ---------------- Camera ---------------- */
async function detectCameras() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        availableCameras = devices.filter((d) => d.kind === 'videoinput');

        if (availableCameras.length > 1) {
            document.getElementById('switchCameraBtn').classList.remove('d-none');
        }
    } catch (err) {
        console.log('Impossible de detecter les cameras :', err);
    }
}

function switchCamera() {
    currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';

    if (stream) {
        stream.getTracks().forEach((track) => track.stop());
    }

    startCamera();
}

async function startCamera() {
    if (!navigator.mediaDevices?.getUserMedia) {
        showError('Camera non supportee par ce navigateur.');
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
        setCameraStatus(true);

        if (availableCameras.length > 1) {
            document.getElementById('switchCameraBtn').classList.remove('d-none');
        }

        hideError();

    } catch (err) {
        console.error('Camera error:', err);
        let errorMsg = 'Erreur d\'acces a la camera.';

        if (err.name === 'NotAllowedError') {
            errorMsg = 'Autorisation refusee. Verifiez les parametres du navigateur.';
        } else if (err.name === 'NotFoundError') {
            errorMsg = 'Aucune camera detectee.';
        } else if (err.name === 'NotReadableError') {
            errorMsg = 'Camera deja utilisee par une autre application.';
        }

        setCameraStatus(false);
        showError(errorMsg);
    }
}

function capturePhoto() {
    const video = document.getElementById('videoElement');
    const canvas = document.getElementById('canvasElement');
    const context = canvas.getContext('2d');

    /* Recadrage centre au format carre 1:1 */
    const videoWidth = video.videoWidth;
    const videoHeight = video.videoHeight;

    const size = Math.min(videoWidth, videoHeight);
    const offsetX = (videoWidth - size) / 2;
    const offsetY = (videoHeight - size) / 2;

    canvas.width = size;
    canvas.height = size;

    context.imageSmoothingEnabled = true;
    context.imageSmoothingQuality = 'high';

    context.drawImage(video, offsetX, offsetY, size, size, 0, 0, size, size);

    const capturedPhoto = document.getElementById('capturedPhoto');
    capturedPhoto.src = canvas.toDataURL('image/jpeg', 0.9);

    video.classList.add('d-none');
    capturedPhoto.classList.remove('d-none');

    document.getElementById('captureBtn').classList.add('d-none');
    document.getElementById('captureActions').classList.remove('d-none');
    document.getElementById('switchCameraBtn').classList.add('d-none');
    document.getElementById('captureGuide').classList.add('d-none');

    if (stream) {
        stream.getTracks().forEach((track) => track.stop());
    }
    setCameraStatus(false);
}

function retakePhoto() {
    document.getElementById('videoElement').classList.remove('d-none');
    document.getElementById('capturedPhoto').classList.add('d-none');
    document.getElementById('captureActions').classList.add('d-none');
    document.getElementById('captureGuide').classList.remove('d-none');
    startCamera();
}

/* ---------------- Compression + recadrage 1:1 ---------------- */
async function compressImage(file, maxWidth = 1024, quality = 0.85) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (e) => {
            const img = new Image();
            img.src = e.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');

                const size = Math.min(img.width, img.height);
                const offsetX = (img.width - size) / 2;
                const offsetY = (img.height - size) / 2;

                let finalSize = size;
                if (finalSize > maxWidth) {
                    finalSize = maxWidth;
                }

                canvas.width = finalSize;
                canvas.height = finalSize;

                const ctx = canvas.getContext('2d');
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';

                ctx.drawImage(img, offsetX, offsetY, size, size, 0, 0, finalSize, finalSize);

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

/* ---------------- Reinitialisation a la fermeture ---------------- */
document.getElementById('photoModal').addEventListener('hidden.bs.modal', function () {
    if (stream) {
        stream.getTracks().forEach((track) => track.stop());
        stream = null;
    }

    document.getElementById('videoElement').classList.remove('d-none');
    document.getElementById('capturedPhoto').classList.add('d-none');
    document.getElementById('startCameraBtn').classList.remove('d-none');
    document.getElementById('captureBtn').classList.add('d-none');
    document.getElementById('captureActions').classList.add('d-none');
    document.getElementById('switchCameraBtn').classList.add('d-none');
    document.getElementById('captureGuide').classList.remove('d-none');
    document.getElementById('photoInput').value = '';
    document.getElementById('uploadPreviewSection').classList.add('d-none');
    setCameraStatus(false);
});

/* ---------------- Glisser-deposer ---------------- */
(function () {
    const uploadZone = document.getElementById('uploadZone');
    if (!uploadZone) return;

    const prevent = (e) => { e.preventDefault(); e.stopPropagation(); };

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((name) => {
        uploadZone.addEventListener(name, prevent, false);
    });

    ['dragenter', 'dragover'].forEach((name) => {
        uploadZone.addEventListener(name, () => uploadZone.classList.add('is-dragover'));
    });

    ['dragleave', 'drop'].forEach((name) => {
        uploadZone.addEventListener(name, () => uploadZone.classList.remove('is-dragover'));
    });

    uploadZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('photoInput').files = files;
            previewUploadedPhoto({ target: { files: files } });
        }
    });
})();
</script>
@endpush
