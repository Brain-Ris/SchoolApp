<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
