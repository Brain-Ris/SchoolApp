<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Matiere;
use Illuminate\Support\Facades\Auth;

class MatiereController extends Controller
{
    // Afficher la liste des matières
    public function index()
    {
        $matieres = Matiere::all();
        return view('matieres.index', compact('matieres'));
    }

    // Enregistrer une nouvelle matière unique (uniquement le nom)
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'gestionnaire') {
            return back()->withErrors(['error' => 'Action non autorisée.']);
        }

        $request->validate([
            // Vérifie l'unicité en ignorant les lignes soft-deletées si tu as SoftDeletes
            'nom' => 'required|string|max:100|unique:matieres,nom,NULL,id,deleted_at,NULL',
        ], [
            'nom.unique' => 'Désolé, cette matière existe déjà dans l’application !',
            'nom.required' => 'Le nom de la matière est obligatoire.',
        ]);

        Matiere::create([
            'nom' => $request->nom,
        ]);

        return redirect()->route('matieres.index')->with('success', 'Matière ajoutée avec succès !');
    }

    // Supprimer une matière
    public function destroy($id)
    {
        if (Auth::user()->role !== 'gestionnaire') {
            return back()->withErrors(['error' => 'Action non autorisée.']);
        }

        $matiere = Matiere::findOrFail($id);
        $matiere->delete();

        return redirect()->route('matieres.index')->with('success', 'Matière supprimée avec succès !');
    }
}