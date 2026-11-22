<x-app-layout>
    <div class="acid-breadcrumb acid-no-print" aria-label="Fil d'Ariane">
        <a href="{{ route('administration.utilisateurs.index') }}">Comptes</a>
        <span aria-hidden="true">&rsaquo;</span>
        <span aria-current="page">Nouveau</span>
    </div>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Administration</span>
            <h1 class="acid-page-header__title">Nouveau compte</h1>
            <p class="acid-page-header__lead">
                Attribuez le rôle le plus restreint qui permette de faire le travail.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('administration.utilisateurs.store') }}">
        @csrf

        @include('administration.utilisateurs._form', ['peutChangerRole' => true])

        <div class="acid-form-actions">
            <a href="{{ route('administration.utilisateurs.index') }}" class="acid-btn acid-btn--neutral">
                Annuler
            </a>
            <div class="acid-form-actions__end">
                <button type="submit" class="acid-btn acid-btn--primary">
                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                    Créer le compte
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
