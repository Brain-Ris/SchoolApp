{{--
    VUE FRAGMENT : edit_fragment.blade.php
    
    Ce fichier est retourné par la route /notes/{eleve}/edit/{trimestre}/fragment
    Il ne contient PAS @extends('layouts.app') — seulement le formulaire.
    Il est injecté dans le modal via JavaScript (fetch).
--}}

<form method="POST" action="{{ route('notes.update', [$eleve->id, $trimestre]) }}">
    @csrf
    @method('PUT')

    <div class="modal-body">
        {{-- Info élève --}}
        <div style="background:var(--bg); border-radius:8px; padding:10px 14px; margin-bottom:16px; font-size:13px;">
            <strong>{{ $eleve->nom }} {{ $eleve->prenom }}</strong>
            — Classe : {{ $eleve->classe->nom }}
            — Trimestre {{ $trimestre }}
        </div>

        {{--
            MATIÈRES DE LA CLASSE AVEC LEURS BARÈMES
            $matieres = $classe->matieres (avec pivot->bareme)
            $notesExistantes = collection keyed par matiere_id
        --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:10px;">
            @foreach($matieres as $matiere)
            @php
                $bareme        = $matiere->pivot->bareme ?? 10;
                $noteExistante = $notesExistantes[$matiere->id] ?? null;
                $valeurs        = $noteExistante ? $noteExistante->valeurs : '';
            @endphp
            <div class="form-group">
                <label class="form-label">
                    {{ $matiere->nom }}
                    <span style="color:var(--text-muted); font-weight:400;">/{{ $bareme }}</span>
                </label>
                <input type="number"
                       name="notes[{{ $matiere->id }}]"
                       class="form-control"
                       placeholder="/{{ $bareme }}"
                       value="{{ $valeurs }}"
                       min="0" max="{{ $bareme }}"
                       step="0.01" required>
            </div>
            @endforeach
        </div>

        <p style="font-size:11px; color:var(--text-muted); margin-top:14px; padding-top:12px; border-top:1px solid var(--border);">
            Les notes vides ne seront pas modifiées. Maximum : selon le barème de chaque matière.
        </p>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit-note')">Annuler</button>
        <button type="submit" class="btn btn-warning">Mettre à jour</button>
    </div>
</form>
