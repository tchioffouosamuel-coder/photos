<x-guest-layout>
    <div class="acid-alert acid-alert--navy" style="margin-block-end:var(--acid-space-6);">
        <i class="fas fa-shield-halved acid-alert__icon" aria-hidden="true"></i>
        <div class="acid-alert__body">
            <p class="acid-alert__title">Zone securisee</p>
            <p style="margin:0;font-size:var(--acid-text-sm);">
                Confirmez votre mot de passe pour continuer.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="acid-field">
            <x-input-label for="password" value="Mot de passe" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-lock acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="password" type="password" name="password"
                              class="{{ $errors->get('password') ? 'is-invalid' : '' }}"
                              required autocomplete="current-password" autofocus />
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="acid-btn--lg acid-btn--block">
            <i class="fas fa-check" aria-hidden="true"></i>
            Confirmer
        </x-primary-button>
    </form>
</x-guest-layout>
