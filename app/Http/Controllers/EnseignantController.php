<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EnseignantController extends Controller
{
    public function index()
    {
        // On ne liste que les utilisateurs ayant le rôle enseignant
        $enseignants = User::where('role', 'enseignant')->get();
        return view('enseignants.index', compact('enseignants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','string','email','max:255',Rule::unique('users')->whereNull('deleted_at')],
            'password' => 'required|string|min:6'
        ],[
            // Personnalisation des messages pour le champ 'name'
            'name.required' => 'Le nom de la classe est obligatoire.',
            'name.max' => 'Le nom de la classe ne doit pas dépasser 255 caractères.',
            // Personnalisation pour le champ 'email'
            'email.required' => 'le mail est obligatoire.',
            'email.unique' => 'Ce mail est déja utulisé.',
            // Personnalisation pour le champ 'password'
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe de doit etre au moins 6.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'enseignant',
            'password' => Hash::make($request->password),
            'is_first_login' => true
        ]);

        return redirect()->route('enseignants.index')->with('success', 'Enseignant ajouté avec succès !');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->role === 'enseignant') {
            $user->delete();
        }
        return redirect()->route('enseignants.index')->with('success', 'Enseignant supprimé.');
    }
}