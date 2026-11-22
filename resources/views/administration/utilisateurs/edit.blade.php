<x-app-layout>
    <div class="acid-breadcrumb acid-no-print" aria-label="Fil d'Ariane">
        <a href="{{ route('administration.utilisateurs.index') }}">Comptes</a>
        <span aria-hidden="true">&rsaquo;</span>
        <span aria-current="page">{{ $utilisateur->name }}</span>
    </div>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Administration</span>
            <h1 class="acid-page-header__title">{{ $utilisateur->name }}</h1>
            <p class="acid-page-header__lead">
                {{ $utilisateur->email }} &middot; {{ $utilisateur->role_libelle }}
            </p>
        </div>

        <div class="acid-page-header__actions">
            @can(\App\Support\Permissions::JOURNAL_CONSULTER)
                <a href="{{ route('administration.journal.index', ['auteur' => $utilisateur->id]) }}"
                   class="acid-btn acid-btn--neutral">
                    <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                    Son activité
                </a>
            @endcan
        </div>
    </div>

    <form method="POST" action="{{ route('administration.utilisateurs.update', $utilisateur) }}">
        @csrf
        @method('PUT')

        @include('administration.utilisateurs._form')

        <div class="acid-form-actions">
            <a href="{{ route('administration.utilisateurs.index') }}" class="acid-btn acid-btn--neutral">
                Annuler
            </a>
            <div class="acid-form-actions__end">
                <button type="submit" class="acid-btn acid-btn--primary">
                    <i class="fas fa-floppy-disk" aria-hidden="true"></i>
                    Enregistrer
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
