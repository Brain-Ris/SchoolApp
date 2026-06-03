@extends('layouts.app')

@section('title', 'Élèves')
@section('page-title', 'Élèves')

@section('topbar-extras')
    <div class="topbar-search">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" placeholder="Rechercher un élève…" oninput="filterTable()">
    </div>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Liste des élèves</h1>
        <p>{{ $eleves->count() }} élève(s) inscrit(s)</p>
    </div>
    <div class="page-header-actions">
        @if(Auth::user()->role === 'gestionnaire')
        <button class="btn btn-primary" onclick="openModal('modal-add')">
            ＋ Nouvel élève
        </button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>📋 Tableau des élèves</h2>
        <div class="toolbar" style="margin:0">
            <select class="toolbar-select" onchange="filterByClasse(this.value)">
                <option value="">Toutes les classes</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->nom }}">{{ $classe->nom }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="elevesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Nom & Prénom</th>
                    <th>Genre</th>
                    <th>Classe</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eleves as $eleve)
                <tr data-classe="{{ $eleve->classe->nom ?? '' }}"
                    data-name="{{ strtolower($eleve->nom . ' ' . $eleve->prenom) }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @if($eleve->photo)
                            <img src="{{ asset('storage/' . $eleve->photo) }}"
                                 alt="{{ $eleve->nom }}" class="eleve-avatar">
                        @else
                            <div class="eleve-avatar-placeholder">
                                {{ strtoupper(substr($eleve->nom,0,1)) }}{{ strtoupper(substr($eleve->prenom,0,1)) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:700;">{{ $eleve->nom }}</div>
                        <div style="color:var(--text-muted);font-size:12px;">{{ $eleve->prenom }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $eleve->genre === 'M' ? 'badge-info' : 'badge-teal' }}">
                            {{ $eleve->genre === 'M' ? '♂ Masculin' : '♀ Féminin' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-primary">{{ $eleve->classe->nom ?? '—' }}</span>
                    </td>
                    <td>
                        <div class="table-actions">
                            {{-- Bouton Modifier --}}
                            @if(Auth::user()->role === 'gestionnaire')
                            <button class="btn btn-warning btn-icon btn-sm"
                                    title="Modifier"
                                    onclick="openEditModal(
                                        {{ $eleve->id }},
                                        '{{ addslashes($eleve->nom) }}',
                                        '{{ addslashes($eleve->prenom) }}',
                                        '{{ $eleve->genre }}',
                                        {{ $eleve->classe_id ?? 'null' }}
                                    )">✏️</button>
                            @endif

                            {{-- Bouton Notes --}}
                            <a href="{{ route('notes.index') }}"
                               class="btn btn-success btn-icon btn-sm" title="Notes">📊</a>

                            {{-- Bouton Supprimer --}}
                            @if(Auth::user()->role === 'gestionnaire')
                            <button class="btn btn-danger btn-icon btn-sm"
                                    title="Supprimer"
                                    onclick="openDeleteModal({{ $eleve->id }}, '{{ addslashes($eleve->nom) }} {{ addslashes($eleve->prenom) }}')">
                                🗑️
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--text-muted); padding:32px;">
                        Aucun élève inscrit pour le moment.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<!-- ===== MODAL : AJOUTER UN ÉLÈVE ===== -->
@if(Auth::user()->role === 'gestionnaire')
<div class="modal-overlay" id="modal-add">
    <div class="modal">
        <div class="modal-header">
            <h3>➕ Inscrire un nouvel élève</h3>
            <button class="modal-close" onclick="closeModal('modal-add')">✕</button>
        </div>
        <form method="POST" action="{{ route('eleves.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-grid-2">

                    <div class="form-group">
                        <label class="form-label">Nom <span class="required">*</span></label>
                        <input type="text" name="nom" class="form-control"
                               placeholder="Entrer le nom" value="{{ old('nom') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prénom(s) <span class="required">*</span></label>
                        <input type="text" name="prenom" class="form-control"
                               placeholder="Entrer le(s) prénom(s)" value="{{ old('prenom') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Genre <span class="required">*</span></label>
                        <select name="genre" class="form-control" required>
                            <option value="">— Choisir —</option>
                            <option value="M" {{ old('genre')=='M'?'selected':'' }}>Masculin</option>
                            <option value="F" {{ old('genre')=='F'?'selected':'' }}>Féminin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Classe <span class="required">*</span></label>
                        <select name="classe_id" class="form-control" required>
                            <option value="">— Choisir —</option>
                            @foreach($classes as $classe)
                                <option value="{{ $classe->id }}"
                                    {{ old('classe_id') == $classe->id ? 'selected' : '' }}>
                                    {{ $classe->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group form-full">
                        <label class="form-label">Photo (optionnel)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>

                </div>
                <p class="form-note">NB : Les champs avec <span style="color:var(--danger)">*</span> sont obligatoires.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-add')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>


<!-- ===== MODAL : MODIFIER UN ÉLÈVE ===== -->
<div class="modal-overlay" id="modal-edit">
    <div class="modal">
        <div class="modal-header">
            <h3>✏️ Modifier l'élève</h3>
            <button class="modal-close" onclick="closeModal('modal-edit')">✕</button>
        </div>
        <form method="POST" id="editForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-grid-2">

                    <div class="form-group">
                        <label class="form-label">Nom <span class="required">*</span></label>
                        <input type="text" name="nom" id="edit-nom" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prénom(s) <span class="required">*</span></label>
                        <input type="text" name="prenom" id="edit-prenom" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Genre <span class="required">*</span></label>
                        <select name="genre" id="edit-genre" class="form-control" required>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Classe <span class="required">*</span></label>
                        <select name="classe_id" id="edit-classe" class="form-control" required>
                            @foreach($classes as $classe)
                                <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group form-full">
                        <label class="form-label">Nouvelle photo (optionnel)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>

                </div>
                <p class="form-note">NB : Les champs avec <span style="color:var(--danger)">*</span> sont obligatoires.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit')">Annuler</button>
                <button type="submit" class="btn btn-warning">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>


<!-- ===== MODAL : SUPPRIMER ===== -->
<div class="modal-overlay" id="modal-delete">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3>🗑️ Confirmer la suppression</h3>
            <button class="modal-close" onclick="closeModal('modal-delete')">✕</button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px; color:var(--text-muted);">
                Êtes-vous sûr de vouloir supprimer l'élève <strong id="delete-name"></strong> ?
                Cette action est irréversible.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeModal('modal-delete')">Annuler</button>
            <form method="POST" id="deleteForm">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
/* ---- Pré-remplir le modal modifier ---- */
function openEditModal(id, nom, prenom, genre, classeId) {
    document.getElementById('editForm').action = '/eleves/' + id;
    document.getElementById('edit-nom').value    = nom;
    document.getElementById('edit-prenom').value = prenom;
    document.getElementById('edit-genre').value  = genre;
    if (classeId) document.getElementById('edit-classe').value = classeId;
    openModal('modal-edit');
}

/* ---- Pré-remplir le modal supprimer ---- */
function openDeleteModal(id, name) {
    document.getElementById('deleteForm').action = '/eleves/' + id;
    document.getElementById('delete-name').textContent = name;
    openModal('modal-delete');
}

/* ---- Filtre recherche ---- */
function filterTable() {
    const q     = document.getElementById('searchInput').value.toLowerCase();
    const rows  = document.querySelectorAll('#elevesTable tbody tr');
    rows.forEach(row => {
        const name = row.dataset.name || '';
        row.style.display = name.includes(q) ? '' : 'none';
    });
}

/* ---- Filtre par classe ---- */
function filterByClasse(val) {
    const rows = document.querySelectorAll('#elevesTable tbody tr');
    rows.forEach(row => {
        row.style.display = (!val || row.dataset.classe === val) ? '' : 'none';
    });
}
</script>
@endpush
