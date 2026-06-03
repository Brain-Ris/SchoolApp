<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Note;

class Matiere extends Model
{
    use SoftDeletes;

    protected $fillable = ['nom'];

    public function classes()
    {
        return $this->belongsToMany(Classe::class)->withPivot('coefficient');
    }

    //permet de suppimer les notes quand la matières est supprimer
    protected static function booted()
    {
        static::deleting(function ($matiere) {

            // Supprimer toutes les notes liées
            Note::where('matiere_id', $matiere->id)->delete();

            // Supprimer les liaisons classe_matiere
            $matiere->classes()->detach();
        });
    }
}