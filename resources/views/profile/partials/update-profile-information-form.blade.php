<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="acid-field">
        <x-input-label for="name" value="Nom complet" class="acid-label--required" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)"
                      class="{{ $errors->get('name') ? 'is-invalid' : '' }}"
                      required autofocus autocomplete="name" />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="acid-field">
        <x-input-label for="email" value="Adresse e-mail" class="acid-label--required" />
        <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)"
                      class="{{ $errors->get('email') ? 'is-invalid' : '' }}"
                      required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="acid-alert acid-alert--warning" style="margin-block-start:var(--acid-space-3);">
                <i class="fas fa-triangle-exclamation acid-alert__icon" aria-hidden="true"></i>
                <div class="acid-alert__body">
                    <p class="acid-alert__title">Adresse non verifiee</p>
                    <button form="send-verification" type="submit"
                            class="acid-btn acid-btn--outline-coral acid-btn--sm"
                            style="margin-block-start:var(--acid-space-2);">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        Renvoyer l'e-mail de verification
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p style="margin:var(--acid-space-2) 0 0;font-size:var(--acid-text-sm);
                                  font-weight:var(--acid-fw-semibold);color:var(--acid-teal-700);">
                            Un nouveau lien de verification vous a ete envoye.
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="acid-form-actions">
        <x-primary-button>
            <i class="fas fa-floppy-disk" aria-hidden="true"></i>
            Enregistrer
        </x-primary-button>

        @if (session('status') === 'profile-updated')
            <span class="acid-badge acid-badge--teal">
                <i class="fas fa-check" aria-hidden="true"></i> Enregistre
            </span>
        @endif
    </div>
</form>
