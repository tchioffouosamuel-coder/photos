{{--
  | Navigation principale ACID.
  | Fond bleu marine, element actif souligne par un accent turquoise.
--}}
<aside class="acid-sidebar" id="acidSidebar" aria-label="Navigation principale">

    <a href="{{ route('dashboard') }}" class="acid-sidebar__brand">
        <x-acid-logo variant="light" size="sm" :tagline="true" />
    </a>

    <nav class="acid-sidebar__nav">

        <p class="acid-sidebar__section">Pilotage</p>

        <a href="{{ route('dashboard') }}"
           class="acid-sidebar__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
           @if (request()->routeIs('dashboard')) aria-current="page" @endif>
            <i class="fas fa-gauge-high" aria-hidden="true"></i>
            <span>Tableau de bord</span>
        </a>

        <a href="{{ route('statistics.index') }}"
           class="acid-sidebar__link {{ request()->routeIs('statistics.index') ? 'is-active' : '' }}"
           @if (request()->routeIs('statistics.index')) aria-current="page" @endif>
            <i class="fas fa-chart-column" aria-hidden="true"></i>
            <span>Statistiques</span>
        </a>

        <a href="{{ route('statistics.no-photos') }}"
           class="acid-sidebar__link {{ request()->routeIs('statistics.no-photos') ? 'is-active' : '' }}"
           @if (request()->routeIs('statistics.no-photos')) aria-current="page" @endif>
            <i class="fas fa-user-slash" aria-hidden="true"></i>
            <span>Photos manquantes</span>
        </a>

        <p class="acid-sidebar__section">Gestion scolaire</p>

        <a href="{{ route('classes.index') }}"
           class="acid-sidebar__link {{ request()->routeIs('classes.*') || request()->routeIs('etudiants.*') || request()->routeIs('candidats-libres.*') ? 'is-active' : '' }}"
           @if (request()->routeIs('classes.*')) aria-current="page" @endif>
            <i class="fas fa-school" aria-hidden="true"></i>
            <span>Classes &amp; eleves</span>
        </a>

        <a href="{{ route('import.index') }}"
           class="acid-sidebar__link {{ request()->routeIs('import.*') ? 'is-active' : '' }}"
           @if (request()->routeIs('import.*')) aria-current="page" @endif>
            <i class="fas fa-file-import" aria-hidden="true"></i>
            <span>Importer une liste</span>
        </a>

        <p class="acid-sidebar__section">Production</p>

        <a href="{{ route('cards.index') }}"
           class="acid-sidebar__link {{ request()->routeIs('cards.*') ? 'is-active' : '' }}"
           @if (request()->routeIs('cards.*')) aria-current="page" @endif>
            <i class="fas fa-id-card" aria-hidden="true"></i>
            <span>Cartes &amp; documents</span>
        </a>
    </nav>

    <div class="acid-sidebar__footer">
        <div class="dropup w-100">
            <a href="#" class="acid-sidebar__user" data-bs-toggle="dropdown" aria-expanded="false"
               role="button" aria-label="Menu du compte">
                <span class="acid-avatar acid-avatar--sm" aria-hidden="true">
                    {{ \Illuminate\Support\Str::of(Auth::user()?->name ?? 'U')->substr(0, 1)->upper() }}
                </span>
                <span class="acid-sidebar__meta">
                    <strong>{{ Auth::user()?->name ?? 'Utilisateur' }}</strong>
                    <span>{{ config('school.name', 'Etablissement') }}</span>
                </span>
                <i class="fas fa-chevron-up ms-auto small" aria-hidden="true"
                   style="color:rgba(255,255,255,.45);"></i>
            </a>

            <ul class="dropdown-menu w-100">
                <li><h6 class="dropdown-header">Compte</h6></li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fas fa-user" aria-hidden="true"></i> Mon profil
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
</aside>

<div class="acid-overlay" id="acidOverlay" aria-hidden="true"></div>
