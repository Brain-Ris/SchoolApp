@extends('layouts.app')
@section('title', 'Notes')
@section('page-title', 'Notes')

@section('topbar-extras')
    <div class="trimestre-btn-group">
        @foreach([1,2,3] as $t)
        <a href="{{ route('notes.index', ['trimestre' => $t, 'search' => $search]) }}"
           class="trimestre-btn {{ $trimestre == $t ? 'active' : '' }}">T{{ $t }}</a>
        @endforeach
    </div>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Notes — Trimestre {{ $trimestre }}</h1>
        <p>Classement des élèves par classe</p>
    </div>
    <div class="page-header-actions">
        {{-- Barre de recherche --}}
        <form method="GET" action="{{ route('notes.index') }}" style="display:flex; gap:8px; align-items:center;">
            <input type="hidden" name="trimestre" value="{{ $trimestre }}">
            <input type="text" name="search" class="form-control" style="width:180px;"
                   placeholder="Rechercher…" value="{{ $search }}">
            <button type="submit" class="btn btn-ghost">🔍</button>
        </form>

        {{--
            BOUTON AJOUTER :
            - Gestionnaire : NE VOIT PAS ce bouton
            - Enseignant   : VOIT le bouton → ouvre le popup
        --}}
        @if(Auth::user()->role === 'enseignant')
        <button class="btn btn-primary" onclick="openModal('modal-add-note')">
            ➕ Saisir des notes
        </button>
        @endif
    </div>
</div>

{{-- TABLEAU DES CLASSEMENTS PAR CLASSE --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
    @foreach($classes as $classe)
    @if($classe->eleves->isNotEmpty())
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h2>🏫 {{ $classe->nom }}</h2>
            <span class="badge badge-info">{{ $classe->eleves->count() }} élève(s)</span>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Élève</th>
                        <th>Moyenne /10</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classe->eleves as $index => $eleve)
                    @php
                        $moyenne = $eleve->moyenneTrimestrielle($trimestre);
                    @endphp
                    <tr>
                        {{-- Rang --}}
                        <td>
                            <span class="badge {{ $index === 0 ? 'badge-warning' : ($index === 1 ? 'badge-info' : 'badge-primary') }}">
                                {{ $index + 1 }}{{ $index === 0 ? 'er' : 'ème' }}
                            </span>
                        </td>

                        {{-- Nom + Prénom --}}
                        <td><strong>{{ $eleve->nom }} {{ $eleve->prenom }}</strong></td>

                        {{-- Moyenne --}}
                        <td>
                            @if($moyenne !== null)
                                <span class="badge {{ $moyenne >= 5 ? 'badge-success' : 'badge-danger' }}"
                                      style="font-size:13px; padding:4px 10px;">
                                    {{ number_format($moyenne, 2) }}/10
                                </span>
                            @else
                                <span class="badge badge-warning">N/A</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="table-actions">
                                {{--
                                    MODIFIER : seulement l'enseignant
                                    On pré-charge les notes de l'élève dans un modal
                                --}}
                                @if(Auth::user()->role === 'enseignant')
                                <button class="btn btn-warning btn-sm"
                                        onclick="ouvrirModalEdit({{ $eleve->id }}, '{{ addslashes($eleve->nom . ' ' . $eleve->prenom) }}', {{ $trimestre }})">
                                    ✏️
                                </button>
                                @endif

                                {{-- Bulletin PDF --}}
                                <a href="{{ route('bulletin.telecharger', $eleve->id) }}?trimestre={{ $trimestre }}"
                                   class="btn btn-info btn-sm">📄</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endforeach
</div>

@if($classes->isEmpty())
<div class="card">
    <div class="card-body" style="text-align:center; padding:40px; color:var(--text-muted);">
        Aucune classe ou aucun élève.
    </div>
</div>
@endif


{{-- ═══════════════════════════════════════════════════════
     MODAL : AJOUTER DES NOTES (enseignant seulement)
     Le formulaire charge dynamiquement les élèves de SA classe
     et les matières de sa classe avec les barèmes
     ═══════════════════════════════════════════════════════ --}}
@if(Auth::user()->role === 'enseignant')

{{-- Récupérer la classe du prof connecté --}}
@php
    $profClasseId = Auth::user()->classe_id;
    $profClasse   = $profClasseId ? \App\Models\Classe::with('matieres')->find($profClasseId) : null;
    $elevesProf   = $profClasseId ? \App\Models\Eleve::where('classe_id', $profClasseId)->get() : collect();
@endphp

<div class="modal-overlay" id="modal-add-note">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3>➕ Saisir des notes
                @if($profClasse)
                    <small style="font-weight:400; color:var(--text-muted); margin-left:8px;">
                        — Classe : {{ $profClasse->nom }}
                    </small>
                @endif
            </h3>
            <button class="modal-close" onclick="closeModal('modal-add-note')">✕</button>
        </div>

        @if(!$profClasse)
            <div class="modal-body">
                <div class="alert alert-warning">
                    ⚠️ Aucune classe ne vous est assignée. Contactez le gestionnaire.
                </div>
            </div>
        @else
        <form method="POST" action="{{ route('notes.store') }}">
            @csrf
            <div class="modal-body">

                {{-- Trimestre --}}
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Trimestre <span class="required">*</span></label>
                    <select name="trimestre" class="form-control" required>
                        @foreach([1,2,3] as $t)
                            <option value="{{ $t }}" {{ $trimestre == $t ? 'selected' : '' }}>
                                Trimestre {{ $t }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{--
                    ÉLÈVE :
                    On liste uniquement les élèves de la classe du prof connecté.
                    La logique : Auth::user()->classe_id → Eleve::where('classe_id', ...) 
                --}}
                <div class="form-group" style="margin-bottom:18px;">
                    <label class="form-label">Élève <span class="required">*</span></label>
                    <select name="eleve_id" class="form-control" required>
                        <option value="">— Sélectionner un élève de {{ $profClasse->nom }} —</option>
                        @foreach($elevesProf as $eleve)
                            <option value="{{ $eleve->id }}">{{ $eleve->nom }} {{ $eleve->prenom }}</option>
                        @endforeach
                    </select>
                </div>

                {{--
                    MATIÈRES DE LA CLASSE :
                    On liste uniquement les matières configurées pour la classe du prof.
                    Chaque matière affiche son barème dans le placeholder.
                --}}
                <div>
                    <label class="form-label" style="display:block; margin-bottom:10px;">
                        Notes par matière
                    </label>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:10px;">
                        @foreach($profClasse->matieres as $matiere)
                        @php
                            $bareme = $matiere->pivot->bareme ?? 10;
                        @endphp
                        <div class="form-group">
                            <label class="form-label">{{ $matiere->nom }}</label>
                            <input type="number"
                                   name="notes[{{ $matiere->id }}]"
                                   class="form-control"
                                   placeholder="/{{ $bareme }}"
                                   min="0" max="{{ $bareme }}"
                                   step="0.5">
                        </div>
                        @endforeach
                    </div>
                </div>

                <p class="form-note">
                    Laissez vide les matières non évaluées.
                    Les notes seront créées ou mises à jour si elles existent déjà.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-add-note')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
        @endif
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════
     MODAL : MODIFIER LES NOTES D'UN ÉLÈVE
     Chargé via AJAX (fetch) pour récupérer les notes existantes
     ═══════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-edit-note">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3>✏️ Modifier les notes de <span id="edit-eleve-nom"></span></h3>
            <button class="modal-close" onclick="closeModal('modal-edit-note')">✕</button>
        </div>
        {{-- Le contenu du formulaire sera injecté par JavaScript --}}
        <div id="edit-note-content" style="padding:20px; text-align:center; color:var(--text-muted);">
            Chargement…
        </div>
    </div>
</div>

@endif

@endsection

@push('scripts')
<script>
/**
 * Ouvre le modal de modification et charge le formulaire en AJAX
 * @param eleveId    L'id de l'élève
 * @param nom        Le nom de l'élève (pour le titre du modal)
 * @param trimestre  Le trimestre courant
 */
function ouvrirModalEdit(eleveId, nom, trimestre) {
    // Afficher le nom dans le titre du modal
    document.getElementById('edit-eleve-nom').textContent = nom;
    
    // Afficher le modal avec un message de chargement
    document.getElementById('edit-note-content').innerHTML = '<p style="padding:20px; text-align:center;">⏳ Chargement des notes…</p>';
    openModal('modal-edit-note');

    // Charger le formulaire via fetch (requête GET vers la route notes.edit)
    // La route retourne un fragment HTML (pas une page complète)
    fetch(`/notes/${eleveId}/edit/${trimestre}/fragment`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        // Injecter le formulaire dans le modal
        document.getElementById('edit-note-content').innerHTML = html;
    })
    .catch(() => {
        document.getElementById('edit-note-content').innerHTML = 
            '<p style="color:var(--danger); padding:20px;">Erreur de chargement.</p>';
    });
}
</script>
@endpush
