<x-app-layout>

    <div class="acid-page-header">
        <div class="acid-page-header__text">
            <span class="acid-page-header__eyebrow">Compte</span>
            <h1 class="acid-page-header__title">Mon profil</h1>
            <p class="acid-page-header__lead">
                Gerez vos informations personnelles, votre mot de passe et votre acces
                a la plateforme.
            </p>
        </div>
    </div>

    <div class="acid-stack" style="max-width:52rem;">

        <section class="acid-card">
            <div class="acid-card__header">
                <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-user-pen"></i></span>
                <div>
                    <h2 class="acid-card__title">Informations du profil</h2>
                    <p class="acid-card__subtitle">Nom et adresse e-mail associes a votre compte.</p>
                </div>
            </div>
            <div class="acid-card__body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </section>

        <section class="acid-card">
            <div class="acid-card__header">
                <span class="acid-stat__icon" aria-hidden="true"><i class="fas fa-key"></i></span>
                <div>
                    <h2 class="acid-card__title">Mot de passe</h2>
                    <p class="acid-card__subtitle">Utilisez un mot de passe long et unique.</p>
                </div>
            </div>
            <div class="acid-card__body">
                @include('profile.partials.update-password-form')
            </div>
        </section>

        <section class="acid-card" style="border-color:var(--acid-coral-100);">
            <div class="acid-card__header">
                <span class="acid-modal-icon acid-modal-icon--coral" aria-hidden="true">
                    <i class="fas fa-triangle-exclamation"></i>
                </span>
                <div>
                    <h2 class="acid-card__title">Supprimer le compte</h2>
                    <p class="acid-card__subtitle">Cette action est definitive et irreversible.</p>
                </div>
            </div>
            <div class="acid-card__body">
                @include('profile.partials.delete-user-form')
            </div>
        </section>
    </div>
</x-app-layout>
