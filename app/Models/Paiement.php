<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paiement extends Model
{
    use SoftDeletes;
    protected $fillable = ['montant', 'eleve_id'];

    // Un paiement appartient à un élève
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }
}