@extends('layouts.app')

@section('title', 'Matières')
@section('page-title', 'Matières')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Liste des matières</h1>
        <p>{{ $matieres->count() }} matière(s) enregistrée(s)</p>
    </div>
    <div class="page-header-actions">
        @if(Auth::user()->role === 'gestionnaire')
        <button class="btn btn-primary" onclick="openModal('modal-add')">
            ＋ Nouvelle matière
        </button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>📚 Tableau des matières</h2>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom de la matière</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matieres as $matiere)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $matiere->nom }}</strong></td>
                    <td>
                        <div class="table-actions">
                            @if(Auth::user()->role === 'gestionnaire')
                            <button class="btn btn-danger btn-icon btn-sm"
                                    title="Supprimer"
                                    onclick="openDeleteModal({{ $matiere->id }}, '{{ addslashes($matiere->nom) }}')">
                                🗑️
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center; color:var(--text-muted); padding:32px;">
                        Aucune matière enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<!-- ===== MODAL : AJOUTER MATIÈRE ===== -->
@if(Auth::user()->role === 'gestionnaire')
<div class="modal-overlay" id="modal-add">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3>➕ Nouvelle matière</h3>
            <button class="modal-close" onclick="closeModal('modal-add')">✕</button>
        </div>
        <form method="POST" action="{{ route('matieres.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nom de la matière <span class="required">*</span></label>
                    <input type="text" name="nom" class="form-control"
                           placeholder="Ex: Mathématiques, Français…"
                           value="{{ old('nom') }}" required>
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


<!-- ===== MODAL : SUPPRIMER MATIÈRE ===== -->
<div class="modal-overlay" id="modal-delete">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3>🗑️ Confirmer la suppression</h3>
            <button class="modal-close" onclick="closeModal('modal-delete')">✕</button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px; color:var(--text-muted);">
                Supprimer la matière <strong id="delete-name"></strong> ?
                Elle sera retirée de toutes les classes.
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
function openDeleteModal(id, nom) {
    document.getElementById('deleteForm').action = '/matieres/' + id;
    document.getElementById('delete-name').textContent = nom;
    openModal('modal-delete');
}
</script>
@endpush
