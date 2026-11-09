<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="acid-field">
        <x-input-label for="update_password_current_password" value="Mot de passe actuel"
                       class="acid-label--required" />
        <x-text-input id="update_password_current_password" name="current_password" type="password"
                      class="{{ $errors->updatePassword->get('current_password') ? 'is-invalid' : '' }}"
                      autocomplete="current-password" />
        <x-input-error :messages="$errors->updatePassword->get('current_password')" />
    </div>

    <div class="acid-form-grid acid-form-grid--2">
        <div class="acid-field" style="margin:0;">
            <x-input-label for="update_password_password" value="Nouveau mot de passe"
                           class="acid-label--required" />
            <x-text-input id="update_password_password" name="password" type="password"
                          class="{{ $errors->updatePassword->get('password') ? 'is-invalid' : '' }}"
                          autocomplete="new-password" />
            <span class="acid-hint">8 caracteres minimum.</span>
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="acid-field" style="margin:0;">
            <x-input-label for="update_password_password_confirmation" value="Confirmation"
                           class="acid-label--required" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation"
                          type="password"
                          class="{{ $errors->updatePassword->get('password_confirmation') ? 'is-invalid' : '' }}"
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>
    </div>

    <div class="acid-form-actions">
        <x-primary-button>
            <i class="fas fa-key" aria-hidden="true"></i>
            Mettre a jour le mot de passe
        </x-primary-button>

        @if (session('status') === 'password-updated')
            <span class="acid-badge acid-badge--teal">
                <i class="fas fa-check" aria-hidden="true"></i> Mot de passe modifie
            </span>
        @endif
    </div>
</form>
