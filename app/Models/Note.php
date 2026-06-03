<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MODEL NOTE
 *
 * Représente UNE note d'UN élève pour UNE matière à UN trimestre.
 *
 * Structure de la table :
 * - eleve_id   → l'élève
 * - matiere_id → la matière (ex: Maths, Français...)
 * - trimestre  → 1, 2 ou 3
 * - valeur     → la note obtenue (ex: 14.5)
 *
 * Le barème (note maximale : 10 ou 20) est stocké dans la table pivot
 * classe_matiere. On le récupère via la relation classe->matieres.
 */
class Note extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'eleve_id',
        'matiere_id',
        'trimestre',
        'valeurs',
    ];

    // Une note appartient à un élève
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    // Une note appartient à une matière
    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }
}
