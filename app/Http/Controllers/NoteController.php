<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Note;
use App\Models\Matiere;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * CONTROLLER NOTE
 *
 * Logique des rôles :
 *
 * GESTIONNAIRE :
 *   - Peut voir le classement (index) mais PAS le formulaire d'ajout/modif
 *
 * ENSEIGNANT :
 *   - Voit tout
 *   - Pour ajouter une note : ne voit QUE les élèves de SA classe
 *   - La classe du prof = sa colonne classe_id dans la table users
 */
class NoteController extends Controller
{
    // ─── INDEX ───────────────────────────────────────────────────────────

    /**
     * Affiche le classement des élèves par classe
     * Tout le monde peut voir cette page (gestionnaire + enseignant)
     */
    public function index(Request $request)
    {
        $trimestre = $request->input('trimestre', 1);
        $search    = $request->input('search', '');

        // Charger toutes les classes avec leurs élèves et leurs notes
        $classes = Classe::with([
            'eleves' => function ($query) use ($search) {
                // Filtrer les élèves si une recherche est faite
                if ($search) {
                    $query->where('nom', 'LIKE', "%{$search}%")
                          ->orWhere('prenom', 'LIKE', "%{$search}%");
                }
            },
            'eleves.notes',
            'eleves.notes.matiere', // Pour le calcul de la moyenne dynamique
        ])->get();

        // Trier les élèves de chaque classe par moyenne (du meilleur au moins bon)
        foreach ($classes as $classe) {
            $elevesTriés = $classe->eleves->sortByDesc(function ($eleve) use ($trimestre) {
                return $eleve->moyenneTrimestrielle($trimestre) ?? -1;
            })->values(); // values() remet les indices à 0 (pour avoir rang 1, 2, 3...)

            $classe->setRelation('eleves', $elevesTriés);
        }

        return view('notes.index', compact('classes', 'trimestre', 'search'));
    }

    // ─── CREATE ──────────────────────────────────────────────────────────

    /**
     * Affiche le formulaire de saisie de notes
     * SEUL L'ENSEIGNANT peut accéder à cette page
     */
    public function create()
    {
        // Bloquer l'accès si ce n'est pas un enseignant
        if (Auth::user()->role !== 'enseignant') {
            return redirect()->route('notes.index')
                             ->withErrors(['error' => 'Seul un enseignant peut saisir des notes.']);
        }

        // Récupérer la classe assignée à CET enseignant connecté
        // On utilise sa colonne classe_id dans la table users
        $prof     = Auth::user();
        $classeId = $prof->classe_id;

        if (!$classeId) {
            return redirect()->route('notes.index')
                             ->withErrors(['error' => 'Aucune classe ne vous est assignée. Contactez le gestionnaire.']);
        }

        // Charger la classe avec ses matières (et leurs barèmes depuis le pivot)
        $classe = Classe::with('matieres')->findOrFail($classeId);

        // Lister uniquement les élèves de SA classe
        $eleves = Eleve::where('classe_id', $classeId)->get();

        // Les matières de sa classe (avec le barème pour chacune)
        $matieres = $classe->matieres; // Collection avec pivot->bareme disponible

        return view('notes.create', compact('eleves', 'matieres', 'classe'));
    }

    // ─── STORE ───────────────────────────────────────────────────────────

    /**
     * Enregistre les notes dans la base de données
     * Chaque matière crée/met à jour UNE ligne dans la table notes
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'enseignant') {
            return redirect()->route('notes.index')
                             ->withErrors(['error' => 'Action non autorisée.']);
        }

        $request->validate([
            'eleve_id'  => 'required|exists:eleves,id',
            'trimestre' => 'required|integer|between:1,3',
            'notes'     => 'required|array', // notes[matiere_id] = valeur
        ]);

        $eleveId   = $request->eleve_id;
        $trimestre = $request->trimestre;

        // Pour chaque matière, enregistrer ou mettre à jour la note
        foreach ($request->notes as $matiereId => $valeurs) {
            // Si la valeur est vide, on passe (note non saisie)
            if ($valeurs === null || $valeurs === '') {
                continue;
            }

            // updateOrCreate : si la note existe pour cet élève/matière/trimestre,
            // on la met à jour. Sinon, on la crée.
            Note::updateOrCreate(
                [
                    'eleve_id'   => $eleveId,
                    'matiere_id' => $matiereId,
                    'trimestre'  => $trimestre,
                ],
                [
                    'valeurs' => floatval($valeurs),
                ]
            );
        }

        return redirect()->route('notes.index', ['trimestre' => $trimestre])
                         ->with('success', 'Notes enregistrées avec succès !');
    }

    // ─── EDIT ────────────────────────────────────────────────────────────

    /**
     * Affiche le formulaire de modification des notes d'un élève
     * Même logique que create() mais avec les notes existantes pré-remplies
     */
    public function edit($eleveId, $trimestre)
    {
        if (Auth::user()->role !== 'enseignant') {
            return redirect()->route('notes.index')
                             ->withErrors(['error' => 'Seul un enseignant peut modifier des notes.']);
        }

        // Récupérer l'élève
        $eleve = Eleve::findOrFail($eleveId);

        // Vérifier que cet élève appartient bien à la classe du prof connecté
        if ($eleve->classe_id !== Auth::user()->classe_id) {
            return redirect()->route('notes.index')
                             ->withErrors(['error' => 'Cet élève n\'est pas dans votre classe.']);
        }

        // Charger la classe avec ses matières et leurs barèmes
        $classe   = Classe::with('matieres')->findOrFail($eleve->classe_id);
        $matieres = $classe->matieres;

        // Récupérer les notes existantes de cet élève pour ce trimestre
        // On crée un tableau associatif : matiere_id => valeur
        // pour faciliter l'affichage dans le formulaire
        $notesExistantes = Note::where('eleve_id', $eleveId)
                               ->where('trimestre', $trimestre)
                               ->get()
                               ->keyBy('matiere_id'); // Clé = matiere_id

        return view('notes.edit', compact('eleve', 'trimestre', 'matieres', 'notesExistantes', 'classe'));
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────

    public function update(Request $request, $eleveId, $trimestre)
    {
        if (Auth::user()->role !== 'enseignant') {
            return redirect()->route('notes.index')
                             ->withErrors(['error' => 'Action non autorisée.']);
        }

        $request->validate([
            'notes' => 'required|array',
        ]);

        // Même logique que store() : updateOrCreate pour chaque matière
        foreach ($request->notes as $matiereId => $valeurs) {
            if ($valeurs === null || $valeurs === '') {
                continue;
            }

            Note::updateOrCreate(
                [
                    'eleve_id'   => $eleveId,
                    'matiere_id' => $matiereId,
                    'trimestre'  => $trimestre,
                ],
                [
                    'valeurs' => floatval($valeurs),
                ]
            );
        }

        return redirect()->route('notes.index', ['trimestre' => $trimestre])
                         ->with('success', 'Notes mises à jour avec succès !');
    }

    // ─── BULLETIN PDF ────────────────────────────────────────────────────

    /**
     * Génère et télécharge le bulletin de l'élève en PDF
     */
    public function telechargerBulletin(Request $request, $id)
    {
        $eleve     = Eleve::with(['classe.matieres', 'notes.matiere'])->findOrFail($id);
        $trimestre = $request->input('trimestre', 1);

        // Récupérer les notes du trimestre avec les matières
        $notes = Note::with('matiere')
                     ->where('eleve_id', $id)
                     ->where('trimestre', $trimestre)
                     ->get();

        if ($notes->isEmpty()) {
            return back()->withErrors(['error' => "Aucune note pour le trimestre {$trimestre}."]);
        }

        // Récupérer les barèmes des matières pour cette classe
        $baremes = [];
        foreach ($eleve->classe->matieres as $matiere) {
            $baremes[$matiere->id] = $matiere->pivot->bareme ?? 10;
        }

        // Calculer la moyenne générale
        $moyenneGenerale = $eleve->moyenneTrimestrielle($trimestre);

        // Générer le PDF
        $pdf = Pdf::loadView('notes.bulletin_pdf', compact(
            'eleve', 'notes', 'trimestre', 'moyenneGenerale', 'baremes'
        ));

        return $pdf->download("bulletin_{$eleve->nom}_{$eleve->prenom}_T{$trimestre}.pdf");
    }

    // ─── EDIT FRAGMENT (pour AJAX) ────────────────────────────────────────

    /**
     * Retourne uniquement le formulaire d'édition (sans layout)
     * C'est ce qui est chargé par le JavaScript dans le modal
     *
     * Route : GET /notes/{eleve}/edit/{trimestre}/fragment
     */
    public function editFragment($eleveId, $trimestre)
    {
        if (Auth::user()->role !== 'enseignant') {
            return response('<p style="color:red; padding:16px;">Accès refusé.</p>', 403);
        }

        $eleve = Eleve::findOrFail($eleveId);

        // Vérifier que cet élève est bien dans la classe du prof
        if ($eleve->classe_id !== Auth::user()->classe_id) {
            return response('<p style="color:red; padding:16px;">Cet élève n\'est pas dans votre classe.</p>', 403);
        }

        $classe   = Classe::with('matieres')->findOrFail($eleve->classe_id);
        $matieres = $classe->matieres;

        // Notes existantes indexées par matiere_id pour pré-remplir le formulaire
        $notesExistantes = Note::where('eleve_id', $eleveId)
                               ->where('trimestre', $trimestre)
                               ->get()
                               ->keyBy('matiere_id');

        // Retourner seulement le fragment HTML (pas la page complète)
        return view('notes.edit_fragment', compact(
            'eleve', 'trimestre', 'matieres', 'notesExistantes', 'classe'
        ));
    }
}
