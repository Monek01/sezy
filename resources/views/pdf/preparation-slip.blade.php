<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; }
        h1 { color: #004aad; border-bottom: 3px solid #3180d0; padding-bottom: 10px; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #f0f5fb; }
        .checkbox { width: 24px; height: 24px; border: 2px solid #333; }
    </style>
</head>
<body>
    <h1>Bon de préparation — {{ $order->order_number }}</h1>
    <div class="info">
        <strong>Client :</strong> {{ $order->customer_name }} ({{ $order->customer_phone }})<br>
        <strong>Mode :</strong> {{ $order->delivery_method === 'click_and_collect' ? 'Retrait en point relais' : 'Livraison à domicile' }}<br>
        <strong>Date :</strong> {{ $order->created_at->format('d/m/Y H:i') }}
    </div>
    <table>
        <thead>
            <tr><th>✓</th><th>Article</th><th>SKU</th><th>Quantité</th></tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td><div class="checkbox"></div></td>
                <td>{{ $item->product_title }} @if($item->variant_label) ({{ $item->variant_label }}) @endif</td>
                <td>{{ $item->product_sku }}</td>
                <td>{{ $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
