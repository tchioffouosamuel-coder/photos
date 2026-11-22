<x-app-layout>
    <div class="acid-breadcrumb acid-no-print" aria-label="Fil d'Ariane">
        <a href="{{ route('administration.etablissements.index') }}">Établissements</a>
        <span aria-hidden="true">&rsaquo;</span>
        <span aria-current="page">{{ $etablissement->nom }}</span>
    </div>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Administration</span>
            <h1 class="acid-page-header__title">{{ $etablissement->nom }}</h1>
            <p class="acid-page-header__lead">
                Code <span class="acid-num">{{ $etablissement->code }}</span>
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('administration.etablissements.update', $etablissement) }}">
        @csrf
        @method('PUT')

        @include('administration.etablissements._form')

        <div class="acid-form-actions">
            <a href="{{ route('administration.etablissements.index') }}" class="acid-btn acid-btn--neutral">
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
