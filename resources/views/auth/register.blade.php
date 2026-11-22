<x-guest-layout>
    <h1 class="acid-auth__title">Créer un accès</h1>
    <p class="acid-auth__lead">Renseignez vos informations pour ouvrir un compte sur la plateforme.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="acid-field">
            <x-input-label for="name" value="Nom complet" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-user acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="name" type="text" name="name" :value="old('name')"
                              class="{{ $errors->get('name') ? 'is-invalid' : '' }}"
                              placeholder="Prénom et nom"
                              required autofocus autocomplete="name" />
            </div>
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="acid-field">
            <x-input-label for="email" value="Adresse e-mail" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-envelope acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="email" type="email" name="email" :value="old('email')"
                              class="{{ $errors->get('email') ? 'is-invalid' : '' }}"
                              placeholder="nom@établissement.cm"
                              required autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="acid-field">
            <x-input-label for="password" value="Mot de passe" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-lock acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="password" type="password" name="password"
                              class="{{ $errors->get('password') ? 'is-invalid' : '' }}"
                              placeholder="8 caractères minimum"
                              required autocomplete="new-password" />
            </div>
            <span class="acid-hint">Utilisez au moins 8 caractères, dont un chiffre.</span>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="acid-field">
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-lock acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                              class="{{ $errors->get('password_confirmation') ? 'is-invalid' : '' }}"
                              placeholder="Répétez le mot de passe"
                              required autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="acid-btn--lg acid-btn--block" style="margin-block-start:var(--acid-space-5);">
            <i class="fas fa-user-plus" aria-hidden="true"></i>
            Créer le compte
        </x-primary-button>
    </form>

    <p class="acid-auth__footer">
        Vous avez déjà un compte ?
        <a href="{{ route('login') }}">Se connecter</a>
    </p>
</x-guest-layout>
