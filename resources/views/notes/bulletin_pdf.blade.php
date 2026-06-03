```blade
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de Notes — {{ $eleve->nom }} {{ $eleve->prenom }}</title>

    <style>
        body{
            font-family: DejaVu Sans, Arial, sans-serif;
            color:#333;
            font-size:13px;
            line-height:1.4;
        }

        /* ===== EN-TÊTE ===== */
        .header{
            text-align:center;
            margin-bottom:25px;
            border-bottom:2px double #333;
            padding-bottom:10px;
        }

        .school-name{
            font-size:18px;
            font-weight:bold;
            text-transform:uppercase;
            letter-spacing:1px;
        }

        .school-motto{
            font-size:11px;
            color:#64748b;
            margin-top:4px;
        }

        .doc-title{
            font-size:16px;
            font-weight:bold;
            background:#f1f5f9;
            padding:6px;
            margin-top:10px;
            border:1px solid #cbd5e1;
        }

        /* ===== INFOS ÉLÈVE ===== */
        .student-info{
            width:100%;
            margin-bottom:20px;
            border-collapse:collapse;
        }

        .student-info td{
            padding:5px 0;
        }

        /* ===== TABLEAU NOTES ===== */
        .table-notes{
            width:100%;
            border-collapse:collapse;
            margin-bottom:20px;
        }

        .table-notes th,
        .table-notes td{
            border:1px solid #333;
            padding:8px;
        }

        .table-notes th{
            background:#f8fafc;
            font-weight:bold;
        }

        .text-center{
            text-align:center;
        }

        .text-right{
            text-align:right;
        }

        .total-row{
            background:#f1f5f9;
            font-weight:bold;
        }

        .success{
            color:green;
            font-weight:bold;
        }

        .danger{
            color:red;
            font-weight:bold;
        }

        .footer{
            margin-top:30px;
            text-align:center;
            font-size:16px;
            color:black;
        }
    </style>
</head>
<body>

    {{-- =========================
         CALCUL DU RANG
    ========================== --}}
    @php
        $totalEleves = $eleve->classe->eleves->count();

        $currentRank = 1;
        $subCount = 0;
        $previousMoyenne = null;
        $displayRank = '-';

        $elevesClasse = $eleve->classe->eleves
            ->sortByDesc(fn($e) => $e->moyenneTrimestrielle($trimestre));

        foreach($elevesClasse as $item){

            $itemMoyenne = $item->moyenneTrimestrielle($trimestre);

            if($itemMoyenne !== null){

                if(
                    $previousMoyenne !== null &&
                    $itemMoyenne < $previousMoyenne
                ){
                    $currentRank += $subCount;
                    $subCount = 1;
                }else{
                    $subCount++;
                }

                $previousMoyenne = $itemMoyenne;

                if($item->id == $eleve->id){
                    $displayRank =
                        $currentRank .
                        ($currentRank == 1 ? 'er' : 'e');
                    break;
                }
            }
        }
    @endphp

    {{-- =========================
         EN-TÊTE
    ========================== --}}
    <div class="header">

        <div class="school-name">
            ÉCOLE PRIMAIRE PRIVÉE WENDBENEDO
        </div>

        <div class="school-motto">
            BURKINA FASO — La Patrie ou la Mort, Nous Vaincrons
        </div>

        <div class="doc-title">
            BULLETIN DE NOTES DU
            {{ $trimestre }}
            {{ $trimestre == 1 ? 'Er' : 'Eme' }}
            TRIMESTRE
        </div>

    </div>

    {{-- =========================
         INFORMATIONS ÉLÈVE
    ========================== --}}
    <table class="student-info">
        <tr>
            <td>
                <strong>Nom :</strong>
                {{ $eleve->nom }}
            </td>

            <td>
                <strong>Prénom :</strong>
                {{ $eleve->prenom }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Classe :</strong>
                {{ $eleve->classe->nom ?? '—' }}
            </td>

            <td>
                <strong>Genre :</strong>
                {{ $eleve->genre == 'M' ? 'Masculin' : 'Féminin' }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Année scolaire :</strong>
                {{ date('Y') }}-{{ date('Y') + 1 }}
            </td>

            <td>
                <strong>Rang :</strong>
                {{ $displayRank }}
                sur
                {{ $totalEleves }}
                élève(s)
            </td>
        </tr>
    </table>

    {{-- =========================
         TABLEAU DES NOTES
    ========================== --}}
    <table class="table-notes">
        <thead>
            <tr>
                <th>Matière</th>
                <th class="text-center">Note Obtenue</th>
                <th class="text-center">Barème</th>
                <th class="text-center">Observation</th>
            </tr>
        </thead>

        <tbody>

            @foreach($notes as $note)

                @php
                    $bareme = $baremes[$note->matiere_id] ?? 10;

                    $sur10 = $bareme > 0
                        ? round(($note->valeurs / $bareme) * 10, 2)
                        : 0;

                    $mention =
                        $sur10 >= 8 ? 'Très Bien' :
                        ($sur10 >= 6 ? 'Bien' :
                        ($sur10 >= 5 ? 'Passable' :
                        'Insuffisant'));
                @endphp

                <tr>

                    <td>
                        {{ $note->matiere->nom ?? '—' }}
                    </td>

                    <td class="text-center">
                        {{ $note->valeurs }}
                    </td>

                    <td class="text-center">
                        / {{ $bareme }}
                    </td>

                    <td class="text-center">

                        <span class="{{ $sur10 >= 5 ? 'success' : 'danger' }}">
                            {{ $mention }}
                        </span>

                    </td>

                </tr>

            @endforeach

            {{-- MOYENNE --}}
            <tr class="total-row">

                <td colspan="2">
                    MOYENNE GÉNÉRALE
                </td>

                <td colspan="2" class="text-center">

                    @if($moyenneGenerale !== null)

                        <span class="{{ $moyenneGenerale >= 5 ? 'success' : 'danger' }}">
                            {{ number_format($moyenneGenerale,2) }}/10
                        </span>

                    @else

                        N/A

                    @endif

                </td>

            </tr>

            {{-- RANG --}}
            <tr class="total-row">

                <td colspan="2">
                    RANG
                </td>

                <td colspan="2" class="text-center">
                    {{ $displayRank }}
                    sur
                    {{ $totalEleves }}
                    élève(s)
                </td>

            </tr>

            {{-- DÉCISION --}}
            <tr class="total-row">

                <td colspan="2">
                    DÉCISION DU CONSEIL
                </td>

                <td colspan="2" class="text-center">

                    @if($moyenneGenerale !== null)

                        <span class="{{ $moyenneGenerale >= 5 ? 'success' : 'danger' }}">
                            {{ $moyenneGenerale >= 5 ? 'ADMIS(E)' : 'NON ADMIS(E)' }}
                        </span>

                    @else

                        N/A

                    @endif

                </td>

            </tr>

        </tbody>
    </table>

    <div class="footer">
        Fait a Ouagadougou le {{ date('d/m/Y à H:i') }}
    </div>

    <table style="width:100%; margin-top:50px;">
        <tr>

            <td style="width:50%; text-align:center;">
                <strong>L'Enseignant(e)</strong>

                <div style="height:80px;"></div>

                ______________________
            </td>

            <td style="width:50%; text-align:center;">
                <strong>Le Directeur</strong>

                <div style="height:80px;"></div>

                ______________________
            </td>

        </tr>
    </table>

</body>
</html>
