<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Eleve extends Model
{
    use SoftDeletes;

    protected $fillable = ['nom', 'prenom', 'genre', 'photo', 'classe_id'];
    protected $dates = ['deleted_at'];

    // RELATIONS 

    // Un élève appartient à une classe
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    // Un élève peut avoir plusieurs paiements
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    // Un élève peut avoir plusieurs notes (une par matière par trimestre)
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    //  MÉTHODES DE CALCUL 

    /**
     * Calcul du total payé par l'élève
     */
    public function totalPaye()
    {
        return $this->paiements()->sum('montant');
    }

    /**
     * Calcul du reste à payer
     */
    public function resteAPayer()
    {
        $fraisClasse = $this->classe->frais ?? 0;
        return $fraisClasse - $this->totalPaye();
    }

    /**
     * CALCUL DE LA MOYENNE TRIMESTRIELLE SUR 10
     *
     * Logique :
     * 1. On récupère toutes les notes de l'élève pour ce trimestre
     * 2. Pour chaque note, on récupère le barème de la matière dans
     *    la classe de l'élève (ex : Maths barème 20, Sport barème 10)
     * 3. On ramène chaque note sur 10 : note_sur_10 = (valeur / bareme) * 10
     * 4. On fait la moyenne de toutes ces notes ramenées sur 10
     *
     * @param int $trimestre  Le trimestre (1, 2 ou 3)
     * @return float|null     La moyenne sur 10, ou null si aucune note
     */
    public function moyenneTrimestrielle($trimestre = 1)
    {
        // Récupérer toutes les notes de cet élève pour ce trimestre
        // avec la matière associée
        $notes = $this->notes()
                      ->with('matiere')
                      ->where('trimestre', $trimestre)
                      ->get();

        // Si aucune note, on retourne null
        if ($notes->isEmpty()) {
            return null;
        }

        // Récupérer les matières de la classe avec leur barème
        // La classe doit être chargée avec ses matières (et le pivot "bareme")
        $classe = $this->classe()->with('matieres')->first();

        if (!$classe) {
            return null;
        }

        // Créer un tableau associatif : matiere_id => bareme
        // Exemple : [1 => 20, 2 => 10, 3 => 20]
        $baremes = [];
        foreach ($classe->matieres as $matiere) {
            // Le barème est dans la colonne "bareme" de la table pivot classe_matiere
            $baremes[$matiere->id] = $matiere->pivot->bareme ?? 10;
        }

        $totalSur10 = 0;  // Somme des notes ramenées sur 10
        $nbMatieres = 0;  // Nombre de matières comptées

        foreach ($notes as $note) {
            // Si la note n'a pas de valeur, on passe
            if ($note->valeurs === null) {
                $note->valeurs=0;
            }

            // Récupérer le barème de cette matière pour cette classe
            $bareme = $baremes[$note->matiere_id] ?? 10; // 10 par défaut si inconnu

            // Ramener la note sur 10
            // Exemple : 15/20 → (15/20)*10 = 7.5/10
            $noteSur10 = ($note->valeurs / $bareme) * 10;

            $totalSur10 += $noteSur10;
            $nbMatieres++;
        }

        if ($nbMatieres === 0) {
            return null;
        }

        // Retourner la moyenne arrondie à 2 décimales
        return round($totalSur10 / $nbMatieres, 2);
    }
}
