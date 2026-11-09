<x-guest-layout>
    <h1 class="acid-auth__title">Connexion</h1>
    <p class="acid-auth__lead">Accedez a votre espace de gestion de l'identification scolaire.</p>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="acid-field">
            <x-input-label for="email" value="Adresse e-mail" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-envelope acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="email" type="email" name="email" :value="old('email')"
                              class="{{ $errors->get('email') ? 'is-invalid' : '' }}"
                              placeholder="nom@etablissement.cm"
                              required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="acid-field">
            <x-input-label for="password" value="Mot de passe" class="acid-label--required" />
            <div class="acid-input-group">
                <i class="fas fa-lock acid-input-group__icon" aria-hidden="true"></i>
                <x-text-input id="password" type="password" name="password"
                              class="{{ $errors->get('password') ? 'is-invalid' : '' }}"
                              placeholder="Votre mot de passe"
                              required autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="acid-row acid-row--between" style="margin-block-end:var(--acid-space-6);">
            <label class="acid-check" for="remember_me">
                <input id="remember_me" type="checkbox" name="remember">
                <span class="acid-check__text">Se souvenir de moi</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   style="font-size:var(--acid-text-sm);font-weight:var(--acid-fw-semibold);">
                    Mot de passe oublie ?
                </a>
            @endif
        </div>

        <x-primary-button class="acid-btn--lg acid-btn--block">
            <i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i>
            Se connecter
        </x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="acid-auth__footer">
            Pas encore de compte ?
            <a href="{{ route('register') }}">Creer un acces</a>
        </p>
    @endif
</x-guest-layout>
