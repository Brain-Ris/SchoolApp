<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * MODEL USER
 *
 * Changements :
 * - Ajout de "classe_id" dans $fillable
 *   → Permet de lier un enseignant à une classe directement
 *
 * Comment savoir si un prof est déjà assigné ?
 * → On cherche dans la table users si un user avec role=enseignant
 *   a déjà ce classe_id. Si oui, on ne le propose plus dans le select.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_first_login',
        'classe_id',  // ← NOUVEAU : l'id de la classe assignée à cet enseignant
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─── RELATIONS ───────────────────────────────────────────────────────

    /**
     * Un enseignant appartient à une classe
     * (La colonne classe_id est dans la table users)
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }
}
