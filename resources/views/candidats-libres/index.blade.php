@php
    $avecPhoto = $candidats->whereNotNull('photo')->count();
    $sansPhoto = max(0, $candidats->count() - $avecPhoto);
@endphp

<x-app-layout>

    <nav aria-label="Fil d'Ariane">
        <ol class="acid-breadcrumb">
            <li><a href="{{ route('classes.index') }}">Classes</a></li>
            <li><a href="{{ route('classes.show', $classe) }}">{{ $classe->nom }}</a></li>
            <li aria-current="page">Candidats libres</li>
        </ol>
    </nav>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">
                {{ $classe->nom }}
                @if ($classe->exam)
                    &middot; {{ $classe->exam }}
                @endif
            </span>
            <h1 class="acid-page-header__title">Candidats libres</h1>
            <p class="acid-page-header__lead">
                Candidats rattaches a cette session d'examen sans etre inscrits dans la classe.
            </p>
        </div>
        <div class="acid-page-header__actions">
            <a href="{{ route('classes.show', $classe) }}" class="acid-btn acid-btn--ghost">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Retour a la classe
            </a>
            <a href="{{ route('candidats-libres.create', $classe) }}" class="acid-btn acid-btn--primary">
                <i class="fas fa-plus" aria-hidden="true"></i>
                Nouveau candidat
            </a>
        </div>
    </div>

    {{-- Indicateurs --}}
    <div class="acid-grid acid-grid--stats" style="margin-block-end:var(--acid-space-5);">
        <article class="acid-stat">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-user-tag"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Candidats</span>
                <span class="acid-stat__value">{{ $candidats->count() }}</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--sky">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-mars"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Garcons</span>
                <span class="acid-stat__value">{{ $candidats->where('sexe', 'M')->count() }}</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--coral">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-venus"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Filles</span>
                <span class="acid-stat__value">{{ $candidats->where('sexe', 'F')->count() }}</span>
            </div>
        </article>

        <article class="acid-stat acid-stat--teal">
            <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-camera-retro"></i></span>
            <div class="acid-stat__body">
                <span class="acid-stat__label">Avec photo</span>
                <span class="acid-stat__value">{{ $avecPhoto }}</span>
                @if ($sansPhoto > 0)
                    <span class="acid-stat__hint">{{ $sansPhoto }} sans photo</span>
                @endif
            </div>
        </article>
    </div>

    {{-- Production de documents --}}
    <section class="acid-card" style="margin-block-end:var(--acid-space-5);">
        <div class="acid-card__header">
            <span class="acid-stat__icon" aria-hidden="true"
                  style="background:var(--acid-sky-50);color:var(--acid-sky-600);">
                <i class="fas fa-print"></i>
            </span>
            <div>
                <h2 class="acid-card__title">Documents a generer</h2>
                <p class="acid-card__subtitle">Photos labellisees pour les fiches d'inscription</p>
            </div>
        </div>
        <div class="acid-card__body acid-card__body--tight">
            @if ($avecPhoto === 0)
                <div class="acid-alert acid-alert--warning">
                    <i class="fas fa-triangle-exclamation acid-alert__icon" aria-hidden="true"></i>
                    <div class="acid-alert__body">
                        <p class="acid-alert__title">Aucune photo disponible</p>
                        <p style="margin:0;font-size:var(--acid-text-sm);">
                            Ajoutez au moins une photo de candidat pour pouvoir generer les documents.
                        </p>
                    </div>
                </div>
            @else
                <div class="acid-btn-group">
                    <a href="{{ route('candidats-libres.photos-4x4', $classe) }}" class="acid-btn acid-btn--primary">
                        <i class="fas fa-table-cells" aria-hidden="true"></i>
                        Planche de photos 4x4
                    </a>
                    <a href="{{ route('candidats-libres.photos-pdf', $classe) }}" class="acid-btn acid-btn--neutral">
                        <i class="fas fa-file-pdf" aria-hidden="true"></i>
                        Photos au format PDF
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- Liste --}}
    @if ($candidats->isEmpty())
        <div class="acid-card">
            <div class="acid-empty">
                <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-user-tag"></i></span>
                <h2 class="acid-empty__title">Aucun candidat libre</h2>
                <p class="acid-empty__text">
                    Enregistrez les candidats libres de cette session pour produire
                    leurs photos d'examen.
                </p>
                <div class="acid-empty__actions">
                    <a href="{{ route('candidats-libres.create', $classe) }}" class="acid-btn acid-btn--primary">
                        <i class="fas fa-plus" aria-hidden="true"></i> Ajouter un candidat
                    </a>
                </div>
            </div>
        </div>
    @else
        <section class="acid-card">
            <div class="acid-card__header">
                <div>
                    <h2 class="acid-card__title">Liste des candidats</h2>
                </div>
                <div class="acid-card__actions">
                    <span class="acid-badge acid-badge--navy">{{ $candidats->count() }}</span>
                </div>
            </div>

            <div class="acid-table-wrap">
                <table class="acid-table acid-table--stack">
                    <thead>
                        <tr>
                            <th scope="col">Candidat</th>
                            <th scope="col">Naissance</th>
                            <th scope="col">Lieu</th>
                            <th scope="col">Sexe</th>
                            <th scope="col" class="acid-table__actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($candidats as $candidat)
                            <tr data-acid-searchable="{{ $candidat->nom }} {{ $candidat->lieu_naissance }}">

                                <td data-label="Candidat">
                                    <div class="acid-identity">
                                        <span class="acid-avatar acid-avatar--navy">
                                            @if ($candidat->photo)
                                                <img src="{{ $candidat->photo_url }}" alt="">
                                            @else
                                                <i class="fas fa-user" aria-hidden="true"></i>
                                            @endif
                                        </span>
                                        <span class="acid-identity__text">
                                            <strong>{{ strtoupper($candidat->nom) }}</strong>
                                            <span>Candidat libre</span>
                                        </span>
                                    </div>
                                </td>

                                <td data-label="Naissance" class="acid-num">
                                    {{ $candidat->date_naissance ? $candidat->date_naissance->format('d/m/Y') : '—' }}
                                </td>

                                <td data-label="Lieu">
                                    {{ $candidat->lieu_naissance ? strtoupper($candidat->lieu_naissance) : '—' }}
                                </td>

                                <td data-label="Sexe">
                                    @if ($candidat->sexe === 'M')
                                        <span class="acid-badge acid-badge--sky">
                                            <i class="fas fa-mars" aria-hidden="true"></i> Masculin
                                        </span>
                                    @else
                                        <span class="acid-badge acid-badge--coral">
                                            <i class="fas fa-venus" aria-hidden="true"></i> Feminin
                                        </span>
                                    @endif
                                </td>

                                <td data-label="Actions" class="acid-table__actions">
                                    <div class="acid-btn-group" style="justify-content:flex-end;">
                                        <a href="{{ route('candidats-libres.edit', [$classe, $candidat]) }}"
                                           class="acid-btn acid-btn--neutral acid-btn--sm acid-btn--icon"
                                           title="Modifier le candidat" aria-label="Modifier le candidat">
                                            <i class="fas fa-pen" aria-hidden="true"></i>
                                        </a>

                                        <form action="{{ route('candidats-libres.destroy', [$classe, $candidat]) }}"
                                              method="POST" style="display:inline;"
                                              data-acid-confirm="Supprimer le candidat {{ $candidat->nom }} ?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="acid-btn acid-btn--outline-coral acid-btn--sm acid-btn--icon"
                                                    title="Supprimer le candidat" aria-label="Supprimer le candidat">
                                                <i class="fas fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div data-acid-search-empty hidden>
                <div class="acid-empty">
                    <span class="acid-empty__icon" aria-hidden="true"><i class="fas fa-magnifying-glass"></i></span>
                    <h3 class="acid-empty__title">Aucun candidat ne correspond</h3>
                    <p class="acid-empty__text">Modifiez votre recherche dans la barre superieure.</p>
                </div>
            </div>
        </section>
    @endif
</x-app-layout>
