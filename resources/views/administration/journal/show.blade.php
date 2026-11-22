<x-app-layout>
    <div class="acid-breadcrumb acid-no-print" aria-label="Fil d'Ariane">
        <a href="{{ route('administration.journal.index') }}">Journal</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page">Entrée n° {{ $entree->id }}</span>
    </div>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">
                {{ $entree->created_at?->format('d/m/Y à H:i:s') }}
            </span>
            <h1 class="acid-page-header__title">
                {{ \App\Services\AuditLogger::libelleAction($entree->action) }}
            </h1>
            <p class="acid-page-header__lead">
                {{ $entree->sujet_libelle ?: 'Objet non précisé' }}
            </p>
        </div>

        <div class="acid-page-header__actions">
            <a href="{{ route('administration.journal.index') }}" class="acid-btn acid-btn--neutral">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Retour au journal
            </a>
        </div>
    </div>

    <div class="acid-grid" style="grid-template-columns:minmax(0,2fr) minmax(280px,1fr);align-items:start;">

        <div class="acid-stack">

            {{-- Ce qui a changé --}}
            <section class="acid-card">
                <div class="acid-card__header">
                    <div>
                        <h2 class="acid-card__title">Ce qui a changé</h2>
                        <p class="acid-card__subtitle">
                            Valeurs avant et après, champ par champ.
                        </p>
                    </div>
                </div>

                <div class="acid-card__body">
                    @if ($differences === [])
                        <p class="acid-hint" style="margin:0;">
                            Cette action ne porte sur aucune valeur modifiable
                            (connexion, consultation, production de document).
                        </p>
                    @else
                        <div class="acid-table-wrap">
                            <table class="acid-table acid-table--compact">
                                <thead>
                                    <tr>
                                        <th scope="col">Champ</th>
                                        <th scope="col">Avant</th>
                                        <th scope="col">Après</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($differences as $champ => $ecart)
                                        <tr>
                                            <td data-label="Champ">
                                                <span class="acid-table__primary">{{ $champ }}</span>
                                            </td>
                                            <td data-label="Avant">
                                                <span style="color:var(--acid-coral-700);">
                                                    {{ $ecart['avant'] === null ? '—' : (is_scalar($ecart['avant']) ? $ecart['avant'] : json_encode($ecart['avant'], JSON_UNESCAPED_UNICODE)) }}
                                                </span>
                                            </td>
                                            <td data-label="Après">
                                                <span style="color:var(--acid-teal-700);">
                                                    {{ $ecart['apres'] === null ? '—' : (is_scalar($ecart['apres']) ? $ecart['apres'] : json_encode($ecart['apres'], JSON_UNESCAPED_UNICODE)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Histoire de l'objet --}}
            @if ($voisines->isNotEmpty())
                <section class="acid-card">
                    <div class="acid-card__header">
                        <div>
                            <h2 class="acid-card__title">Histoire de cet objet</h2>
                            <p class="acid-card__subtitle">
                                Les {{ $voisines->count() }} dernières actions le concernant.
                            </p>
                        </div>
                    </div>

                    <div class="acid-table-wrap">
                        <table class="acid-table acid-table--compact">
                            <thead>
                                <tr>
                                    <th scope="col">Quand</th>
                                    <th scope="col">Auteur</th>
                                    <th scope="col">Action</th>
                                    <th scope="col" class="acid-table__actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($voisines as $voisine)
                                    <tr>
                                        <td data-label="Quand">
                                            <span class="acid-num">{{ $voisine->created_at?->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td data-label="Auteur">{{ $voisine->auteur }}</td>
                                        <td data-label="Action">
                                            {{ \App\Services\AuditLogger::libelleAction($voisine->action) }}
                                        </td>
                                        <td class="acid-table__actions">
                                            <a href="{{ route('administration.journal.show', $voisine) }}"
                                               class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon"
                                               aria-label="Voir cette entrée">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>

        {{-- Contexte --}}
        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Contexte</h2>
                </div>
            </div>

            <div class="acid-card__body">
                <dl style="margin:0;display:grid;gap:var(--acid-space-4);">
                    <div>
                        <dt class="acid-stat__label">Auteur</dt>
                        <dd style="margin:0;">
                            <strong>{{ $entree->auteur }}</strong>
                            @if (! $entree->user)
                                <span class="acid-badge acid-badge--coral">compte supprimé</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="acid-stat__label">Rôle au moment de l'action</dt>
                        <dd style="margin:0;">{{ $entree->user_role ?: '—' }}</dd>
                    </div>

                    <div>
                        <dt class="acid-stat__label">Établissement</dt>
                        <dd style="margin:0;">
                            {{ $entree->etablissement?->nom ?? 'Aucun en particulier' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="acid-stat__label">Horodatage</dt>
                        <dd style="margin:0;" class="acid-num">
                            {{ $entree->created_at?->format('d/m/Y H:i:s') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="acid-stat__label">Adresse IP</dt>
                        <dd style="margin:0;" class="acid-num">{{ $entree->ip ?: '—' }}</dd>
                    </div>

                    <div>
                        <dt class="acid-stat__label">Appareil</dt>
                        <dd style="margin:0;font-size:var(--acid-text-sm);color:var(--acid-text-muted);
                                   word-break:break-word;">
                            {{ $entree->user_agent ?: '—' }}
                        </dd>
                    </div>

                    @if ($entree->sujet_type)
                        <div>
                            <dt class="acid-stat__label">Type d'objet</dt>
                            <dd style="margin:0;">
                                {{ class_basename($entree->sujet_type) }}
                                @if ($entree->sujet_id)
                                    <span class="acid-num">#{{ $entree->sujet_id }}</span>
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </section>
    </div>
</x-app-layout>
