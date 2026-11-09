<p style="margin:0 0 var(--acid-space-4);color:var(--acid-text-muted);">
    La suppression du compte efface definitivement toutes les donnees associees.
    Pensez a exporter au prealable les documents que vous souhaitez conserver.
</p>

<button type="button" class="acid-btn acid-btn--coral"
        data-bs-toggle="modal" data-bs-target="#modal-confirm-user-deletion">
    <i class="fas fa-trash-can" aria-hidden="true"></i>
    Supprimer mon compte
</button>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" maxWidth="md">
    <form method="post" action="{{ route('profile.destroy') }}">
        @csrf
        @method('delete')

        <div class="modal-header">
            <span class="acid-modal-icon acid-modal-icon--coral" aria-hidden="true">
                <i class="fas fa-triangle-exclamation"></i>
            </span>
            <div>
                <h2 class="modal-title" id="modal-confirm-user-deletion-label">
                    Supprimer definitivement ce compte ?
                </h2>
                <p style="margin:.25rem 0 0;font-size:var(--acid-text-sm);color:var(--acid-text-muted);">
                    Cette action est irreversible. Saisissez votre mot de passe pour confirmer.
                </p>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"
                    aria-label="Fermer"></button>
        </div>

        <div class="modal-body">
            <div class="acid-field" style="margin:0;">
                <x-input-label for="delete_password" value="Mot de passe" class="acid-label--required" />
                <x-text-input id="delete_password" name="password" type="password"
                              class="{{ $errors->userDeletion->get('password') ? 'is-invalid' : '' }}"
                              placeholder="Votre mot de passe" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="acid-btn acid-btn--neutral" data-bs-dismiss="modal">
                Annuler
            </button>
            <x-danger-button>
                <i class="fas fa-trash-can" aria-hidden="true"></i>
                Supprimer definitivement
            </x-danger-button>
        </div>
    </form>
</x-modal>
