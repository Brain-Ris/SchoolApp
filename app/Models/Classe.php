<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MODEL CLASSE
 *
 * Changements :
 * - La relation matieres() utilise maintenant "bareme" au lieu de "coefficient"
 *   et récupère aussi le user_id (l'enseignant assigné à cette matière)
 * - Nouvelle méthode enseignant() pour savoir quel prof gère la classe
 */
class Classe extends Model
{
    use SoftDeletes;

    protected $fillable = ['nom', 'frais'];

    // ─── RELATIONS ───────────────────────────────────────────────────────

    // Une classe a plusieurs élèves
    public function eleves()
    {
        return $this->hasMany(Eleve::class);
    }

    /**
     * Une classe a plusieurs matières (relation many-to-many)
     * La table pivot "classe_matiere" contient aussi :
     * - bareme  : note maximum pour cette matière dans cette classe (10 ou 20)
     * - user_id : l'enseignant qui gère la classe
     */
    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'classe_matiere')
                    ->withPivot('bareme', 'user_id')  // On récupère ces colonnes de la table pivot
                    ->withTimestamps();
    }

    /**
     * Récupérer l'enseignant assigné à cette classe
     * (On cherche l'user dont la classe_id correspond à cette classe)
     */
    public function enseignant()
    {
        return $this->hasOne(User::class); // Un prof a une seule classe (classe_id dans users)
    }

    // Suppression automatique des élèves quand on supprime la classe
    protected static function booted()
    {
        static::deleting(function ($classe) {
            $classe->eleves()->delete();
        });
    }
}
