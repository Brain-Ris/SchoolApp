<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Eleve;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // On charge les élèves avec leurs classes et leurs paiements pour les calculs
        $eleves = Eleve::with(['classe', 'paiements'])->get();
        $trimestre = $request->input('trimestre', 1);

        //Statistiques globales
        $totalEleves  = Eleve::count();
        $totalClasses = Classe::count();
        $totalEncaisse = DB::table('paiements')->sum('montant') ?? 0;

        $touteLesClasses = Classe::all();
        $totalAttendu=0;

        foreach ($touteLesClasses as $classe){
            $nbEleve=DB::table('eleves')->where('classe_id',$classe->id)->count();
            $totalAttendu = $totalAttendu + ($classe->frais*$nbEleve);
        }


        // Taux de réussite global
        $elevesAvecMoyenne = 0;
        $elevesAdmis       = 0;
        $tousLesEleves = Eleve::with(['notes.matiere', 'classe.matieres'])->get();

        foreach ($tousLesEleves as $eleve) {
            $moyenne = $eleve->moyenneTrimestrielle($trimestre);
            if ($moyenne !== null) {
                $elevesAvecMoyenne++;
                if ($moyenne >= 5) {
                    $elevesAdmis++;
                }
            }
        }

        $tauxReussite = $elevesAvecMoyenne > 0
            ? round(($elevesAdmis / $elevesAvecMoyenne) * 100, 1)
            : 0;

        //  Classement par classe 
        // On charge les classes avec élèves + photos + notes + matières
        $classes = Classe::with([
            'eleves',
            'eleves.notes',
            'eleves.notes.matiere',
            'matieres', // Pour les barèmes (calcul de moyenne)
        ])->get();

        // Pour chaque classe, trier les élèves par moyenne décroissante
        foreach ($classes as $classe) {
            $elevesTriés = $classe->eleves->sortByDesc(function ($eleve) use ($trimestre) {
                return $eleve->moyenneTrimestrielle($trimestre) ?? -1;
            })->values(); // .values() remet les index à 0 → rang 1, 2, 3...

            $classe->setRelation('eleves', $elevesTriés);
        }

        return view('dashboard', compact(
            'totalEleves',
            'totalClasses',
            'tauxReussite',
            'totalEncaisse',
            'totalAttendu',
            'trimestre',
            'classes',
            'eleves',
        ));
    }
}
