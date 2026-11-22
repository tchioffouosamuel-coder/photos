<x-app-layout>
    <div class="acid-breadcrumb acid-no-print" aria-label="Fil d'Ariane">
        <a href="{{ route('dashboard') }}">Tableau de bord</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page">Comptes</span>
    </div>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Administration</span>
            <h1 class="acid-page-header__title">Comptes et accès</h1>
            <p class="acid-page-header__lead">
                Chaque compte reçoit un rôle, qui détermine ce qu'il peut faire.
            </p>
        </div>

        <div class="acid-page-header__actions">
            <a href="{{ route('administration.roles.index') }}" class="acid-btn acid-btn--neutral">
                <i class="fas fa-shield-halved" aria-hidden="true"></i>
                Rôles et permissions
            </a>
            @can(\App\Support\Permissions::JOURNAL_CONSULTER)
                <a href="{{ route('administration.journal.index') }}" class="acid-btn acid-btn--neutral">
                    <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                    Journal
                </a>
            @endcan
            <a href="{{ route('administration.utilisateurs.create') }}" class="acid-btn acid-btn--primary">
                <i class="fas fa-user-plus" aria-hidden="true"></i>
                Nouveau compte
            </a>
        </div>
    </div>

    {{-- Répartition par rôle --}}
    <div class="acid-grid acid-grid--stats" style="margin-block-end:var(--acid-space-5);">
        @foreach ($roles as $role)
            <div class="acid-stat">
                <span class="acid-stat__icon" aria-hidden="true">
                    <i class="fas fa-user-shield"></i>
                </span>
                <span class="acid-stat__body">
                    <span class="acid-stat__label">{{ $role->nom }}</span>
                    <span class="acid-stat__value">{{ $role->users_count }}</span>
                    <span class="acid-stat__hint">{{ $role->users_count > 1 ? 'comptes' : 'compte' }}</span>
                </span>
            </div>
        @endforeach
    </div>

    <section class="acid-card">
        <div class="acid-toolbar">
            <form method="GET" class="acid-toolbar__grow" style="display:contents;">
                <div class="acid-toolbar__grow">
                    <div class="acid-input-group">
                        <i class="fas fa-magnifying-glass acid-input-group__icon" aria-hidden="true"></i>
                        <input type="search" name="recherche" class="acid-input"
                               value="{{ request('recherche') }}"
                               placeholder="Rechercher un nom ou une adresse e-mail"
                               aria-label="Rechercher un compte">
                    </div>
                </div>

                <div style="flex:0 0 14rem;">
                    <select name="role" class="acid-select" aria-label="Filtrer par rôle"
                            onchange="this.form.submit()">
                        <option value="">Tous les rôles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(request('role') == $role->id)>
                                {{ $role->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="acid-btn acid-btn--neutral">
                    <i class="fas fa-filter" aria-hidden="true"></i> Filtrer
                </button>
            </form>
        </div>

        <div class="acid-table-wrap">
            <table class="acid-table acid-table--tiles">
                <thead>
                    <tr>
                        <th scope="col">Compte</th>
                        <th scope="col">Rôle</th>
                        <th scope="col" class="acid-hide-md">Périmètre</th>
                        <th scope="col" class="acid-table__actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($utilisateurs as $utilisateur)
                        <tr>
                            <td data-label="Compte" data-cell="identity">
                                <div class="acid-identity">
                                    <span class="acid-avatar acid-avatar--navy" aria-hidden="true">
                                        {{ $utilisateur->initiale }}
                                    </span>
                                    <span class="acid-identity__text">
                                        <strong>
                                            {{ $utilisateur->name }}
                                            @if ($utilisateur->is(auth()->user()))
                                                <span class="acid-badge acid-badge--teal">vous</span>
                                            @endif
                                        </strong>
                                        <span>{{ $utilisateur->email }}</span>
                                    </span>
                                </div>
                            </td>

                            <td data-label="Rôle" data-cell="meta">
                                @if ($utilisateur->estDesactive())
                                    <span class="acid-badge acid-badge--coral">
                                        <i class="fas fa-ban" aria-hidden="true"></i> Désactivé
                                    </span>
                                @elseif ($utilisateur->estAdministrateur())
                                    <span class="acid-badge acid-badge--solid-navy">{{ $utilisateur->role_libelle }}</span>
                                @else
                                    <span class="acid-badge acid-badge--navy">{{ $utilisateur->role_libelle }}</span>
                                @endif
                            </td>

                            <td data-label="Périmètre" data-cell="meta" class="acid-hide-md">
                                @if ($utilisateur->aPorteeGlobale())
                                    <span class="acid-badge acid-badge--solid-navy">
                                        <i class="fas fa-globe" aria-hidden="true"></i>
                                        Tous les établissements
                                    </span>
                                @else
                                    <span class="acid-table__primary">{{ $utilisateur->etablissement_libelle }}</span>

                                    <span class="acid-table__meta" style="display:block;">
                                        @if ($utilisateur->estLimiteAuxClassesAssignees())
                                            {{ $utilisateur->classes->count() }} classe(s) :
                                            {{ $utilisateur->classes->pluck('nom')->take(3)->implode(', ') }}{{ $utilisateur->classes->count() > 3 ? '…' : '' }}
                                        @else
                                            Tout l'établissement
                                        @endif
                                    </span>
                                @endif
                            </td>

                            <td data-label="Actions" data-cell="actions" class="acid-table__actions">
                                <div class="acid-btn-group" style="justify-content:flex-end;">
                                    <a href="{{ route('administration.utilisateurs.edit', $utilisateur) }}"
                                       class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon"
                                       title="Modifier ce compte" aria-label="Modifier {{ $utilisateur->name }}">
                                        <i class="fas fa-pen" aria-hidden="true"></i>
                                    </a>

                                    @can('delete', $utilisateur)
                                        <form method="POST"
                                              action="{{ route('administration.utilisateurs.destroy', $utilisateur) }}"
                                              style="display:inline;"
                                              data-acid-confirm="Supprimer le compte de {{ $utilisateur->name }} ? Ses traces resteront au journal.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="acid-btn acid-btn--outline-coral acid-btn--sm acid-btn--icon"
                                                    title="Supprimer ce compte" aria-label="Supprimer {{ $utilisateur->name }}">
                                                <i class="fas fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon"
                                              style="opacity:.4;cursor:not-allowed;"
                                              title="{{ $utilisateur->is(auth()->user())
                                                    ? 'Vous ne pouvez pas supprimer votre propre compte'
                                                    : 'Dernier administrateur : suppression impossible' }}"
                                              aria-hidden="true">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="acid-empty">
                                    <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-users"></i></span>
                                    <h3 class="acid-empty__title">Aucun compte ne correspond</h3>
                                    <p class="acid-empty__text">Modifiez votre recherche ou le filtre de rôle.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($utilisateurs->hasPages())
            <div class="acid-card__footer">
                {{ $utilisateurs->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
