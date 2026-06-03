@extends('layouts.app')
@section('title', 'Classes')
@section('page-title', 'Classes')

@section('content')


<div class="stats-grid">
    <div class="stat-card blue"><div class="stat-icon">🎓</div><div class="stat-info"><div class="stat-value">{{ $totalEleves }}</div><div class="stat-label">Total élèves</div></div></div>
    <div class="stat-card gold"><div class="stat-icon">🏫</div><div class="stat-info"><div class="stat-value">{{ $classes->count() }}</div><div class="stat-label">Classes</div></div></div>
</div>

<div class="page-header">
    <div class="page-header-left">
        <h1>Liste des classes</h1>
        <p>{{ $classes->count() }} classe(s) configurée(s)</p>
    </div>
    <div class="page-header-actions">
        @if(Auth::user()->role === 'gestionnaire')
            <button class="btn btn-primary" onclick="openModal('modal-add')">＋ Nouvelle classe</button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>🏫 Tableau des classes</h2></div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Nom</th><th>Enseignant</th>
                    <th>Effectif</th><th>Frais</th><th>Matières / Barèmes</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $classe)
                {{-- Chercher l'enseignant assigné à cette classe via classe_id dans users --}}
                @php
                    $enseignant = \App\Models\User::where('classe_id', $classe->id)->first();
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $classe->nom }}</strong></td>
                    <td>
                        @if($enseignant)
                            <span class="badge badge-success">{{ $enseignant->name }}</span>
                        @else
                            <span class="badge badge-warning">Non assigné</span>
                        @endif
                    </td>
                    <td><span class="badge badge-info">{{ $classe->eleves->count() }}</span></td>
                    <td>{{ number_format($classe->frais, 0, ',', ' ') }} F</td>
                    <td style="max-width:280px;">
                        {{-- Afficher chaque matière avec son barème depuis le pivot --}}
                        @forelse($classe->matieres as $matiere)
                            <span class="badge badge-primary" style="margin:2px; font-size:11px;">
                                {{ $matiere->nom }} /{{ $matiere->pivot->bareme }}
                            </span>
                        @empty
                            <span style="color:var(--text-muted); font-size:12px;">Aucune matière</span>
                        @endforelse
                    </td>
                    <td>
                        <div class="table-actions">
                            @if(Auth::user()->role === 'gestionnaire')
                            <button class="btn btn-warning btn-icon btn-sm" title="Modifier"
                                    onclick='openEditModal(
                                        {{ $classe->id }},
                                        @json($classe->nom),
                                        {{ $classe->frais }},
                                        {{ optional($enseignant)->id ?? "null" }},
                                        @json(
                                            $classe->matieres->mapWithKeys(function($m){
                                                return [
                                                    $m->id => [
                                                        "bareme" => $m->pivot->bareme
                                                    ]
                                                ];
                                            })
                                        )
                                        )'>✏️</button>
                            <button class="btn btn-danger btn-icon btn-sm" title="Supprimer"
                                    onclick="openDeleteModal({{ $classe->id }}, '{{ addslashes($classe->nom) }}')">🗑️</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:var(--text-muted); padding:32px;">Aucune classe créée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════ MODAL AJOUTER ═══════ --}}
@if(Auth::user()->role === 'gestionnaire')
<div class="modal-overlay" id="modal-add">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3>➕ Nouvelle classe</h3>
            <button class="modal-close" onclick="closeModal('modal-add')">✕</button>
        </div>
        <form method="POST" action="{{ route('classes.store') }}">
            @csrf
            <div class="modal-body">

                {{-- Nom + Frais --}}
                <div class="form-grid-2" style="margin-bottom:16px;">
                    <div class="form-group">
                        <label class="form-label">Nom de la classe <span class="required">*</span></label>
                        <input type="text" name="nom" class="form-control" placeholder="Ex: CP1, CE2…" value="{{ old('nom') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Frais de scolarité (F CFA) <span class="required">*</span></label>
                        <input type="number" name="frais" class="form-control" placeholder="Ex: 75000" value="{{ old('frais') }}" min="0" required>
                    </div>
                </div>

                {{-- Enseignant --}}
                <div class="form-group" style="margin-bottom:18px;">
                    <label class="form-label">Enseignant responsable</label>
                    <select name="enseignant_id" class="form-control">
                        <option value="">— Aucun (assigner plus tard) —</option>
                        @foreach($enseignantsDisponibles as $prof)
                            <option value="{{ $prof->id }}">{{ $prof->name }} — {{ $prof->email }}</option>
                        @endforeach
                    </select>
                    <small style="color:var(--text-muted);">
                        ℹ️ Seuls les enseignants sans classe sont listés ici.
                    </small>
                </div>

                {{-- Matières + Barèmes --}}
                <div>
                    <label class="form-label" style="display:block; margin-bottom:10px;">
                        Matières de la classe
                        <small style="color:var(--text-muted); font-weight:400;">
                            — Cochez et saisissez le barème (10 ou 20)
                        </small>
                    </label>

                    @if($matieres->isEmpty())
                        <p style="color:var(--warning); font-size:13px;">
                            ⚠️ Aucune matière disponible. <a href="{{ route('matieres.index') }}">Créer des matières d'abord →</a>
                        </p>
                    @else
                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:10px;">
                            @foreach($matieres as $matiere)
                            <div style="background:var(--bg); border:1.5px solid var(--border); border-radius:8px; padding:10px 14px; display:flex; align-items:center; gap:10px;">

                                {{-- Checkbox --}}
                                <input type="checkbox"
                                       name="matieres[{{ $matiere->id }}][coche]"
                                       id="add_m{{ $matiere->id }}"
                                       value="1"
                                       onchange="toggleBareme(this, 'add_b{{ $matiere->id }}')">

                                {{-- Nom matière --}}
                                <label for="add_m{{ $matiere->id }}" style="flex:1; font-weight:600; font-size:13px; cursor:pointer;">
                                    {{ $matiere->nom }}
                                </label>

                                {{-- Barème (10 ou 20) --}}
                                <input type="number"
                                       name="matieres[{{ $matiere->id }}][bareme]"
                                       id="add_b{{ $matiere->id }}"
                                       class="form-control"
                                       placeholder="/10 ou /20"
                                       min="10" max="20" step="10"
                                       style="width:85px; opacity:0.4;"
                                       disabled>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <p class="form-note">
                    NB : <span style="color:var(--danger)">*</span> = obligatoire.
                    Le barème doit être <strong>10</strong> ou <strong>20</strong> uniquement.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-add')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════ MODAL MODIFIER ═══════ --}}
<div class="modal-overlay" id="modal-edit">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3>✏️ Modifier la classe</h3>
            <button class="modal-close" onclick="closeModal('modal-edit')">✕</button>
        </div>

        <form method="POST" id="editForm">
            @csrf
            @method('PUT')

            <div class="modal-body">

                {{-- Nom + frais --}}
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" id="edit-nom" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Frais</label>
                        <input type="number" name="frais" id="edit-frais" class="form-control" required>
                    </div>
                </div>

                {{-- Enseignant --}}
                <div class="form-group">
                    <label class="form-label">Enseignant responsable</label>

                    <select name="enseignant_id" id="edit-enseignant" class="form-control">
                        <option value="">Aucun</option>

                        @foreach($enseignantsDisponibles as $prof)
                            <option value="{{ $prof->id }}">
                                {{ $prof->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Matières --}}
                <div>
                    <label class="form-label">
                        Matières et barèmes
                    </label>

                    <div class="matieres-edit">

                        @foreach($matieres as $matiere)

                        <div class="matiere-item">

                            <input
                                type="checkbox"
                                id="edit_m{{ $matiere->id }}"
                                name="matieres[{{ $matiere->id }}][coche]"
                                value="1"
                                onchange="toggleBareme(this,'edit_b{{ $matiere->id }}')">

                            <label for="edit_m{{ $matiere->id }}">
                                {{ $matiere->nom }}
                            </label>

                            <input
                                type="number"
                                id="edit_b{{ $matiere->id }}"
                                name="matieres[{{ $matiere->id }}][bareme]"
                                min="10"
                                max="20"
                                step="10"
                                class="form-control"
                                style="width:80px;">
                        </div>

                        @endforeach

                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit')">
                    Annuler
                </button>

                <button type="submit" class="btn btn-warning">
                    Mettre à jour
                </button>
            </div>

        </form>
    </div>
</div>

{{-- ═══════ MODAL SUPPRIMER ═══════ --}}
<div class="modal-overlay" id="modal-delete">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3>🗑️ Confirmer la suppression</h3>
            <button class="modal-close" onclick="closeModal('modal-delete')">✕</button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px; color:var(--text-muted);">
                Supprimer la classe <strong id="delete-name"></strong> ?
                <br><strong style="color:var(--danger);">⚠️ Les élèves rattachés seront aussi supprimés.</strong>
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeModal('modal-delete')">Annuler</button>
            <form method="POST" id="deleteForm">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function openEditModal(id, nom, frais, enseignantId, matieres){
    document.getElementById('editForm').action ='/classes/' + id;
    document.getElementById('edit-nom').value = nom;
    document.getElementById('edit-frais').value =frais;
    document.getElementById('edit-enseignant').value =enseignantId ?? '';

    document.querySelectorAll(
        '#modal-edit input[type=checkbox]'
    ).forEach(cb => cb.checked = false);

    document.querySelectorAll(
        '#modal-edit input[type=number]'
    ).forEach(input => {
        if(input.id.startsWith('edit_b')){
            input.value = '';
            input.disabled = true;
            input.required = false;
        }
    });

    for(let idMatiere in matieres){
        let check =
            document.getElementById(
                'edit_m' + idMatiere
            );
        let bareme =
            document.getElementById(
                'edit_b' + idMatiere
            );
        if(check && bareme){
            check.checked = true;
            bareme.disabled = false;
            bareme.required = true;
            bareme.value =
                matieres[idMatiere].bareme;
        }
    }

    openModal('modal-edit');
}

function openDeleteModal(id, nom) {
    document.getElementById('deleteForm').action = '/classes/' + id;
    document.getElementById('delete-name').textContent = nom;
    openModal('modal-delete');
}

/**
 * Active ou désactive le champ barème selon la case cochée
 * Quand on coche une matière, on active son input barème (obligatoire)
 * Quand on décoche, on le désactive
 */
function toggleBareme(checkbox, baremeId) {
    const input = document.getElementById(baremeId);
    if (checkbox.checked) {
        input.disabled = false;
        input.style.opacity = '1';
        input.required = true;
        input.value = 10; // valeur par défaut
    } else {
        input.disabled = true;
        input.style.opacity = '0.4';
        input.required = false;
        input.value = '';
    }
}
</script>
@endpush
