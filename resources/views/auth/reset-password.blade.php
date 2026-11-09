<x-guest-layout>
    <h1 class="acid-auth__title">Nouveau mot de passe</h1>
    <p class="acid-auth__lead">Choisissez un mot de passe solide pour securiser votre acces.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="acid-field">
            <x-input-label for="email" value="Adresse e-mail" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-envelope acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)"
                              class="{{ $errors->get('email') ? 'is-invalid' : '' }}"
                              required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="acid-field">
            <x-input-label for="password" value="Nouveau mot de passe" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-lock acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="password" type="password" name="password"
                              class="{{ $errors->get('password') ? 'is-invalid' : '' }}"
                              placeholder="8 caracteres minimum"
                              required autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="acid-field">
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-lock acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                              class="{{ $errors->get('password_confirmation') ? 'is-invalid' : '' }}"
                              required autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="acid-btn--lg acid-btn--block" style="margin-block-start:var(--acid-space-5);">
            <i class="fas fa-key" aria-hidden="true"></i>
            Enregistrer le mot de passe
        </x-primary-button>
    </form>
</x-guest-layout>
