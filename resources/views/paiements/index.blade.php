@extends('layouts.app')

@section('title', 'Paiements')
@section('page-title', 'Paiements')

@section('topbar-extras')
    <div class="topbar-search">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" placeholder="Rechercher un élève…" oninput="filterTable()">
    </div>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Suivi des paiements</h1>
        <p>Gestion de la scolarité par élève</p>
    </div>
    <div class="page-header-actions">
        @if(Auth::user()->role === 'gestionnaire')
        <button class="btn btn-primary" onclick="openModal('modal-add')">
            ＋ Enregistrer un versement
        </button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>💰 Tableau des paiements</h2>
    </div>
    <div class="table-responsive">
        <table id="paiTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Élève</th>
                    <th>Classe</th>
                    <th>Frais total</th>
                    <th>Payé</th>
                    <th>Reste</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eleves as $eleve)
                @php
                    $totalPaye  = $eleve->totalPaye();
                    $resteApayer= $eleve->resteAPayer();
                    $fraisTotal = $eleve->classe->frais ?? 0;
                    $stt = $fraisTotal > 0 ? min(100, round(($totalPaye/$fraisTotal)*100)) : 0;
                @endphp
                <tr data-name="{{ strtolower($eleve->nom . ' ' . $eleve->prenom) }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight:700;">{{ $eleve->nom }}</div>
                        <div style="color:var(--text-muted);font-size:12px;">{{ $eleve->prenom }}</div>
                    </td>
                    <td><span class="badge badge-primary">{{ $eleve->classe->nom ?? '—' }}</span></td>
                    <td>{{ number_format($fraisTotal, 0, ',', ' ') }} F</td>
                    <td style="color:var(--success); font-weight:700;">
                        {{ number_format($totalPaye, 0, ',', ' ') }} F
                    </td>
                    <td style="color:{{ $resteApayer > 0 ? 'var(--danger)' : 'var(--success)' }}; font-weight:700;">
                        {{ number_format($resteApayer, 0, ',', ' ') }} F
                    </td>
                    <td>
                        <div style="display:flex; flex-direction:column; gap:4px; min-width:100px;">
                            <div style="font-size:11px; font-weight:700; color:var(--text-muted);">{{ $stt }}%</div>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" style="width:{{ $stt }}%;
                                    background: {{ $resteApayer == 0 ? 'var(--success)' : ($stt > 50 ? 'var(--warning)' : 'var(--danger)') }};"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('paiements.recu', $eleve->id) }}"
                               class="btn btn-info btn-sm" title="Télécharger reçu">📄 Reçu</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:var(--text-muted); padding:32px;">
                        Aucun élève trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<!-- ===== MODAL : ENREGISTRER VERSEMENT ===== -->
@if(Auth::user()->role === 'gestionnaire')
<div class="modal-overlay" id="modal-add">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3>💰 Enregistrer un versement</h3>
            <button class="modal-close" onclick="closeModal('modal-add')">✕</button>
        </div>
        <form method="POST" action="{{ route('paiements.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Élève <span class="required">*</span></label>
                    <select name="eleve_id" class="form-control" required>
                        <option value="">— Sélectionner un élève —</option>
                        @foreach($eleves as $eleve)
                            @if($eleve->resteAPayer() > 0)
                            <option value="{{ $eleve->id }}">
                                {{ $eleve->nom }} {{ $eleve->prenom }}
                                — Reste: {{ number_format($eleve->resteAPayer(), 0, ',', ' ') }} F CFA
                            </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Montant versé (F CFA) <span class="required">*</span></label>
                    <input type="number" name="montant" class="form-control"
                           placeholder="Ex: 25000" min="1" required>
                </div>
                <p class="form-note">NB : Le montant ne peut pas dépasser le reste à payer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-add')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function filterTable() {
    const q    = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#paiTable tbody tr');
    rows.forEach(row => {
        row.style.display = (row.dataset.name || '').includes(q) ? '' : 'none';
    });
}
</script>
@endpush
