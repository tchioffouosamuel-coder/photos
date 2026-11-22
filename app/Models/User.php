<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'etablissement_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ====================================================================
     | Relations
     * ================================================================== */

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Etablissement auquel le compte est rattache.
     *
     * NULL signifie portee globale : le compte a acces a tous les
     * etablissements. C'est la situation des administrateurs du reseau, et
     * celle des administrateurs existants au moment du passage au
     * multi-etablissement.
     */
    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    /**
     * Classes confiees a ce compte (operateur photo).
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classe::class, 'classe_user')
            ->withTimestamps()
            ->using(\Illuminate\Database\Eloquent\Relations\Pivot::class);
    }

    /* ====================================================================
     | Droits
     * ================================================================== */

    public function estAdministrateur(): bool
    {
        return $this->role?->estAdministrateur() ?? false;
    }

    public function aLeRole(string ...$codes): bool
    {
        return in_array($this->role?->code, $codes, true);
    }

    /**
     * Le compte possede-t-il cette permission ?
     *
     * L'administrateur passe partout : c'est le filet qui garantit qu'une
     * erreur de dotation ne rend jamais l'application inadministrable.
     */
    public function aLaPermission(string $code): bool
    {
        if ($this->estAdministrateur()) {
            return true;
        }

        return in_array($code, $this->codesPermissions(), true);
    }

    /**
     * @return list<string>
     */
    public function codesPermissions(): array
    {
        if (! $this->role) {
            return [];
        }

        /* Charge une seule fois par instance, donc une fois par requete. */
        $this->role->loadMissing('permissions');

        return $this->role->permissions->pluck('code')->all();
    }

    /* ====================================================================
     | Portee par etablissement
     * ================================================================== */

    /**
     * Le compte voit-il tous les etablissements ?
     *
     * Volontairement independant du role : la portee est une propriete du
     * COMPTE, pas de son niveau d'acces. Un administrateur rattache a un
     * etablissement administre cet etablissement et lui seul ; c'est ce qui
     * permet de confier l'administration d'une ecole a quelqu'un sans lui
     * ouvrir les donnees des autres.
     */
    public function aPorteeGlobale(): bool
    {
        return $this->etablissement_id === null;
    }

    /**
     * Le compte a-t-il acces a cet etablissement ?
     */
    public function peutAccederEtablissement(int|Etablissement|null $etablissement): bool
    {
        if ($this->aPorteeGlobale()) {
            return true;
        }

        if ($etablissement === null) {
            return false;
        }

        $id = $etablissement instanceof Etablissement
            ? (int) $etablissement->getKey()
            : (int) $etablissement;

        return (int) $this->etablissement_id === $id;
    }

    public function getEtablissementLibelleAttribute(): string
    {
        return $this->aPorteeGlobale()
            ? 'Tous les établissements'
            : ($this->etablissement?->nom ?? 'Établissement supprimé');
    }

    /* ====================================================================
     | Portee par classe
     * ================================================================== */

    /**
     * Ce compte est-il limite a certaines classes ?
     *
     * Un role porte par classe SANS aucune assignation voit toutes les
     * classes. C'est un choix delibere : creer un operateur et oublier de lui
     * assigner ses classes produirait sinon un compte muet, sans message
     * d'erreur comprehensible. L'interface affiche clairement le perimetre.
     */
    public function estLimiteAuxClassesAssignees(): bool
    {
        if ($this->estAdministrateur() || ! $this->role) {
            return false;
        }

        if (! $this->role->estPorteParClasse()) {
            return false;
        }

        return $this->classes()->exists();
    }

    /**
     * Ce compte a-t-il acces a cette classe ?
     *
     * L'etablissement est verifie en PREMIER, et separement de la portee
     * globale de requete.
     *
     | Ce n'est pas une redondance inutile. En pratique, la portee globale
     | fait qu'une classe d'un autre etablissement n'est meme pas trouvee par
     | la resolution de route : le refus arrive avant la Policy. Mais faire
     | reposer tout le cloisonnement inter-etablissements sur ce seul
     | mecanisme signifie qu'un withoutGlobalScope() ecrit ailleurs, pour une
     | bonne raison, rend soudain accessible ce qui ne doit pas l'etre. La
     | Policy doit pouvoir repondre juste meme quand on lui presente un objet
     | qu'elle n'aurait pas du voir.
     */
    public function peutAccederClasse(Classe $classe): bool
    {
        if (! $this->peutAccederEtablissement($classe->etablissement_id)) {
            return false;
        }

        if (! $this->estLimiteAuxClassesAssignees()) {
            return true;
        }

        return $this->classes()->whereKey($classe->getKey())->exists();
    }

    /**
     * Identifiants des classes visibles, ou null si toutes le sont.
     *
     * @return list<int>|null
     */
    public function classesVisibles(): ?array
    {
        if (! $this->estLimiteAuxClassesAssignees()) {
            return null;
        }

        return $this->classes()->pluck('classes.id')->all();
    }

    /* ====================================================================
     | Presentation
     * ================================================================== */

    public function getRoleLibelleAttribute(): string
    {
        return $this->role?->nom ?? 'Sans rôle';
    }

    public function getInitialeAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->name ?? '?', 0, 1));
    }

    /**
     * Un compte sans role ne peut rien faire : c'est l'etat d'un compte
     * desactive sans etre supprime, afin de preserver ses traces.
     */
    public function estDesactive(): bool
    {
        return $this->role_id === null;
    }

    public static function roleParDefaut(): string
    {
        return Roles::LECTEUR;
    }
}
