<?php

namespace App\Models;

use App\Support\Roles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['code', 'nom', 'description', 'est_systeme'];

    protected $casts = ['est_systeme' => 'boolean'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function estAdministrateur(): bool
    {
        return $this->code === Roles::ADMINISTRATEUR;
    }

    /**
     * Ce role ne voit-il que les classes qui lui sont assignees ?
     */
    public function estPorteParClasse(): bool
    {
        return in_array($this->code, Roles::portesParClasse(), true);
    }

    /**
     * @return list<string>
     */
    public function codesPermissions(): array
    {
        return $this->permissions->pluck('code')->all();
    }
}
