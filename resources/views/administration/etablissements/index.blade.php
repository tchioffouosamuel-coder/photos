<x-app-layout>
    <div class="acid-breadcrumb acid-no-print" aria-label="Fil d'Ariane">
        <a href="{{ route('dashboard') }}">Tableau de bord</a>
        <span aria-hidden="true">&rsaquo;</span>
        <span aria-current="page">Établissements</span>
    </div>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Administration</span>
            <h1 class="acid-page-header__title">Établissements</h1>
            <p class="acid-page-header__lead">
                Chaque établissement est cloisonné : ses classes, ses élèves et ses photos
                ne sont visibles ni depuis un autre, ni par un compte qui ne lui est pas rattaché.
            </p>
        </div>

        @if ($peutCreer)
            <div class="acid-page-header__actions">
                <a href="{{ route('administration.etablissements.create') }}" class="acid-btn acid-btn--primary">
                    <i class="fas fa-plus" aria-hidden="true"></i> Nouvel établissement
                </a>
            </div>
        @endif
    </div>

    @unless ($peutCreer)
        <div class="acid-alert acid-alert--navy" style="margin-block-end:var(--acid-space-5);">
            <i class="fas fa-circle-info acid-alert__icon" aria-hidden="true"></i>
            <div class="acid-alert__body">
                <p class="acid-alert__title">Vous administrez votre établissement</p>
                <p style="margin:0;">
                    Créer ou supprimer un établissement relève de l'administration du réseau,
                    pas de la direction d'une école. Vous pouvez en revanche modifier la fiche
                    du vôtre.
                </p>
            </div>
        </div>
    @endunless

    <section class="acid-card">
        <div class="acid-table-wrap">
            <table class="acid-table acid-table--tiles">
                <thead>
                    <tr>
                        <th scope="col">Établissement</th>
                        <th scope="col">Code</th>
                        <th scope="col">Classes</th>
                        <th scope="col">Élèves</th>
                        <th scope="col" class="acid-hide-md">Comptes</th>
                        <th scope="col" class="acid-table__actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($etablissements as $etablissement)
                        @php
                            $compte = $effectifs[$etablissement->id] ?? ['classes' => 0, 'eleves' => 0, 'comptes' => 0];
                            $estActif = $idActif === (int) $etablissement->id;
                        @endphp
                        <tr>
                            <td data-label="Établissement" data-cell="identity">
                                <div class="acid-identity">
                                    <span class="acid-avatar acid-avatar--navy" aria-hidden="true">
                                        {{ $etablissement->initiales }}
                                    </span>
                                    <span class="acid-identity__text">
                                        <strong>{{ $etablissement->nom }}</strong>
                                        <span>
                                            {{ $etablissement->ville ?: 'Ville non précisée' }}
                                            @if ($estActif)
                                                &middot; <span class="acid-badge acid-badge--teal">en cours</span>
                                            @endif
                                        </span>
                                    </span>
                                </div>
                            </td>

                            <td data-label="Code" data-cell="meta">
                                <span class="acid-num">{{ $etablissement->code }}</span>
                            </td>

                            <td data-label="Classes" data-cell="meta">
                                <span class="acid-num">{{ $compte['classes'] }}</span>
                            </td>

                            <td data-label="Élèves" data-cell="meta">
                                <span class="acid-num">{{ $compte['eleves'] }}</span>
                            </td>

                            <td data-label="Comptes" data-cell="meta" class="acid-hide-md">
                                <span class="acid-num">{{ $compte['comptes'] }}</span>
                            </td>

                            <td data-label="Actions" data-cell="actions" class="acid-table__actions">
                                <div class="acid-btn-group" style="justify-content:flex-end;">

                                    @can('basculer', $etablissement)
                                        @unless ($estActif)
                                            <form method="POST"
                                                  action="{{ route('administration.etablissements.basculer') }}">
                                                @csrf
                                                <input type="hidden" name="etablissement_id"
                                                       value="{{ $etablissement->id }}">
                                                <button type="submit"
                                                        class="acid-btn acid-btn--neutral acid-btn--sm"
                                                        title="Travailler sur cet établissement">
                                                    <i class="fas fa-right-to-bracket" aria-hidden="true"></i>
                                                    Y travailler
                                                </button>
                                            </form>
                                        @endunless
                                    @endcan

                                    @can('update', $etablissement)
                                        <a href="{{ route('administration.etablissements.edit', $etablissement) }}"
                                           class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon"
                                           title="Modifier la fiche" aria-label="Modifier la fiche">
                                            <i class="fas fa-pen" aria-hidden="true"></i>
                                        </a>
                                    @endcan

                                    @can('delete', $etablissement)
                                        <form method="POST"
                                              action="{{ route('administration.etablissements.destroy', $etablissement) }}"
                                              data-acid-confirm="Supprimer {{ $etablissement->nom }} ?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="acid-btn acid-btn--danger acid-btn--sm acid-btn--icon"
                                                    title="Supprimer" aria-label="Supprimer cet établissement">
                                                <i class="fas fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="acid-empty">
                                    <span class="acid-empty__icon" aria-hidden="true">
                                        <i class="fas fa-school"></i>
                                    </span>
                                    <h3 class="acid-empty__title">Aucun établissement</h3>
                                    <p class="acid-empty__text">
                                        Aucun établissement ne vous est accessible.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="acid-alert acid-alert--navy" style="margin-block-start:var(--acid-space-5);">
        <i class="fas fa-circle-question acid-alert__icon" aria-hidden="true"></i>
        <div class="acid-alert__body">
            <p class="acid-alert__title">Pourquoi un établissement refuse-t-il d'être supprimé ?</p>
            <p style="margin:0;">
                Parce qu'il contient encore des classes, des élèves ou des comptes. Supprimer
                un établissement plein emporterait ses élèves et leurs photos sans retour
                possible : transférez ou supprimez son contenu d'abord. Le dernier
                établissement ne se supprime pas non plus — l'application n'aurait plus où
                ranger la classe suivante.
            </p>
        </div>
    </div>
</x-app-layout>
