<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $order->type }} - {{ $order->order_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; font-size: 16px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .company-info { font-size: 24px; font-weight: bold; color: #4F46E5; }
        .invoice-title { font-size: 28px; text-transform: uppercase; color: #111; }
        .details-table { width: 100%; text-align: left; border-collapse: collapse; margin-bottom: 40px; }
        .details-table th { background: #F9FAFB; padding: 12px; border-bottom: 2px solid #E5E7EB; }
        .details-table td { padding: 12px; border-bottom: 1px solid #F3F4F6; }
        .totals { float: right; width: 300px; }
        .totals-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .grand-total { font-size: 20px; font-weight: bold; border-top: 2px solid #4F46E5; margin-top: 10px; padding-top: 10px; }
        .footer { margin-top: 100px; font-size: 12px; color: #6B7280; text-align: center; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table style="width: 100%; margin-bottom: 40px;">
            <tr>
                <td style="font-size: 28px; font-weight: bold; color: #4F46E5;">
                    NEXUS INVENTORY
                </td>
                <td style="text-align: right;">
                    <span style="font-size: 24px; color: #111;">{{ strtoupper($order->type) }}</span><br>
                    #{{ $order->order_number }}<br>
                    Date: {{ $order->created_at->format('M d, Y') }}
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 40px;">
            <tr>
                <td>
                    <strong>Bill To:</strong><br>
                    {{ $order->customer_name }}<br>
                    {{ $order->customer_email }}
                </td>
                <td style="text-align: right;">
                    <strong>Status:</strong> {{ $order->status }}<br>
                    <strong>Currency:</strong> {{ $order->currency }}
                </td>
            </tr>
        </table>

        <table class="details-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->orderItems as $item)
                <tr>
                    <td>{{ $item->variant->product->name }} ({{ $item->variant->name }})</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td>General Inventory Order ({{ $order->order_number }})</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;">{{ number_format($order->total_amount - $order->tax_amount + $order->discount_amount, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($order->total_amount - $order->tax_amount + $order->discount_amount, 2) }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="width: 100%; overflow: hidden;">
            <div style="float: right; width: 250px;">
                <table style="width: 100%;">
                    <tr>
                        <td>Subtotal:</td>
                        <td style="text-align: right;">{{ number_format($order->total_amount - $order->tax_amount + $order->discount_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Discount:</td>
                        <td style="text-align: right;">-{{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Tax:</td>
                        <td style="text-align: right;">+{{ number_format($order->tax_amount, 2) }}</td>
                    </tr>
                    <tr style="font-weight: bold; font-size: 18px; color: #4F46E5;">
                        <td style="padding-top: 10px;">Total:</td>
                        <td style="text-align: right; padding-top: 10px;">{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($order->notes)
        <div style="margin-top: 40px; padding: 15px; background: #F9FAFB; border-radius: 8px;">
            <strong>Notes:</strong><br>
            {{ $order->notes }}
        </div>
        @endif

        <div class="footer">
            Thank you for your business!<br>
            Generated by Nexus Inventory Management System
        </div>
    </div>
</body>
</html>
