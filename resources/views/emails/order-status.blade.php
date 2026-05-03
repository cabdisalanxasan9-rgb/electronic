<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Update {{ $order->order_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #111827; background: #f8fafc; padding: 20px;">
    <div style="max-width: 680px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 22px;">
        <h1 style="margin: 0 0 8px;">Order Update</h1>
        <p style="margin: 0 0 16px;">Order Number: <strong>{{ $order->order_number }}</strong></p>
        <p style="margin: 0 0 16px;">{{ $message }}</p>
        <p style="margin: 0;">Status: <strong>{{ ucfirst($order->status) }}</strong></p>
        <p style="margin: 0;">Payment: <strong>{{ strtoupper($order->payment_status) }}</strong></p>
        <p style="margin: 16px 0 0;">Thank you for shopping with ElectroHub.</p>
    </div>
</body>
</html>
