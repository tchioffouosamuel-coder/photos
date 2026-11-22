{{--
  | Fiche d'établissement, partagée entre création et modification.
  |
  | $etablissement : l'établissement édité, ou null en création
--}}
@php
    $edition = isset($etablissement) && $etablissement->exists;
@endphp

<div class="acid-grid" style="grid-template-columns:minmax(0,2fr) minmax(280px,1fr);align-items:start;">

    <section class="acid-card">
        <div class="acid-card__header">
            <div>
                <h2 class="acid-card__title">Identité</h2>
                <p class="acid-card__subtitle">Les champs marqués d'une étoile sont obligatoires.</p>
            </div>
        </div>

        <div class="acid-card__body">
            <div class="acid-form-grid acid-form-grid--2">
                <div class="acid-field">
                    <label for="nom" class="acid-label acid-label--required">Nom de l'établissement</label>
                    <input type="text" id="nom" name="nom" class="acid-input" required
                           value="{{ old('nom', $etablissement->nom ?? '') }}">
                    <x-input-error :messages="$errors->get('nom')" class="acid-error" />
                </div>

                <div class="acid-field">
                    <label for="code" class="acid-label acid-label--required">Code</label>
                    <input type="text" id="code" name="code" class="acid-input" required
                           value="{{ old('code', $etablissement->code ?? '') }}">
                    <span class="acid-hint">
                        Imprimé sur les photos 4×4 et les documents officiels.
                    </span>
                    <x-input-error :messages="$errors->get('code')" class="acid-error" />
                </div>

                <div class="acid-field">
                    <label for="sigle" class="acid-label">Sigle</label>
                    <input type="text" id="sigle" name="sigle" class="acid-input" maxlength="30"
                           value="{{ old('sigle', $etablissement->sigle ?? '') }}">
                    <span class="acid-hint">Forme courte, utilisée dans les listes.</span>
                    <x-input-error :messages="$errors->get('sigle')" class="acid-error" />
                </div>

                <div class="acid-field">
                    <label for="ville" class="acid-label">Ville</label>
                    <input type="text" id="ville" name="ville" class="acid-input"
                           value="{{ old('ville', $etablissement->ville ?? '') }}">
                    <x-input-error :messages="$errors->get('ville')" class="acid-error" />
                </div>

                <div class="acid-field">
                    <label for="region" class="acid-label">Région</label>
                    <input type="text" id="region" name="region" class="acid-input"
                           value="{{ old('region', $etablissement->region ?? '') }}">
                    <x-input-error :messages="$errors->get('region')" class="acid-error" />
                </div>

                <div class="acid-field">
                    <label for="telephone" class="acid-label">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" class="acid-input"
                           value="{{ old('telephone', $etablissement->telephone ?? '') }}">
                    <x-input-error :messages="$errors->get('telephone')" class="acid-error" />
                </div>

                <div class="acid-field">
                    <label for="email" class="acid-label">Adresse e-mail</label>
                    <input type="email" id="email" name="email" class="acid-input"
                           value="{{ old('email', $etablissement->email ?? '') }}">
                    <x-input-error :messages="$errors->get('email')" class="acid-error" />
                </div>

                <div class="acid-field">
                    <label for="adresse" class="acid-label">Adresse</label>
                    <input type="text" id="adresse" name="adresse" class="acid-input"
                           value="{{ old('adresse', $etablissement->adresse ?? '') }}">
                    <x-input-error :messages="$errors->get('adresse')" class="acid-error" />
                </div>
            </div>
        </div>
    </section>

    <div class="acid-stack">
        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Stockage des photos</h2>
                </div>
            </div>

            <div class="acid-card__body">
                @if ($edition)
                    <dl style="margin:0;display:grid;gap:var(--acid-space-3);">
                        <div>
                            <dt class="acid-stat__label">Dossier des photos</dt>
                            <dd style="margin:0;" class="acid-num">
                                {{ $etablissement->prefixe_photos
                                    ? 'photos/' . $etablissement->prefixe_photos . '/'
                                    : 'photos/' }}
                            </dd>
                        </div>
                    </dl>

                    <p class="acid-hint" style="margin:var(--acid-space-3) 0 0;">
                        @if ($etablissement->prefixe_photos)
                            Le dossier ne change pas si vous modifiez le code : le renommer
                            rendrait introuvables toutes les photos déjà rangées.
                        @else
                            Cet établissement est celui d'origine : ses photos sont rangées
                            directement sous <span class="acid-num">photos/</span>, comme avant
                            le passage au multi-établissement. Aucun fichier n'a été déplacé.
                        @endif
                    </p>
                @else
                    <p class="acid-hint" style="margin:0;">
                        Un dossier propre à cet établissement sera créé à partir du code, afin
                        que ses photos ne se mélangent pas à celles des autres.
                    </p>
                @endif
            </div>
        </section>

        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Après la création</h2>
                </div>
            </div>

            <div class="acid-card__body">
                <p class="acid-hint" style="margin:0;">
                    Un établissement naît vide. Pour qu'il serve, il lui faut
                    <strong>ses classes</strong> et <strong>au moins un compte</strong> qui lui
                    soit rattaché — sans quoi seule l'administration du réseau y aura accès.
                </p>
            </div>
        </section>
    </div>
</div>
