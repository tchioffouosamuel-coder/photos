{{--
  | Navigation basse ACID : visible uniquement sous 992px (voir acid.css).
  |
  | Quatre destinations au pouce plus un acces au tiroir complet, qui reste la
  | source de verite de la navigation : on ne duplique donc pas tout le menu,
  | seulement les entrees les plus utilisees au quotidien.
--}}
<nav class="acid-bottomnav acid-no-print" aria-label="Navigation principale (mobile)">
    <ul class="acid-bottomnav__list">

        <li>
            <a href="{{ route('dashboard') }}"
               class="acid-bottomnav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
               @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                <span class="acid-bottomnav__icon" aria-hidden="true">
                    <i class="fas fa-gauge-high"></i>
                </span>
                <span class="acid-bottomnav__label">Accueil</span>
            </a>
        </li>

        <li>
            <a href="{{ route('classes.index') }}"
               class="acid-bottomnav__link {{ request()->routeIs('classes.*') || request()->routeIs('etudiants.*') || request()->routeIs('candidats-libres.*') ? 'is-active' : '' }}"
               @if (request()->routeIs('classes.*')) aria-current="page" @endif>
                <span class="acid-bottomnav__icon" aria-hidden="true">
                    <i class="fas fa-school"></i>
                </span>
                <span class="acid-bottomnav__label">Classes</span>
            </a>
        </li>

        <li>
            <a href="{{ route('cards.index') }}"
               class="acid-bottomnav__link {{ request()->routeIs('cards.*') ? 'is-active' : '' }}"
               @if (request()->routeIs('cards.*')) aria-current="page" @endif>
                <span class="acid-bottomnav__icon" aria-hidden="true">
                    <i class="fas fa-id-card"></i>
                </span>
                <span class="acid-bottomnav__label">Cartes</span>
            </a>
        </li>

        <li>
            <a href="{{ route('statistics.no-photos') }}"
               class="acid-bottomnav__link {{ request()->routeIs('statistics.no-photos') ? 'is-active' : '' }}"
               @if (request()->routeIs('statistics.no-photos')) aria-current="page" @endif>
                <span class="acid-bottomnav__icon" aria-hidden="true">
                    <i class="fas fa-user-slash"></i>
                    @if (!empty($pendingPhotos))
                        <span class="acid-bottomnav__dot">
                            {{ $pendingPhotos > 99 ? '99+' : $pendingPhotos }}
                        </span>
                    @endif
                </span>
                <span class="acid-bottomnav__label">A photographier</span>
                @if (!empty($pendingPhotos))
                    <span class="acid-sr-only">{{ $pendingPhotos }} eleves sans photo</span>
                @endif
            </a>
        </li>

        <li>
            {{-- Ouvre le tiroir : meme etat que le bouton hamburger de la topbar --}}
            <button type="button" class="acid-bottomnav__link" id="acidNavToggleBottom"
                    aria-controls="acidSidebar" aria-expanded="false">
                <span class="acid-bottomnav__icon" aria-hidden="true">
                    <i class="fas fa-bars"></i>
                </span>
                <span class="acid-bottomnav__label">Menu</span>
            </button>
        </li>
    </ul>
</nav>
