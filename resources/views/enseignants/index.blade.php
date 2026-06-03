@extends('layouts.app')

@section('title', 'Enseignants')
@section('page-title', 'Enseignants')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Liste des enseignants</h1>
        <p>{{ $enseignants->count() }} enseignant(s) enregistré(s)</p>
    </div>
    <div class="page-header-actions">
        @if(Auth::user()->role === 'gestionnaire')
        <button class="btn btn-primary" onclick="openModal('modal-add')">
            ＋ Nouvel enseignant
        </button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>👨‍🏫 Tableau des enseignants</h2>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enseignants as $enseignant)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="eleve-avatar-placeholder">
                                {{ strtoupper(substr($enseignant->name, 0, 2)) }}
                            </div>
                            <strong>{{ $enseignant->name }}</strong>
                        </div>
                    </td>
                    <td style="color:var(--text-muted);">{{ $enseignant->email }}</td>
                    <td><span class="badge badge-primary">Enseignant</span></td>
                    <td>
                        <div class="table-actions">
                            @if(Auth::user()->role === 'gestionnaire')
                            <button class="btn btn-danger btn-icon btn-sm"
                                    title="Supprimer"
                                    onclick="openDeleteModal({{ $enseignant->id }}, '{{ addslashes($enseignant->name) }}')">
                                🗑️
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--text-muted); padding:32px;">
                        Aucun enseignant enregistré.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<!-- ===== MODAL : AJOUTER ENSEIGNANT ===== -->
@if(Auth::user()->role === 'gestionnaire')
<div class="modal-overlay" id="modal-add">
    <div class="modal">
        <div class="modal-header">
            <h3>➕ Ajouter un enseignant</h3>
            <button class="modal-close" onclick="closeModal('modal-add')">✕</button>
        </div>
        <form method="POST" action="{{ route('enseignants.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label class="form-label">Nom complet <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control"
                               placeholder="Entrer le nom" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group form-full">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control"
                               placeholder="Entrer l'adresse email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group form-full">
                        <label class="form-label">Mot de passe <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Au moins 6 caractères" required>
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


<!-- ===== MODAL : SUPPRIMER ENSEIGNANT ===== -->
<div class="modal-overlay" id="modal-delete">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3>🗑️ Confirmer la suppression</h3>
            <button class="modal-close" onclick="closeModal('modal-delete')">✕</button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px; color:var(--text-muted);">
                Supprimer l'enseignant <strong id="delete-name"></strong> ?
                Son compte sera définitivement supprimé.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeModal('modal-delete')">Annuler</button>
            <form method="POST" id="deleteForm">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function openDeleteModal(id, name) {
    document.getElementById('deleteForm').action = '/enseignants/' + id;
    document.getElementById('delete-name').textContent = name;
    openModal('modal-delete');
}
</script>
@endpush
