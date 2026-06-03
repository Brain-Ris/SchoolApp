<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $informations = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Créer le compte admin initial s'il n'existe pas du tout dans la table users
        $adminEmail = "admin@gmail.com";
        if (User::where('email', $adminEmail)->count() == 0) {
            User::create([
                'name' => 'Gestionnaire Principal',
                'email' => $adminEmail,
                'role' => 'gestionnaire',
                'password' => Hash::make('Admin@1234'),
                'is_first_login' => true
            ]);
        }

        // Tentative globale de connexion
        if (Auth::attempt($informations)) {
            $request->session()->regenerate();

            // Si is_first_login est vrai, on l'envoie direct modifier son mot de passe
            if (Auth::user()->is_first_login) {
                return redirect()->route('password.change');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects.',
        ])->onlyInput('email');
    }

    // Afficher le formulaire obligatoire
    public function showChangePassword()
    {
        return view('auth.change_password');
    }

    // Traiter le changement de mot de passe
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->is_first_login = false; // Marqué comme fait !
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Votre mot de passe a été sécurisé avec succès !');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}