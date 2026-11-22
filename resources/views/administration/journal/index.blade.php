<x-app-layout>
    <div class="acid-breadcrumb acid-no-print" aria-label="Fil d'Ariane">
        <a href="{{ route('dashboard') }}">Tableau de bord</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page">Journal</span>
    </div>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Administration</span>
            <h1 class="acid-page-header__title">Journal d'activité</h1>
            <p class="acid-page-header__lead">
                Qui a fait quoi, quand. Le journal est en ajout seul : aucune entrée
                ne peut être modifiée ni supprimée depuis l'application.
            </p>
        </div>
    </div>

    <div class="acid-grid acid-grid--stats" style="margin-block-end:var(--acid-space-5);">
        <div class="acid-stat">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
            <span class="acid-stat__body">
                <span class="acid-stat__label">Entrées</span>
                <span class="acid-stat__value">{{ number_format($resume['total'], 0, ',', ' ') }}</span>
                <span class="acid-stat__hint">Depuis la mise en service</span>
            </span>
        </div>

        <div class="acid-stat">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-teal-50);color:var(--acid-teal-700);">
                <i class="fas fa-calendar-day"></i>
            </span>
            <span class="acid-stat__body">
                <span class="acid-stat__label">Aujourd'hui</span>
                <span class="acid-stat__value">{{ $resume['aujourdhui'] }}</span>
                <span class="acid-stat__hint">Actions du jour</span>
            </span>
        </div>

        <div class="acid-stat">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-sky-50);color:var(--acid-sky-600);">
                <i class="fas fa-users"></i>
            </span>
            <span class="acid-stat__body">
                <span class="acid-stat__label">Intervenants</span>
                <span class="acid-stat__value">{{ $resume['auteurs'] }}</span>
                <span class="acid-stat__hint">Comptes ayant agi</span>
            </span>
        </div>

        <div class="acid-stat">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-coral-50);color:var(--acid-coral-700);">
                <i class="fas fa-shield-halved"></i>
            </span>
            <span class="acid-stat__body">
                <span class="acid-stat__label">Échecs de connexion</span>
                <span class="acid-stat__value">{{ $resume['echecs'] }}</span>
                <span class="acid-stat__hint">7 derniers jours</span>
            </span>
        </div>
    </div>

    <section class="acid-card">
        <div class="acid-toolbar">
            <form method="GET" style="display:contents;">
                <div class="acid-toolbar__grow">
                    <div class="acid-input-group">
                        <i class="fas fa-magnifying-glass acid-input-group__icon" aria-hidden="true"></i>
                        <input type="search" name="recherche" class="acid-input"
                               value="{{ request('recherche') }}"
                               placeholder="Rechercher un objet ou un auteur"
                               aria-label="Rechercher dans le journal">
                    </div>
                </div>

                <div style="flex:0 0 13rem;">
                    <select name="auteur" class="acid-select" aria-label="Filtrer par auteur">
                        <option value="">Tous les auteurs</option>
                        @foreach ($auteurs as $auteur)
                            <option value="{{ $auteur->id }}" @selected(request('auteur') == $auteur->id)>
                                {{ $auteur->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="flex:0 0 13rem;">
                    <select name="action" class="acid-select" aria-label="Filtrer par action">
                        <option value="">Toutes les actions</option>
                        @foreach ($actions as $code => $libelle)
                            <option value="{{ $code }}" @selected(request('action') === $code)>
                                {{ $libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="flex:0 0 10rem;">
                    <input type="date" name="du" class="acid-input" value="{{ request('du') }}"
                           aria-label="À partir du">
                </div>

                <div style="flex:0 0 10rem;">
                    <input type="date" name="au" class="acid-input" value="{{ request('au') }}"
                           aria-label="Jusqu'au">
                </div>

                <button type="submit" class="acid-btn acid-btn--neutral">
                    <i class="fas fa-filter" aria-hidden="true"></i> Filtrer
                </button>

                @if (request()->hasAny(['recherche', 'auteur', 'action', 'du', 'au']))
                    <a href="{{ route('administration.journal.index') }}" class="acid-btn acid-btn--ghost">
                        <i class="fas fa-rotate-left" aria-hidden="true"></i> Réinitialiser
                    </a>
                @endif
            </form>
        </div>

        <div class="acid-table-wrap">
            <table class="acid-table acid-table--tiles acid-table--compact">
                <thead>
                    <tr>
                        <th scope="col">Quand</th>
                        <th scope="col">Auteur</th>
                        <th scope="col">Action</th>
                        <th scope="col">Objet</th>
                        <th scope="col" class="acid-hide-md">Origine</th>
                        <th scope="col" class="acid-table__actions">Détail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entrees as $entree)
                        <tr>
                            <td data-label="Quand" data-cell="meta">
                                <span class="acid-table__primary acid-num">
                                    {{ $entree->created_at?->format('d/m/Y H:i') }}
                                </span>
                                <span class="acid-table__meta" style="display:block;">
                                    {{ $entree->created_at?->diffForHumans() }}
                                </span>
                            </td>

                            <td data-label="Auteur" data-cell="identity">
                                <div class="acid-identity">
                                    <span class="acid-avatar acid-avatar--sm acid-avatar--navy" aria-hidden="true">
                                        {{ mb_substr($entree->auteur, 0, 1) }}
                                    </span>
                                    <span class="acid-identity__text">
                                        <strong>{{ $entree->auteur }}</strong>
                                        <span>{{ $entree->user_role ?: '—' }}</span>
                                    </span>
                                </div>
                            </td>

                            <td data-label="Action" data-cell="meta">
                                @php
                                    $ton = match (true) {
                                        str_starts_with($entree->action, 'auth.echec') => 'coral',
                                        str_starts_with($entree->action, 'suppression') => 'coral',
                                        str_starts_with($entree->action, 'creation') => 'teal',
                                        str_starts_with($entree->action, 'photo') => 'sky',
                                        default => 'navy',
                                    };
                                @endphp
                                <span class="acid-badge acid-badge--{{ $ton }}">
                                    {{ \App\Services\AuditLogger::libelleAction($entree->action) }}
                                </span>
                            </td>

                            <td data-label="Objet" data-cell="meta">
                                <span class="acid-table__primary">{{ $entree->sujet_libelle ?: '—' }}</span>
                                @if ($entree->sujet_type)
                                    <span class="acid-table__meta" style="display:block;">
                                        {{ class_basename($entree->sujet_type) }}
                                    </span>
                                @endif
                            </td>

                            <td data-label="Origine" data-cell="meta" class="acid-hide-md">
                                <span class="acid-num">{{ $entree->ip ?: '—' }}</span>
                            </td>

                            <td data-label="Détail" data-cell="actions" class="acid-table__actions">
                                <div class="acid-btn-group" style="justify-content:flex-end;">
                                    <a href="{{ route('administration.journal.show', $entree) }}"
                                       class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon"
                                       title="Voir le détail" aria-label="Voir le détail de cette entrée">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="acid-empty">
                                    <span class="acid-empty__icon" aria-hidden="true">
                                        <i class="fas fa-clipboard-list"></i>
                                    </span>
                                    <h3 class="acid-empty__title">Aucune entrée</h3>
                                    <p class="acid-empty__text">
                                        Aucune activité ne correspond à ces critères.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($entrees->hasPages())
            <div class="acid-card__footer">
                {{ $entrees->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
