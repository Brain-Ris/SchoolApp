<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de Paiement - {{ $eleve->nom }}</title>
    <style>
        /* Styles CSS purs spécifiques pour l'impression PDF */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.4;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .institution {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
        }
        .doc-title {
            text-align: right;
            font-size: 22px;
            font-weight: bold;
            color: #3b82f6;
        }
        .info-section {
            margin-bottom: 30px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding: 5px 0;
            font-size: 15px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .data-table th {
            background-color: #1e293b;
            color: white;
            text-align: left;
            padding: 10px;
            font-size: 14px;
        }
        .data-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .total-box {
            float: right;
            width: 300px;
            background: #f1f5f9;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="institution">
                SchoolApp <br>
                <span style="font-size: 12px; color: #64748b; font-weight: normal; text-transform: none;">Application de Suivi de Scolarité</span>
            </td>
            <td class="doc-title">REÇU DE SCOLARITÉ</td>
        </tr>
    </table>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td style="width: 15%; font-weight: bold;">Élève :</td>
                <td style="font-size: 16px; font-weight: bold; color: #0f172a;">{{ $eleve->nom }} {{ $eleve->prenom }}</td>
                <td style="text-align: right; color: #64748b;">Date d'édition : {{ $date }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Classe :</td>
                <td>{{ $eleve->classe->nom }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Genre :</td>
                <td>{{ $eleve->genre }}</td>
                <td></td>
            </tr>
        </table>
    </div>

    <h3 style="margin-bottom: 10px; color: #1e293b;">Détail des versements effectués</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">N°</th>
                <th style="width: 50%;">Date du Versement</th>
                <th style="text-align: right; width: 40%;">Montant Versé</th>
            </tr>
        </thead>
        <tbody>
            @forelse($eleve->paiements as $index => $paiement)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $paiement->created_at->format('d/m/Y à H:i') }}</td>
                    <td style="text-align: right; font-weight: 500; color: #16a34a;">{{ number_format($paiement->montant, 0, ',', ' ') }} F CFA</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #64748b;">Aucun versement enregistré pour le moment.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-box">
        <table style="width: 100%;">
            <tr>
                <td style="padding: 4px 0; color: #475569;">Frais Scolarité :</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($eleve->classe->frais, 0, ',', ' ') }} F</td>
            </tr>
            <tr>
                <td style="padding: 4px 0; color: #16a34a; font-weight: 500;">Montant Total Payé :</td>
                <td style="text-align: right; color: #16a34a; font-weight: bold;">{{ number_format($totalPaye, 0, ',', ' ') }} F</td>
            </tr>
            <tr style="border-top: 1px solid #cbd5e1;">
                <td style="padding: 8px 0 0 0; color: #b91c1c; font-weight: bold; font-size: 15px;">Reste à Payer:</td>
                <td style="text-align: right; color: #b91c1c; font-weight: bold; font-size: 15px; padding-top: 8px;">{{ number_format($resteAPayer, 0, ',', ' ') }} F CFA</td>
            </tr>
        </table>
    </div>

    </div>
        <table style="width: 100%; margin-top: 150px; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top; font-size: 14px; color: #333;">
                    Fait à <strong>Ouagadougou</strong>, le {{ date('d/m/Y') }}
                </td>
                
                <td style="width: 50%; text-align: right; vertical-align: top; font-size: 14px; color: #333;">
                    <p style="margin: 0 0 50px 0; font-weight: bold; text-decoration: underline;">
                        Le Directeur de l'Établissement
                    </p>
                    <p style="font-size: 11px; color: #94a3b8; font-style: italic; margin-top: 60px;">
                        (Signature et cachet)
                    </p>
                </td>
            </tr>
        </table>

        <div class="footer">
            Document généré automatiquement par SchoolApp. Certifié conforme.<br>
        </div>

</body>
</html>