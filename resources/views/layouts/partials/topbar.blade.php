{{--
  | Barre superieure : fond blanc, contexte de page, recherche, notifications,
  | menu profil. La recherche filtre instantanement les elements de la page
  | portant l'attribut data-acid-searchable (aucun appel reseau, aucune route).
--}}
<header class="acid-topbar">

    <button type="button" class="acid-topbar__toggle" id="acidNavToggle"
            aria-controls="acidSidebar" aria-expanded="false" aria-label="Ouvrir la navigation">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>

    <div class="acid-topbar__context">
        <strong>{{ config('school.name', 'Etablissement') }}</strong>
        <span>
            Annee scolaire {{ date('Y') }}-{{ date('Y') + 1 }}
            @if (config('school.code'))
                &middot; Code {{ config('school.code') }}
            @endif
        </span>
    </div>

    <div class="acid-topbar__spacer"></div>

    <label class="acid-search" for="acidSearch">
        <i class="fas fa-magnifying-glass acid-search__icon" aria-hidden="true"></i>
        <input type="search" id="acidSearch" class="acid-search__input"
               placeholder="Rechercher dans la page..." autocomplete="off"
               aria-label="Rechercher dans la page">
    </label>

    <div class="acid-topbar__actions">

        {{-- Acces rapide a la production --}}
        <a href="{{ route('cards.index') }}" class="acid-icon-btn d-none d-md-inline-flex"
           title="Cartes et documents" aria-label="Cartes et documents">
            <i class="fas fa-id-card" aria-hidden="true"></i>
        </a>

        {{-- Alertes : eleves sans photo --}}
        <a href="{{ route('statistics.no-photos') }}" class="acid-icon-btn"
           title="Eleves sans photo" aria-label="Eleves sans photo">
            <i class="fas fa-bell" aria-hidden="true"></i>
            @if (!empty($pendingPhotos))
                <span class="acid-icon-btn__dot" aria-hidden="true"></span>
                <span class="acid-sr-only">{{ $pendingPhotos }} eleves sans photo</span>
            @endif
        </a>

        <div class="dropdown">
            <button type="button" class="acid-usermenu" data-bs-toggle="dropdown" aria-expanded="false"
                    aria-label="Menu du compte">
                <span class="acid-avatar" aria-hidden="true">
                    {{ \Illuminate\Support\Str::of(Auth::user()?->name ?? 'U')->substr(0, 1)->upper() }}
                </span>
                <span class="acid-usermenu__name">{{ Auth::user()?->name ?? 'Utilisateur' }}</span>
                <i class="fas fa-chevron-down acid-usermenu__caret" aria-hidden="true"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end" style="min-width:14rem;">
                <li>
                    <h6 class="dropdown-header">
                        {{ Auth::user()?->email ?? '' }}
                    </h6>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fas fa-user" aria-hidden="true"></i> Mon profil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('statistics.index') }}">
                        <i class="fas fa-chart-column" aria-hidden="true"></i> Statistiques
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item dropdown-item--danger">
                            <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i> Deconnexion
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
