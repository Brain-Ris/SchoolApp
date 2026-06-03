<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckEnseignant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur est connecté mais qu'il est gestionnaire, on lui bloque l'accès aux notes
        if (Auth::check() && Auth::user()->role !== 'enseignant') {
            abort(403, "Action interdite : Le gestionnaire ne peut pas attribuer ou modifier de notes.");
        }

        return $next($request);
    }
}