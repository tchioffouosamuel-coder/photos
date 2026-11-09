<x-guest-layout>
    <h1 class="acid-auth__title">Creer un acces</h1>
    <p class="acid-auth__lead">Renseignez vos informations pour ouvrir un compte sur la plateforme.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="acid-field">
            <x-input-label for="name" value="Nom complet" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-user acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="name" type="text" name="name" :value="old('name')"
                              class="{{ $errors->get('name') ? 'is-invalid' : '' }}"
                              placeholder="Prenom et nom"
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
                              placeholder="nom@etablissement.cm"
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
                              placeholder="8 caracteres minimum"
                              required autocomplete="new-password" />
            </div>
            <span class="acid-hint">Utilisez au moins 8 caracteres, dont un chiffre.</span>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="acid-field">
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-lock acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                              class="{{ $errors->get('password_confirmation') ? 'is-invalid' : '' }}"
                              placeholder="Repetez le mot de passe"
                              required autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="acid-btn--lg acid-btn--block" style="margin-block-start:var(--acid-space-5);">
            <i class="fas fa-user-plus" aria-hidden="true"></i>
            Creer le compte
        </x-primary-button>
    </form>

    <p class="acid-auth__footer">
        Vous avez deja un compte ?
        <a href="{{ route('login') }}">Se connecter</a>
    </p>
</x-guest-layout>
