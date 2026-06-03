<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Eleve;
use App\Models\Paiement;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf; // Importation pour le PDF

class PaiementController extends Controller
{
    // 1. Afficher la page des paiements (Ton tableau de suivi)
    public function index()
    {
        // On charge les élèves avec leurs classes et leurs paiements pour les calculs
        $eleves = Eleve::with(['classe', 'paiements'])->get();
        return view('paiements.index', compact('eleves'));
    }

    // 2. Enregistrer un nouveau versement (Gestionnaire uniquement)
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'gestionnaire') {
            return back()->withErrors(['error' => 'Action non autorisée. Seul le gestionnaire gère la scolarité.']);
        }

        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'montant' => 'required|integer|min:1'
        ]);

        $eleve = Eleve::findOrFail($request->eleve_id);
        $resteActuel = $eleve->resteAPayer();

        // Sécurité : Empêcher de verser plus que le reste dû
        if ($request->montant > $resteActuel) {
            return back()->withErrors(['error' => "Le montant versé dépasse le reste à payer ! Cet élève ne doit plus que " . $resteActuel . " F CFA."]);
        }

        // Création du versement
        Paiement::create([
            'eleve_id' => $request->eleve_id,
            'montant' => $request->montant
        ]);

        return redirect()->route('paiements.index')->with('success', 'Versement enregistré avec succès !');
    }

    // 3. Générer et télécharger le reçu de scolarité au format PDF
    public function telechargerRecu($id)
    {
        $eleve = Eleve::with(['classe', 'paiements'])->findOrFail($id);

        $data = [
            'eleve' => $eleve,
            'date' => date('d/m/Y à H:i'),
            'totalPaye' => $eleve->totalPaye(),
            'resteAPayer' => $eleve->resteAPayer(),
        ];

        $pdf = Pdf::loadView('paiements.recu_pdf', $data);

        return $pdf->download('recu_' . $eleve->nom . '_' . $eleve->prenom . '.pdf');
    }
}