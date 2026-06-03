<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Eleve;
use App\Models\Classe;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EleveController extends Controller
{
    // Afficher la liste des élèves (Zoning : Page Eleves)
    public function index()
    {
        // On récupère les élèves avec leur classe associée (Eager Loading pour optimiser)
        $eleves = Eleve::with('classe')->get();
        // On récupère aussi toutes les classes pour alimenter le formulaire d'ajout
        $classes = Classe::all();

        return view('eleves.index', compact('eleves', 'classes'));
    }

    // Enregistrer un nouvel élève (Réservé au Gestionnaire)
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'gestionnaire') {
            return back()->withErrors(['error' => 'Seul le gestionnaire peut inscrire un élève.']);
        }

        $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'genre' => 'required|in:M,F',
            'classe_id' => 'required|exists:classes,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Max 2Mo
        ]);

        $pathPhoto = null;

        // Gestion de l'upload de la photo
        if ($request->hasFile('photo')) {
            // Sauvegarde dans le dossier storage/app/public/photos_eleves
            $pathPhoto = $request->file('photo')->store('photos_eleves', 'public');
        }

        Eleve::create([
            'nom' => strtoupper($request->nom), // Nom en majuscules
            'prenom' => ucfirst($request->prenom), // Première lettre du prénom en majuscule
            'genre' => $request->genre,
            'classe_id' => $request->classe_id,
            'photo' => $pathPhoto
        ]);

        return redirect()->route('eleves.index')->with('success', 'Élève inscrit avec succès !');
    }

    // Supprimer un élève
    public function destroy($id)
    {
        if (Auth::user()->role !== 'gestionnaire') {
            return back()->withErrors(['error' => 'Action non autorisée.']);
        }

        $eleve = Eleve::findOrFail($id);

        // Supprimer la photo du stockage si elle existe
        if ($eleve->photo) {
            Storage::disk('public')->delete($eleve->photo);
        }

        $eleve->delete();

        return redirect()->route('eleves.index')->with('success', 'Élève supprimé avec succès !');
    }

    // 1. Afficher le formulaire de modification
    public function edit($id)
    {
        $eleve = \App\Models\Eleve::findOrFail($id);
        $classes = \App\Models\Classe::all(); // Pour re-sélectionner la classe
        
        return view('eleves.edit', compact('eleve', 'classes'));
    }

    // 2. Traiter l'enregistrement des modifications
    public function update(Request $request, $id)
    {
        $eleve = \App\Models\Eleve::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'genre' => 'required|string',
            'classe_id' => 'required|exists:classes,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $eleve->nom = $request->nom;
        $eleve->prenom = $request->prenom;
        $eleve->genre = $request->genre;
        $eleve->classe_id = $request->classe_id;

        // Gestion de la nouvelle photo si elle est téléchargée
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($eleve->photo && file_exists(public_path('storage/' . $eleve->photo))) {
                unlink(public_path('storage/' . $eleve->photo));
            }
            // Stocker la nouvelle
            $path = $request->file('photo')->store('eleves', 'public');
            $eleve->photo = $path;
        }

        $eleve->save();

        return redirect()->route('eleves.index')->with('success', 'Informations de l\'élève mises à jour !');
    }
}