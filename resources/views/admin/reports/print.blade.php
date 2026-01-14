<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Report') }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 24px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        h2 { font-size: 16px; margin-top: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; text-align: left; }
        th { background: #f5f5f5; }
        .meta { color: #555; font-size: 12px; }
    </style>
</head>
<body>
    <h1>{{ __('Report') }}: {{ ucfirst($type) }}</h1>
    <div class="meta">
        {{ __('Date range') }}: {{ $start?->toDateString() ?? __('All time') }} - {{ $end?->toDateString() ?? __('All time') }}
    </div>

    @if ($type === 'sales')
        <h2>{{ __('Summary') }}</h2>
        <table>
            <tr><th>{{ __('Metric') }}</th><th>{{ __('Value') }}</th></tr>
            <tr><td>{{ __('Total orders') }}</td><td>{{ $reportData['total_orders'] ?? 0 }}</td></tr>
            <tr><td>{{ __('Revenue') }}</td><td>{{ number_format((float) ($reportData['revenue'] ?? 0), 2) }}</td></tr>
            <tr><td>{{ __('Paid orders') }}</td><td>{{ $reportData['paid_orders'] ?? 0 }}</td></tr>
            <tr><td>{{ __('Unpaid orders') }}</td><td>{{ $reportData['unpaid_orders'] ?? 0 }}</td></tr>
        </table>

        <h2>{{ __('Top products') }}</h2>
        <table>
            <tr><th>{{ __('Product') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Revenue') }}</th></tr>
            @foreach ($reportData['top_products'] ?? [] as $item)
                <tr>
                    <td>{{ $item->product?->name ?? __('Unknown') }}</td>
                    <td>{{ $item->total_qty ?? 0 }}</td>
                    <td>{{ number_format((float) ($item->total_revenue ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </table>
    @elseif ($type === 'products')
        <h2>{{ __('Low stock') }}</h2>
        <table>
            <tr><th>{{ __('Product') }}</th><th>{{ __('Stock') }}</th></tr>
            @foreach ($reportData['low_stock'] ?? [] as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->stock }}</td>
                </tr>
            @endforeach
        </table>

        <h2>{{ __('Best sellers') }}</h2>
        <table>
            <tr><th>{{ __('Product') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Revenue') }}</th></tr>
            @foreach ($reportData['best_sellers'] ?? [] as $item)
                <tr>
                    <td>{{ $item->product?->name ?? __('Unknown') }}</td>
                    <td>{{ $item->total_qty ?? 0 }}</td>
                    <td>{{ number_format((float) ($item->total_revenue ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <h2>{{ __('New customers') }}</h2>
        <table>
            <tr><th>{{ __('Metric') }}</th><th>{{ __('Value') }}</th></tr>
            <tr><td>{{ __('New customers') }}</td><td>{{ $reportData['new_customers'] ?? 0 }}</td></tr>
        </table>

        <h2>{{ __('Top spenders') }}</h2>
        <table>
            <tr><th>{{ __('Customer') }}</th><th>{{ __('Total spent') }}</th><th>{{ __('Orders') }}</th></tr>
            @foreach ($reportData['top_spenders'] ?? [] as $item)
                <tr>
                    <td>{{ $item->user?->name ?? __('Unknown') }}</td>
                    <td>{{ number_format((float) ($item->total_spent ?? 0), 2) }}</td>
                    <td>{{ $item->orders_count ?? 0 }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>
