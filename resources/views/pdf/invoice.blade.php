<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        .header { background: #004aad; color: #fff; padding: 20px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; font-size: 12px; opacity: 0.85; }
        .meta { display: table; width: 100%; margin-bottom: 20px; }
        .meta .col { display: table-cell; width: 50%; vertical-align: top; }
        .meta h3 { font-size: 11px; text-transform: uppercase; color: #3180d0; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f0f5fb; text-align: left; padding: 8px; font-size: 11px; text-transform: uppercase; color: #004aad; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .totals { width: 280px; margin-left: auto; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .grand-total td { font-weight: bold; font-size: 14px; border-top: 2px solid #004aad; padding-top: 8px; }
        .footer { margin-top: 40px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SEZY</h1>
        <p>Facture {{ $order->order_number }} — {{ $order->created_at->format('d/m/Y') }}</p>
    </div>

    <div class="meta">
        <div class="col">
            <h3>Facturé à</h3>
            <p>
                {{ $order->customer_name }}<br>
                {{ $order->customer_phone }}<br>
                {{ $order->customer_email }}
            </p>
        </div>
        <div class="col">
            <h3>Livraison</h3>
            <p>
                @if($order->delivery_method === 'click_and_collect')
                    Retrait en point relais
                @else
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_district }}, {{ $order->shipping_city }}
                @endif
            </p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th>Référence</th>
                <th>Prix unitaire</th>
                <th>Qté</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_title }} @if($item->variant_label) ({{ $item->variant_label }}) @endif</td>
                <td>{{ $item->product_sku }}</td>
                <td>{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->line_total, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Sous-total</td><td>{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</td></tr>
        <tr><td>Livraison</td><td>{{ number_format($order->shipping_fee, 0, ',', ' ') }} FCFA</td></tr>
        @if($order->discount_amount > 0)
        <tr><td>Remise{{ $order->coupon_code ? " ({$order->coupon_code})" : '' }}</td><td>-{{ number_format($order->discount_amount, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        @if($order->loyalty_points_used > 0)
        <tr><td>Points fidélité utilisés</td><td>-{{ number_format($order->loyalty_points_used, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        <tr class="grand-total"><td>Total TTC</td><td>{{ number_format($order->total, 0, ',', ' ') }} FCFA</td></tr>
    </table>

    <div class="footer">
        SEZY — Document généré automatiquement le {{ now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
