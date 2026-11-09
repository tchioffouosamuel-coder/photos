<x-guest-layout>
    <h1 class="acid-auth__title">Mot de passe oublie</h1>
    <p class="acid-auth__lead">
        Indiquez votre adresse e-mail : nous vous enverrons un lien
        permettant de definir un nouveau mot de passe.
    </p>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="acid-field">
            <x-input-label for="email" value="Adresse e-mail" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-envelope acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="email" type="email" name="email" :value="old('email')"
                              class="{{ $errors->get('email') ? 'is-invalid' : '' }}"
                              placeholder="nom@etablissement.cm"
                              required autofocus />
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="acid-btn--lg acid-btn--block">
            <i class="fas fa-paper-plane" aria-hidden="true"></i>
            Envoyer le lien de reinitialisation
        </x-primary-button>
    </form>

    <p class="acid-auth__footer">
        <a href="{{ route('login') }}">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Retour a la connexion
        </a>
    </p>
</x-guest-layout>
