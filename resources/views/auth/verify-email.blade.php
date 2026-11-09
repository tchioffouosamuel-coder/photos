<x-guest-layout>
    <h1 class="acid-auth__title">Verifiez votre e-mail</h1>
    <p class="acid-auth__lead">
        Merci pour votre inscription. Avant de commencer, confirmez votre adresse
        e-mail en cliquant sur le lien que nous venons de vous envoyer.
        Si vous ne l'avez pas recu, nous pouvons vous en renvoyer un.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="acid-auth-status" role="status">
            <i class="fas fa-circle-check" aria-hidden="true" style="margin-top:.15rem;"></i>
            <span>
                Un nouveau lien de verification a ete envoye a l'adresse indiquee
                lors de votre inscription.
            </span>
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <x-primary-button class="acid-btn--lg acid-btn--block">
            <i class="fas fa-paper-plane" aria-hidden="true"></i>
            Renvoyer l'e-mail de verification
        </x-primary-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="acid-auth__footer">
        @csrf
        <button type="submit" class="acid-btn acid-btn--ghost acid-btn--sm">
            <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>
            Se deconnecter
        </button>
    </form>
</x-guest-layout>
