<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color:#1f2937; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border-bottom: 1px solid #ddd; padding: 8px; text-align: left; }
        .total { font-size: 20px; margin-top: 12px; }
    </style>
</head>
<body>
    <h1>ElectroHub Invoice</h1>
    <p>Order: <strong>{{ $order->order_number }}</strong></p>
    <p>Customer: {{ $order->customer_name }} ({{ $order->customer_email }})</p>
    <p>Address: {{ $order->shipping_address }}</p>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>Subtotal: ${{ number_format((float) $order->sub_total, 2) }}</p>
    <p>Discount: -${{ number_format((float) $order->discount_amount, 2) }}</p>
    <p>Tax: ${{ number_format((float) $order->tax_amount, 2) }}</p>
    <p>Shipping: ${{ number_format((float) $order->shipping_amount, 2) }}</p>
    <p class="total">Grand Total: <strong>${{ number_format((float) $order->grand_total, 2) }}</strong></p>

    <script>window.print();</script>
</body>
</html>
