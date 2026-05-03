<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Orders Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>ElectroHub Orders Report</h1>
    <p>Generated at: {{ $generatedAt }}</p>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Discount</th>
                <th>Tax</th>
                <th>Total</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->customer_email }}</td>
                    <td>{{ strtoupper($order->payment_status) }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>${{ number_format((float) $order->discount_amount, 2) }}</td>
                    <td>${{ number_format((float) $order->tax_amount, 2) }}</td>
                    <td>${{ number_format((float) $order->grand_total, 2) }}</td>
                    <td>{{ $order->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
