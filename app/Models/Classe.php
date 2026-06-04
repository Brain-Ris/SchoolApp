<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classe extends Model
{
    use SoftDeletes;

    protected $fillable = ['nom', 'frais'];

    //  RELATIONS 

    // Une classe a plusieurs élèves
    public function eleves()
    {
        return $this->hasMany(Eleve::class);
    }

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
