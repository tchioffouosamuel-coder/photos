<x-app-layout>
    <div class="acid-breadcrumb acid-no-print" aria-label="Fil d'Ariane">
        <a href="{{ route('administration.utilisateurs.index') }}">Comptes</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page">Rôles</span>
    </div>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Administration</span>
            <h1 class="acid-page-header__title">Rôles et permissions</h1>
            <p class="acid-page-header__lead">
                Ce que chaque rôle a le droit de faire. Toute modification est consignée au journal.
            </p>
        </div>
    </div>

    <div class="acid-alert acid-alert--navy" style="margin-block-end:var(--acid-space-5);">
        <i class="fas fa-circle-info acid-alert__icon" aria-hidden="true"></i>
        <div class="acid-alert__body">
            <p class="acid-alert__title">Permissions et périmètre</p>
            <p style="margin:0;">
                Une permission dit <em>ce que</em> le rôle peut faire ; le périmètre dit
                <em>sur quelles classes</em>. L'opérateur photo est limité aux classes qui lui
                sont assignées sur sa fiche de compte.
            </p>
        </div>
    </div>

    <div class="acid-stack">
        @foreach ($roles as $role)
            @php $detenues = $role->permissions->pluck('code')->all(); @endphp

            <section class="acid-card">
                <div class="acid-card__header">
                    <div>
                        <h2 class="acid-card__title">
                            {{ $role->nom }}
                            @if ($role->estAdministrateur())
                                <span class="acid-badge acid-badge--solid-navy">verrouillé</span>
                            @endif
                        </h2>
                        <p class="acid-card__subtitle">{{ $role->description }}</p>
                    </div>
                    <div class="acid-card__actions">
                        <span class="acid-badge acid-badge--navy">
                            {{ $role->users_count }} compte{{ $role->users_count > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                <div class="acid-card__body">
                    @if ($role->estAdministrateur())
                        <p class="acid-hint" style="margin:0;">
                            L'administrateur dispose de toutes les permissions et n'est pas
                            modifiable : c'est le recours si une dotation devient incohérente.
                        </p>
                    @else
                        <form method="POST" action="{{ route('administration.roles.update', $role) }}">
                            @csrf
                            @method('PUT')

                            @foreach ($groupes as $groupe => $permissions)
                                <fieldset style="border:0;padding:0;margin:0 0 var(--acid-space-5);">
                                    <legend class="acid-section-title"
                                            style="font-size:var(--acid-text-sm);margin-block-end:var(--acid-space-2);">
                                        {{ $groupe }}
                                    </legend>

                                    <div class="acid-form-grid acid-form-grid--2">
                                        @foreach ($permissions as $code => $libelle)
                                            <label class="acid-check" style="margin:0;">
                                                <input type="checkbox" name="permissions[]"
                                                       value="{{ $code }}"
                                                       @checked(in_array($code, $detenues, true))>
                                                <span class="acid-check__text">
                                                    <strong>{{ $libelle }}</strong>
                                                    <span>{{ $code }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach

                            <div class="acid-form-actions">
                                <div class="acid-form-actions__end">
                                    <button type="submit" class="acid-btn acid-btn--primary">
                                        <i class="fas fa-floppy-disk" aria-hidden="true"></i>
                                        Enregistrer « {{ $role->nom }} »
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
</x-app-layout>
