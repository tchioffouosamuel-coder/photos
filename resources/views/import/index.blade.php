<x-app-layout>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Gestion scolaire</span>
            <h1 class="acid-page-header__title">Importer une liste d'eleves</h1>
            <p class="acid-page-header__lead">
                Chargez un fichier Excel pour creer ou mettre a jour l'effectif d'une classe.
                Les listes generees par SYGEST sont prises en charge telles quelles.
            </p>
        </div>
        <div class="acid-page-header__actions">
            <a href="{{ route('import.template') }}" class="acid-btn acid-btn--neutral">
                <i class="fas fa-download" aria-hidden="true"></i>
                Telecharger le modele
            </a>
        </div>
    </div>

    {{-- Etapes --}}
    <ol class="acid-steps" style="margin-block-end:var(--acid-space-6);">
        <li class="acid-steps__item is-active">Choisir la classe</li>
        <li class="acid-steps__item">Deposer le fichier Excel</li>
        <li class="acid-steps__item">Verifier le resultat</li>
    </ol>

    {{-- Erreurs de validation --}}
    @if ($errors->any())
        <div class="acid-alert acid-alert--danger" style="margin-block-end:var(--acid-space-5);" role="alert">
            <i class="fas fa-triangle-exclamation acid-alert__icon" aria-hidden="true"></i>
            <div class="acid-alert__body">
                <p class="acid-alert__title">Le fichier n'a pas pu etre importe</p>
                <ul style="margin:0;padding-inline-start:1.125rem;font-size:var(--acid-text-sm);">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="acid-grid" style="grid-template-columns:minmax(0,1fr) minmax(320px,1fr);align-items:start;">

        {{-- Formulaire --}}
        <section class="acid-card">
            <div class="acid-card__header">
                <span class="acid-stat__icon" aria-hidden="true"
                      style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                    <i class="fas fa-file-import"></i>
                </span>
                <div>
                    <h2 class="acid-card__title">Fichier a importer</h2>
                    <p class="acid-card__subtitle">Excel .xlsx ou .xls, 10 Mo maximum</p>
                </div>
            </div>

            <form id="uploadForm" action="{{ route('import.upload') }}" method="POST"
                  enctype="multipart/form-data" data-acid-no-busy>
                @csrf

                <div class="acid-card__body">

                    <div class="acid-field">
                        <label for="classe_id" class="acid-label acid-label--required">Classe de destination</label>
                        <select id="classe_id" name="classe_id" required
                                class="acid-select @error('classe_id') is-invalid @enderror">
                            <option value="">Selectionnez une classe</option>
                            @foreach ($classes as $classe)
                                <option value="{{ $classe->id }}" {{ old('classe_id') == $classe->id ? 'selected' : '' }}>
                                    {{ $classe->nom }}@if ($classe->eleves_count) &nbsp;({{ $classe->eleves_count }} eleves)@endif
                                </option>
                            @endforeach
                        </select>
                        <span class="acid-hint">
                            Les eleves deja presents sont mis a jour, pas dupliques.
                        </span>
                        @error('classe_id')
                            <div class="acid-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="acid-field" style="margin-block-end:0;">
                        <span class="acid-label acid-label--required">Fichier Excel</span>

                        <div class="acid-dropzone" id="dropzone" role="button" tabindex="0"
                             aria-label="Selectionner un fichier Excel">
                            <input type="file" name="excel_file" id="fileInput" accept=".xlsx,.xls" hidden required>

                            <span class="acid-dropzone__icon" aria-hidden="true">
                                <i class="fas fa-file-excel"></i>
                            </span>
                            <span class="acid-dropzone__title">Cliquez ou glissez-deposez votre fichier</span>
                            <p class="acid-dropzone__hint">Formats acceptes : .xlsx, .xls &middot; 10 Mo maximum</p>

                            <span id="fileNameDisplay" class="acid-badge acid-badge--solid-teal d-none"
                                  style="margin-block-start:var(--acid-space-2);">
                                <i class="fas fa-file-excel" aria-hidden="true"></i>
                                <span id="fileName"></span>
                            </span>
                        </div>

                        @error('excel_file')
                            <div class="acid-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="acid-card__footer">
                    <button type="submit" class="acid-btn acid-btn--primary acid-btn--lg acid-btn--block"
                            id="submitBtn">
                        <i class="fas fa-upload" aria-hidden="true"></i>
                        <span id="btnText">Importer maintenant</span>
                    </button>
                </div>
            </form>
        </section>

        {{-- Documentation du format --}}
        <aside class="acid-stack">

            <section class="acid-card">
                <div class="acid-card__header">
                    <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-table-columns"></i></span>
                    <div>
                        <h2 class="acid-card__title">Colonnes attendues</h2>
                        <p class="acid-card__subtitle">Dans cet ordre exact</p>
                    </div>
                </div>

                <div class="acid-table-wrap">
                    <table class="acid-table acid-table--compact">
                        <thead>
                            <tr>
                                <th scope="col" style="width:3rem;">Col.</th>
                                <th scope="col">Contenu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="acid-badge acid-badge--solid-navy">A</span></td>
                                <td><span class="acid-table__primary">N&deg;</span>
                                    <span class="acid-table__meta">Numero de ligne</span></td>
                            </tr>
                            <tr>
                                <td><span class="acid-badge acid-badge--solid-navy">B</span></td>
                                <td><span class="acid-table__primary">Matricule national</span></td>
                            </tr>
                            <tr>
                                <td><span class="acid-badge acid-badge--solid-navy">C</span></td>
                                <td><span class="acid-table__primary">Matricule interne</span></td>
                            </tr>
                            <tr>
                                <td><span class="acid-badge acid-badge--solid-navy">D</span></td>
                                <td><span class="acid-table__primary">Nom et prenoms</span>
                                    <span class="acid-table__meta">
                                        Nom de famille en MAJUSCULES, puis prenoms
                                    </span></td>
                            </tr>
                            <tr>
                                <td><span class="acid-badge acid-badge--solid-navy">E</span></td>
                                <td><span class="acid-table__primary">Date de naissance</span>
                                    <span class="acid-table__meta">Format JJ/MM/AAAA</span></td>
                            </tr>
                            <tr>
                                <td><span class="acid-badge acid-badge--solid-navy">F</span></td>
                                <td><span class="acid-table__primary">Lieu de naissance</span></td>
                            </tr>
                            <tr>
                                <td><span class="acid-badge acid-badge--solid-navy">G</span></td>
                                <td><span class="acid-table__primary">Sexe</span>
                                    <span class="acid-table__meta">M ou F</span></td>
                            </tr>
                            <tr>
                                <td><span class="acid-badge acid-badge--solid-navy">H</span></td>
                                <td><span class="acid-table__primary">Statut</span>
                                    <span class="acid-table__meta">N = Normal, R = Redoublant</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="acid-alert acid-alert--warning">
                <i class="fas fa-triangle-exclamation acid-alert__icon" aria-hidden="true"></i>
                <div class="acid-alert__body">
                    <p class="acid-alert__title">Points de vigilance</p>
                    <ul style="margin:0;padding-inline-start:1.125rem;font-size:var(--acid-text-sm);">
                        <li>L'en-tete de classe peut commencer en ligne 1.</li>
                        <li>Les donnees doivent suivre la ligne intitulee &laquo; N&deg; &raquo;.</li>
                        <li>L'ordre des colonnes doit etre respecte.</li>
                        <li>
                            Colonne D : &laquo; MBALLA Paul &raquo; donne le nom MBALLA et le
                            prenom Paul. Un nom compose en majuscules est reconnu.
                        </li>
                        <li>Les dates sont au format JJ/MM/AAAA (ex. 15/08/2008).</li>
                        <li>
                            Les doublons sont mis a jour : par matricule national, ou a defaut
                            par nom, prenoms et date de naissance.
                        </li>
                    </ul>
                </div>
            </div>

            <div class="acid-alert acid-alert--navy">
                <i class="fas fa-circle-info acid-alert__icon" aria-hidden="true"></i>
                <div class="acid-alert__body">
                    <p class="acid-alert__title">Besoin d'un point de depart ?</p>
                    <p style="margin:0 0 var(--acid-space-3);font-size:var(--acid-text-sm);">
                        Telechargez le modele deja structure et completez-le.
                    </p>
                    <a href="{{ route('import.template') }}" class="acid-btn acid-btn--outline acid-btn--sm">
                        <i class="fas fa-download" aria-hidden="true"></i>
                        Modele Excel
                    </a>
                </div>
            </div>
        </aside>
    </div>

    {{-- Modale de detail des erreurs d'import --}}
    <div class="modal fade" id="errorsModal" tabindex="-1" aria-labelledby="errorsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="acid-modal-icon acid-modal-icon--coral" aria-hidden="true">
                        <i class="fas fa-triangle-exclamation"></i>
                    </span>
                    <div>
                        <h2 class="modal-title" id="errorsModalLabel">Detail des erreurs</h2>
                        <p style="margin:.125rem 0 0;font-size:var(--acid-text-sm);color:var(--acid-text-muted);">
                            Lignes du fichier qui n'ont pas pu etre importees
                        </p>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"
                            aria-label="Fermer"></button>
                </div>
                <div class="modal-body" id="errorsContent"></div>
                <div class="modal-footer">
                    <button type="button" class="acid-btn acid-btn--neutral" data-bs-dismiss="modal">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const dropzone        = document.getElementById('dropzone');
                const fileInput       = document.getElementById('fileInput');
                const fileNameDisplay = document.getElementById('fileNameDisplay');
                const fileName        = document.getElementById('fileName');
                const uploadForm      = document.getElementById('uploadForm');
                const submitBtn       = document.getElementById('submitBtn');
                const btnText         = document.getElementById('btnText');

                if (!dropzone) return;

                dropzone.addEventListener('click', () => fileInput.click());
                dropzone.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        fileInput.click();
                    }
                });

                fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

                dropzone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropzone.classList.add('is-dragover');
                });
                dropzone.addEventListener('dragleave', () => dropzone.classList.remove('is-dragover'));
                dropzone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('is-dragover');
                    handleFiles(e.dataTransfer.files);
                });

                function handleFiles(files) {
                    if (!files.length) return;
                    const file = files[0];

                    if (file.size > 10 * 1024 * 1024) {
                        window.AcidUI.notify('Fichier trop volumineux : 10 Mo maximum.', 'error');
                        return;
                    }

                    if (!/\.(xlsx|xls)$/i.test(file.name)) {
                        window.AcidUI.notify('Format non pris en charge : utilisez un fichier .xlsx ou .xls.', 'error');
                        return;
                    }

                    fileName.textContent = file.name;
                    fileNameDisplay.classList.remove('d-none');
                    dropzone.style.borderColor = 'var(--acid-teal-500)';
                    dropzone.style.background = 'var(--acid-teal-50)';

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                }

                uploadForm.addEventListener('submit', (e) => {
                    if (!fileInput.files || fileInput.files.length === 0) {
                        e.preventDefault();
                        window.AcidUI.notify('Veuillez selectionner un fichier Excel.', 'error');
                        return;
                    }

                    submitBtn.disabled = true;
                    btnText.innerHTML =
                        '<span class="acid-spinner acid-spinner--sm" aria-hidden="true"></span> Importation en cours...';
                });
            })();

            /* Affichage detaille des erreurs d'import (appele par le controleur) */
            function showErrors(errors) {
                const errorsContent = document.getElementById('errorsContent');

                if (typeof errors === 'string') {
                    try { errors = JSON.parse(errors); } catch (e) { errors = [errors]; }
                }

                const wrap = document.createElement('div');

                const renderList = (items) => {
                    const ul = document.createElement('ul');
                    ul.style.margin = '0';
                    ul.style.paddingInlineStart = '1.125rem';
                    (Array.isArray(items) ? items : [items]).forEach((item) => {
                        const li = document.createElement('li');
                        li.textContent = item;
                        ul.appendChild(li);
                    });
                    return ul;
                };

                if (Array.isArray(errors)) {
                    const alert = document.createElement('div');
                    alert.className = 'acid-alert acid-alert--danger';
                    const body = document.createElement('div');
                    body.className = 'acid-alert__body';
                    body.appendChild(renderList(errors));
                    alert.appendChild(body);
                    wrap.appendChild(alert);
                } else if (errors && typeof errors === 'object') {
                    Object.entries(errors).forEach(([key, value]) => {
                        const alert = document.createElement('div');
                        alert.className = 'acid-alert acid-alert--danger';
                        alert.style.marginBlockEnd = 'var(--acid-space-3)';

                        const body = document.createElement('div');
                        body.className = 'acid-alert__body';

                        const title = document.createElement('p');
                        title.className = 'acid-alert__title';
                        title.textContent = 'Ligne ' + key;

                        body.appendChild(title);
                        body.appendChild(renderList(value));
                        alert.appendChild(body);
                        wrap.appendChild(alert);
                    });
                }

                errorsContent.innerHTML = '';
                errorsContent.appendChild(wrap);

                new bootstrap.Modal(document.getElementById('errorsModal')).show();
            }
        </script>
    @endpush
</x-app-layout>
