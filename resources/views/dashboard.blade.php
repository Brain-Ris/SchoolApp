@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')

{{-- STATS GLOBALES --}}
<div class="stats-grid">
    <div class="stat-card teal">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <div class="stat-value">{{ number_format($totalEncaisse, 0, ',', ' ') }}</div>
            <div class="stat-label">Encaissé (F CFA)</div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <div class="stat-value">{{ number_format(($totalAttendu-$totalEncaisse), 0, ',', ' ') }}</div>
            <div class="stat-label">En attente (F CFA)</div>
        </div>
    </div>
    <!-- <div class="stat-card blue">
        <div class="stat-icon">🎓</div>
        <div class="stat-info">
            <div class="stat-value">{{ $totalEleves }}</div>
            <div class="stat-label">Élèves inscrits</div>
        </div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon">🏫</div>
        <div class="stat-info">
            <div class="stat-value">{{ $totalClasses }}</div>
            <div class="stat-label">Classes actives</div>
        </div>
    </div> -->
</div>

<div class="page-header" style="margin-top:8px;">
    <div class="page-header-left">
        <h1>Elève(s) en retard de paiements</h1>
    </div>
</div>


<div class="card">
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
                @php $cpt=0; @endphp
                @forelse($eleves as $eleve)
                @php
                    $totalPaye  = $eleve->totalPaye();
                    $resteApayer= $eleve->resteAPayer();
                    $fraisTotal = $eleve->classe->frais ?? 0;
                    $stt = $fraisTotal > 0 ? min(100, round(($totalPaye/$fraisTotal)*100)) : 0;
                @endphp
                @if($resteApayer>0)
                @php $cpt=$cpt+1; @endphp
                    <tr data-name="{{ strtolower($eleve->nom . ' ' . $eleve->prenom) }}">
                        <td>{{ $cpt }}</td>
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
                                <a href="{{ route('paiements.index') }}" 
                                   class="btn btn-info btn-sm" title="Encaissé">Payer</a>
                            </div>
                        </td>
                    </tr>
                @endif
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

@endsection
