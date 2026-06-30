<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; }
        .header { background: #004aad; color: #fff; padding: 20px 24px; margin-bottom: 24px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: bold; }
        .header p { margin: 4px 0 0; font-size: 11px; opacity: 0.8; }
        .kpis { display: table; width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 20px; }
        .kpi { display: table-cell; background: #f0f5fb; border-radius: 8px; padding: 12px 16px; text-align: center; }
        .kpi .label { font-size: 10px; color: #3180d0; text-transform: uppercase; }
        .kpi .value { font-size: 16px; font-weight: bold; color: #004aad; margin-top: 4px; }
        table.transactions { width: 100%; border-collapse: collapse; }
        table.transactions thead tr { background: #f0f5fb; }
        table.transactions th { padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #004aad; border-bottom: 2px solid #d0e0f5; }
        table.transactions td { padding: 7px 10px; border-bottom: 1px solid #eef2f8; font-size: 10px; }
        table.transactions tr:hover td { background: #f8fbff; }
        .badge-paid { color: #15803d; background: #dcfce7; padding: 2px 7px; border-radius: 20px; font-size: 9px; font-weight: bold; }
        .badge-pending { color: #92400e; background: #fef3c7; padding: 2px 7px; border-radius: 20px; font-size: 9px; font-weight: bold; }
        .badge-refunded { color: #1d4ed8; background: #dbeafe; padding: 2px 7px; border-radius: 20px; font-size: 9px; font-weight: bold; }
        .badge-other { color: #374151; background: #f3f4f6; padding: 2px 7px; border-radius: 20px; font-size: 9px; }
        .footer { margin-top: 32px; font-size: 9px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SEZY — Rapport Comptable</h1>
        <p>Période : {{ ucfirst($period) }} &nbsp;|&nbsp; Du {{ $start->format('d/m/Y') }} au {{ $end->format('d/m/Y') }}</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="kpis">
        <div class="kpi">
            <div class="label">Chiffre d'affaires</div>
            <div class="value">{{ number_format($revenue, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="kpi">
            <div class="label">Remboursements</div>
            <div class="value">{{ number_format($refunds, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="kpi">
            <div class="label">Net</div>
            <div class="value">{{ number_format($revenue - $refunds, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="kpi">
            <div class="label">Transactions</div>
            <div class="value">{{ $transactions->count() }}</div>
        </div>
    </div>

    <table class="transactions">
        <thead>
            <tr>
                <th>N° Commande</th>
                <th>Client</th>
                <th>Paiement</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
            <tr>
                <td><strong>{{ $t->order_number }}</strong></td>
                <td>{{ trim($t->customer_name) }}</td>
                <td>{{ str_replace('_', ' ', ucfirst($t->payment_method)) }}</td>
                <td>{{ number_format($t->total, 0, ',', ' ') }} FCFA</td>
                <td>
                    @if($t->payment_status === 'paid')
                        <span class="badge-paid">Payé</span>
                    @elseif($t->payment_status === 'refunded')
                        <span class="badge-refunded">Remboursé</span>
                    @elseif($t->payment_status === 'pending')
                        <span class="badge-pending">En attente</span>
                    @else
                        <span class="badge-other">{{ $t->payment_status }}</span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:20px;color:#9ca3af;">Aucune transaction sur cette période.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        SEZY — agencesezy@gmail.com &nbsp;|&nbsp; Rapport généré automatiquement &nbsp;|&nbsp; Toutes valeurs en FCFA
    </div>
</body>
</html>
