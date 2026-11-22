<x-app-layout>
    <div class="acid-breadcrumb acid-no-print" aria-label="Fil d'Ariane">
        <a href="{{ route('administration.etablissements.index') }}">Établissements</a>
        <span aria-hidden="true">&rsaquo;</span>
        <span aria-current="page">Nouveau</span>
    </div>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Administration</span>
            <h1 class="acid-page-header__title">Nouvel établissement</h1>
            <p class="acid-page-header__lead">
                Ses classes, ses élèves et ses photos resteront invisibles aux autres établissements.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('administration.etablissements.store') }}">
        @csrf

        @include('administration.etablissements._form')

        <div class="acid-form-actions">
            <a href="{{ route('administration.etablissements.index') }}" class="acid-btn acid-btn--neutral">
                Annuler
            </a>
            <div class="acid-form-actions__end">
                <button type="submit" class="acid-btn acid-btn--primary">
                    <i class="fas fa-school" aria-hidden="true"></i>
                    Créer l'établissement
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
