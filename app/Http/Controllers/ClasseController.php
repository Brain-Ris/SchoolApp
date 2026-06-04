<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClasseController extends Controller
{
    // INDEX 

    public function index(Request $request)
    {
        $trimestre = $request->input('trimestre', 1);

        // Récupérer toutes les classes avec leurs élèves et matières
        $classes = Classe::with(['eleves', 'matieres', 'enseignant'])->get();

        // Toutes les matières pour le formulaire d'ajout
        $matieres = Matiere::all();

        // Enseignants disponibles = ceux qui n'ont PAS encore de classe assignée
        // (classe_id IS NULL dans la table users)
        $enseignantsDisponibles = User::where('role', 'enseignant')
                                      ->whereNull('classe_id')
                                      ->get();

        // Statistiques globales
        $totalEleves    = Eleve::count();
        $totalEncaisse  = DB::table('paiements')->sum('montant') ?? 0;

        // Calcul du taux de réussite global
        $tousLesEleves     = Eleve::with('notes')->get();
        $elevesAvecMoyenne = 0;
        $elevesAdmis       = 0;

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

        return view('classes.index', compact(
            'classes',
            'matieres',
            'enseignantsDisponibles',
            'totalEleves',
            'tauxReussite',
            'totalEncaisse',
            'trimestre'
        ));
    }

    // STORE

    public function store(Request $request)
    {
        // Seul le gestionnaire peut créer des classes
        if (Auth::user()->role !== 'gestionnaire') {
            return back()->withErrors(['error' => 'Action non autorisée.']);
        }

        $request->validate([
            'nom'          => ['required', 'string', 'max:50',
                               Rule::unique('classes')->whereNull('deleted_at')],
            'frais'        => 'required|integer|min:0',
            'enseignant_id'=> 'nullable|exists:users,id', // Le prof assigné (optionnel)
            'matieres'     => 'nullable|array',
        ], [
            'nom.unique'   => 'Cette classe existe déjà !',
            'nom.required' => 'Le nom de la classe est obligatoire.',
            'frais.required'=> 'Les frais de scolarité sont obligatoires.',
        ]);

        // 1. Créer la classe
        $classe = Classe::create([
            'nom'   => $request->nom,
            'frais' => $request->frais,
        ]);

        // 2. Attacher les matières cochées avec leur barème
        if ($request->has('matieres')) {
            $pivotData = []; // Tableau qui contiendra les données pour la table pivot

            foreach ($request->matieres as $matiereId => $donnees) {
                // On vérifie que la case était cochée
                if (isset($donnees['coche'])) {
                    // Récupérer le barème saisi (10 ou 20), par défaut 10
                    $bareme = intval($donnees['bareme'] ?? 10);

                    // Sécurité : le barème doit être exactement 10 ou 20
                    if (!in_array($bareme, [10, 20])) {
                        $bareme = 10;
                    }

                    // Stocker dans le tableau pivot
                    $pivotData[$matiereId] = [
                        'bareme'  => $bareme,
                        'user_id' => $request->enseignant_id, // Le même prof pour toutes les matières
                    ];
                }
            }

            // Attacher les matières à la classe (table pivot classe_matiere)
            $classe->matieres()->sync($pivotData);
        }

        // 3. Assigner le prof à cette classe (mettre à jour classe_id dans users)
        if ($request->enseignant_id) {
            User::where('id', $request->enseignant_id)
                ->update(['classe_id' => $classe->id]);
        }

        return redirect()->route('classes.index')
                         ->with('success', 'Classe créée avec succès !');
    }

    //  EDIT 

    public function edit($id)
    {
        $classe = Classe::with('matieres', 'enseignant')->findOrFail($id);
        $matieres = Matiere::all();

        // Pour la modification : afficher les profs disponibles +
        // le prof actuel de cette classe (pour ne pas le masquer)
        $enseignantsDisponibles = User::where('role', 'enseignant')
                                      ->where(function ($query) use ($classe) {
                                          // Disponibles (sans classe) OU déjà assigné à cette classe
                                          $query->whereNull('classe_id')
                                                ->orWhere('classe_id', $classe->id);
                                      })
                                      ->get();

        return view('classes.edit', compact('classe', 'matieres', 'enseignantsDisponibles'));
    }

    // UPDATE 

    public function update(Request $request, $id)
    {
        $classe = Classe::findOrFail($id);

        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classes')
                    ->whereNull('deleted_at')
                    ->ignore($classe->id),
            ],
            'frais' => 'required|integer|min:0',
            'enseignant_id' => 'nullable|exists:users,id',
        ], [
            'nom.unique' => 'Cette classe existe déjà !',
        ]);

        // Mise à jour des informations de la classe
        $classe->update([
            'nom'   => $request->nom,
            'frais' => $request->frais,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Gestion de l'enseignant
        |--------------------------------------------------------------------------
        */

        // Retirer l'ancien enseignant de cette classe
        if ($request->enseignant_id!=null){
            User::where('classe_id', $classe->id)
                ->update(['classe_id' => null]);

                // Affecter le nouvel enseignant
            if ($request->filled('enseignant_id')) {
                User::where('id', $request->enseignant_id)
                    ->update(['classe_id' => $classe->id]);
            }
        }
        

        /*
        |--------------------------------------------------------------------------
        | Gestion des matières
        |--------------------------------------------------------------------------
        */

        $pivotData = [];

        if ($request->has('matieres')) {

            foreach ($request->matieres as $matiereId => $donnees) {

                if (isset($donnees['coche'])) {

                    $bareme = (int) ($donnees['bareme'] ?? 10);

                    // Sécurité : uniquement 10 ou 20
                    if (!in_array($bareme, [10, 20])) {
                        $bareme = 10;
                    }

                    $pivotData[$matiereId] = [
                        'bareme' => $bareme,
                        'user_id' => $request->enseignant_id,
                    ];
                }
            }
        }

        // Synchronise même si aucune matière n'est cochée
        $classe->matieres()->sync($pivotData);

        return redirect()
            ->route('classes.index')
            ->with('success', 'Classe modifiée avec succès !');
    }

    // DESTROY 

    public function destroy($id)
    {
        if (Auth::user()->role !== 'gestionnaire') {
            return back()->withErrors(['error' => 'Action non autorisée.']);
        }

        $classe = Classe::findOrFail($id);

        // Libérer l'enseignant assigné à cette classe
        User::where('classe_id', $classe->id)
            ->update(['classe_id' => null]);

        $classe->delete();

        return redirect()->route('classes.index')
                         ->with('success', 'Classe supprimée !');
    }
}
