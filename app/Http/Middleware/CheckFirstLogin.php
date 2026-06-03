<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckFirstLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur est connecté, qu'il est gestionnaire et que c'est sa première connexion
        if (Auth::check() && Auth::user()->role === 'gestionnaire' && Auth::user()->is_first_login) {
            if (!$request->is('password/change') && !$request->is('logout')) {
                return redirect()->route('password.change')->with('error', 'Vous devez changer votre mot de passe par défaut avant de continuer.');
            }
        }

        return $next($request);
    }
}