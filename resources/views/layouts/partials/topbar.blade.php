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

    @php
        /*
         | Le contexte affiche l'établissement sur lequel on travaille, et non
         | plus le nom figé dans le .env : avec plusieurs établissements, ce
         | nom décrirait le mauvais pour tout le monde sauf un.
         */
        $acidContexte = app(\App\Support\EtablissementContext::class);
        $acidEtabActif = $acidContexte->actif();
        $acidEtabDispo = $acidContexte->aPorteeGlobale()
            && auth()->user()?->aLaPermission(\App\Support\Permissions::ETABLISSEMENTS_VOIR)
            ? $acidContexte->disponibles()
            : collect();
    @endphp

    <div class="acid-topbar__context">
        <strong>{{ $acidEtabActif->nom ?? ($acidEtabDispo->count() > 1 ? 'Tous les établissements' : 'Établissement') }}</strong>
        <span>
            Année scolaire {{ date('Y') }}-{{ date('Y') + 1 }}
            @if ($acidEtabActif?->code)
                &middot; Code {{ $acidEtabActif->code }}
            @endif
        </span>
    </div>

    {{-- Bascule d'établissement, pour les comptes à portée globale --}}
    @if ($acidEtabDispo->count() > 1)
        <form method="POST" action="{{ route('administration.etablissements.basculer') }}"
              class="acid-topbar__etab d-none d-md-block">
            @csrf
            <label class="acid-sr-only" for="acidEtabSelect">Établissement sur lequel travailler</label>
            <select id="acidEtabSelect" name="etablissement_id" class="acid-select acid-select--sm"
                    onchange="this.form.submit()">
                <option value="">Tous les établissements</option>
                @foreach ($acidEtabDispo as $acidEtab)
                    <option value="{{ $acidEtab->id }}" @selected($acidContexte->idActif() === (int) $acidEtab->id)>
                        {{ $acidEtab->nom }}
                    </option>
                @endforeach
            </select>
        </form>
    @endif

    <div class="acid-topbar__spacer"></div>

    <label class="acid-search" for="acidSearch">
        <i class="fas fa-magnifying-glass acid-search__icon" aria-hidden="true"></i>
        <input type="search" id="acidSearch" class="acid-search__input"
               placeholder="Rechercher dans la page..." autocomplete="off"
               aria-label="Rechercher dans la page">
    </label>

    <div class="acid-topbar__actions">

        {{-- Acces rapide a la production --}}
        @can(\App\Support\Permissions::DOCUMENTS_GENERER)
            <a href="{{ route('cards.index') }}" class="acid-icon-btn d-none d-md-inline-flex"
               title="Cartes et documents" aria-label="Cartes et documents">
                <i class="fas fa-id-card" aria-hidden="true"></i>
            </a>
        @endcan

        {{-- Alertes : eleves sans photo --}}
        @can(\App\Support\Permissions::STATISTIQUES_VOIR)
            <a href="{{ route('statistics.no-photos') }}" class="acid-icon-btn"
               title="Élèves sans photo" aria-label="Élèves sans photo">
                <i class="fas fa-bell" aria-hidden="true"></i>
                @if (!empty($pendingPhotos))
                    <span class="acid-icon-btn__dot" aria-hidden="true"></span>
                    <span class="acid-sr-only">{{ $pendingPhotos }} élèves sans photo</span>
                @endif
            </a>
        @endcan

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
                @can(\App\Support\Permissions::STATISTIQUES_VOIR)
                    <li>
                        <a class="dropdown-item" href="{{ route('statistics.index') }}">
                            <i class="fas fa-chart-column" aria-hidden="true"></i> Statistiques
                        </a>
                    </li>
                @endcan

                {{-- Bascule d'établissement sur mobile, où le sélecteur est masqué --}}
                @if ($acidEtabDispo->count() > 1)
                    <li><hr class="dropdown-divider d-md-none"></li>
                    <li class="d-md-none">
                        <h6 class="dropdown-header">Établissement</h6>
                    </li>
                    @foreach ($acidEtabDispo as $acidEtab)
                        <li class="d-md-none">
                            <form method="POST"
                                  action="{{ route('administration.etablissements.basculer') }}">
                                @csrf
                                <input type="hidden" name="etablissement_id" value="{{ $acidEtab->id }}">
                                <button type="submit" class="dropdown-item">
                                    @if ($acidContexte->idActif() === (int) $acidEtab->id)
                                        <i class="fas fa-check" aria-hidden="true"></i>
                                    @else
                                        <i class="fas fa-school" aria-hidden="true"></i>
                                    @endif
                                    {{ $acidEtab->nom }}
                                </button>
                            </form>
                        </li>
                    @endforeach
                @endif
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item dropdown-item--danger">
                            <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i> Déconnexion
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
