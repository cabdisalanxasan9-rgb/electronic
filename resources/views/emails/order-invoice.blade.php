<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #1f2937; background: #f8fafc; padding: 20px;">
    <div style="max-width: 720px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 22px;">
        <h1 style="margin: 0 0 10px;">ElectroHub Invoice</h1>
        <p style="margin: 0 0 20px; color: #475569;">Order Number: <strong>{{ $order->order_number }}</strong></p>

        <h3 style="margin-bottom: 8px;">Customer</h3>
        <p style="margin: 0;">{{ $order->customer_name }}</p>
        <p style="margin: 0;">{{ $order->customer_email }}</p>
        <p style="margin: 0 0 20px;">{{ $order->shipping_address }}</p>

        <h3 style="margin-bottom: 8px;">Items</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; border-bottom: 1px solid #e5e7eb; padding: 8px 0;">Item</th>
                    <th style="text-align: right; border-bottom: 1px solid #e5e7eb; padding: 8px 0;">Qty</th>
                    <th style="text-align: right; border-bottom: 1px solid #e5e7eb; padding: 8px 0;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td style="padding: 8px 0;">{{ $item->product_name }}</td>
                        <td style="padding: 8px 0; text-align: right;">{{ $item->quantity }}</td>
                        <td style="padding: 8px 0; text-align: right;">${{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 12px;">
            <p style="margin: 4px 0;">Subtotal: <strong>${{ number_format((float) $order->sub_total, 2) }}</strong></p>
            <p style="margin: 4px 0;">Discount: <strong>- ${{ number_format((float) $order->discount_amount, 2) }}</strong></p>
            <p style="margin: 4px 0;">Tax: <strong>${{ number_format((float) $order->tax_amount, 2) }}</strong></p>
            <p style="margin: 4px 0;">Shipping: <strong>${{ number_format((float) $order->shipping_amount, 2) }}</strong></p>
            <p style="margin: 8px 0 0; font-size: 18px;">Grand Total: <strong>${{ number_format((float) $order->grand_total, 2) }}</strong></p>
        </div>
    </div>
</body>
</html>
