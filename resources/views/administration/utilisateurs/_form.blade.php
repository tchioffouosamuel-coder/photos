{{--
  | Formulaire de compte, partagé entre création et modification.
  |
  | $utilisateur     : le compte édité, ou null en création
  | $peutChangerRole : false quand la Policy l'interdit (son propre compte,
  |                    ou dernier administrateur)
  | $peutChangerEtablissement : réservé à la portée globale
--}}
@php
    $edition = isset($utilisateur) && $utilisateur->exists;
    $peutChangerRole = $peutChangerRole ?? true;
    $peutChangerEtablissement = $peutChangerEtablissement ?? false;
    $etablissements = $etablissements ?? collect();
    $classesChoisies = old('classes', $edition ? $utilisateur->classes->pluck('id')->all() : []);

    $etablissementChoisi = old(
        'etablissement_id',
        $edition ? $utilisateur->etablissement_id : ($etablissementParDefaut ?? null)
    );
@endphp

<div class="acid-grid" style="grid-template-columns:minmax(0,2fr) minmax(280px,1fr);align-items:start;">

    <section class="acid-card">
        <div class="acid-card__header">
            <div>
                <h2 class="acid-card__title">Identité et accès</h2>
                <p class="acid-card__subtitle">Les champs marqués d'une étoile sont obligatoires.</p>
            </div>
        </div>

        <div class="acid-card__body">
            <div class="acid-form-grid acid-form-grid--2">
                <div class="acid-field">
                    <label for="name" class="acid-label acid-label--required">Nom et prénoms</label>
                    <input type="text" id="name" name="name" class="acid-input"
                           value="{{ old('name', $utilisateur->name ?? '') }}" required autocomplete="name">
                    <x-input-error :messages="$errors->get('name')" class="acid-error" />
                </div>

                <div class="acid-field">
                    <label for="email" class="acid-label acid-label--required">Adresse e-mail</label>
                    <input type="email" id="email" name="email" class="acid-input"
                           value="{{ old('email', $utilisateur->email ?? '') }}" required autocomplete="email">
                    <span class="acid-hint">Elle sert d'identifiant de connexion.</span>
                    <x-input-error :messages="$errors->get('email')" class="acid-error" />
                </div>

                <div class="acid-field">
                    <label for="password" class="acid-label {{ $edition ? '' : 'acid-label--required' }}">
                        Mot de passe
                    </label>
                    <input type="password" id="password" name="password" class="acid-input"
                           autocomplete="new-password" {{ $edition ? '' : 'required' }}>
                    <span class="acid-hint">
                        @if ($edition)
                            Laissez vide pour conserver le mot de passe actuel.
                        @else
                            8 caractères minimum, avec au moins une lettre et un chiffre.
                        @endif
                    </span>
                    <x-input-error :messages="$errors->get('password')" class="acid-error" />
                </div>

                <div class="acid-field">
                    <label for="password_confirmation" class="acid-label {{ $edition ? '' : 'acid-label--required' }}">
                        Confirmation
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="acid-input" autocomplete="new-password" {{ $edition ? '' : 'required' }}>
                </div>
            </div>
        </div>
    </section>

    <div class="acid-stack">

        {{-- Rôle --}}
        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Rôle</h2>
                    <p class="acid-card__subtitle">Il détermine tout ce que le compte peut faire.</p>
                </div>
            </div>

            <div class="acid-card__body">
                @if ($peutChangerRole)
                    <div class="acid-field">
                        <label for="role_id" class="acid-label acid-label--required">Rôle attribué</label>
                        <select id="role_id" name="role_id" class="acid-select" required
                                onchange="acidDecrireRole(this)">
                            <option value="">Choisir un rôle</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                        data-description="{{ $role->description }}"
                                        @selected(old('role_id', $utilisateur->role_id ?? '') == $role->id)>
                                    {{ $role->nom }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role_id')" class="acid-error" />
                    </div>

                    <p class="acid-hint" id="roleDescription" style="margin:0;">
                        {{ optional($roles->firstWhere('id', old('role_id', $utilisateur->role_id ?? null)))->description }}
                    </p>
                @else
                    <div class="acid-alert acid-alert--warning">
                        <i class="fas fa-lock acid-alert__icon" aria-hidden="true"></i>
                        <div class="acid-alert__body">
                            <p class="acid-alert__title">Rôle non modifiable</p>
                            <p style="margin:0;">
                                @if ($edition && $utilisateur->is(auth()->user()))
                                    Vous ne pouvez pas changer votre propre niveau d'accès.
                                @else
                                    Ce compte est le dernier administrateur : le rétrograder
                                    rendrait l'application inadministrable.
                                @endif
                            </p>
                            <p style="margin:var(--acid-space-2) 0 0;">
                                Rôle actuel : <strong>{{ $utilisateur->role_libelle }}</strong>
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- Établissement --}}
        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Établissement</h2>
                    <p class="acid-card__subtitle">Ce que le compte peut voir, avant tout rôle.</p>
                </div>
            </div>

            <div class="acid-card__body">
                @if ($peutChangerEtablissement)
                    <div class="acid-field">
                        <label for="etablissement_id" class="acid-label">Rattachement</label>
                        <select id="etablissement_id" name="etablissement_id" class="acid-select">
                            <option value="">Tous les établissements (portée globale)</option>
                            @foreach ($etablissements as $etab)
                                <option value="{{ $etab->id }}"
                                        @selected((string) $etablissementChoisi === (string) $etab->id)>
                                    {{ $etab->nom }}
                                </option>
                            @endforeach
                        </select>
                        <span class="acid-hint">
                            Un compte rattaché ne voit que cet établissement, quel que soit son rôle.
                            La portée globale donne accès à tous : réservez-la à l'administration du réseau.
                        </span>
                        <x-input-error :messages="$errors->get('etablissement_id')" class="acid-error" />
                    </div>

                    @if ($edition)
                        <div class="acid-alert acid-alert--warning" style="margin-block-start:var(--acid-space-3);">
                            <i class="fas fa-triangle-exclamation acid-alert__icon" aria-hidden="true"></i>
                            <div class="acid-alert__body">
                                <p style="margin:0;">
                                    Changer l'établissement retire les classes assignées :
                                    elles appartiennent à l'établissement que le compte quitte.
                                </p>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="acid-alert acid-alert--navy">
                        <i class="fas fa-lock acid-alert__icon" aria-hidden="true"></i>
                        <div class="acid-alert__body">
                            <p class="acid-alert__title">Rattachement non modifiable</p>
                            <p style="margin:0;">
                                Seule l'administration du réseau peut déplacer un compte d'un
                                établissement à l'autre.
                            </p>
                            <p style="margin:var(--acid-space-2) 0 0;">
                                Établissement :
                                <strong>
                                    {{ $edition
                                        ? $utilisateur->etablissement_libelle
                                        : (auth()->user()?->etablissement?->nom ?? 'le vôtre') }}
                                </strong>
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- Périmètre --}}
        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Périmètre</h2>
                    <p class="acid-card__subtitle">Classes confiées à ce compte.</p>
                </div>
            </div>

            <div class="acid-card__body">
                <div class="acid-alert acid-alert--navy" style="margin-block-end:var(--acid-space-4);">
                    <i class="fas fa-circle-info acid-alert__icon" aria-hidden="true"></i>
                    <div class="acid-alert__body">
                        <p style="margin:0;">
                            N'a d'effet que pour les rôles limités par classe, l'opérateur photo
                            aujourd'hui. <strong>Aucune classe cochée</strong> signifie
                            accès à tout l'établissement de rattachement.
                        </p>
                    </div>
                </div>

                @forelse ($classes as $classe)
                    <label class="acid-check" style="margin-block-end:var(--acid-space-2);">
                        <input type="checkbox" name="classes[]" value="{{ $classe->id }}"
                               @checked(in_array($classe->id, array_map('intval', (array) $classesChoisies), true))>
                        <span class="acid-check__text">
                            <strong>{{ $classe->nom }}</strong>
                            <span>{{ $classe->annee_scolaire }}</span>
                        </span>
                    </label>
                @empty
                    <p class="acid-hint" style="margin:0;">
                        @if ($edition && $utilisateur->aPorteeGlobale())
                            Ce compte a une portée globale : il voit toutes les classes de tous
                            les établissements, une assignation n'aurait pas de sens.
                        @else
                            Aucune classe enregistrée dans cet établissement pour l'instant.
                        @endif
                    </p>
                @endforelse
            </div>
        </section>
    </div>
</div>

@push('scripts')
    <script>
        /* Affiche la description du rôle choisi, pour éviter une attribution
           faite au jugé sur le seul intitulé. */
        function acidDecrireRole(select) {
            const cible = document.getElementById('roleDescription');
            if (!cible) return;

            const option = select.options[select.selectedIndex];
            cible.textContent = option ? (option.dataset.description || '') : '';
        }
    </script>
@endpush
