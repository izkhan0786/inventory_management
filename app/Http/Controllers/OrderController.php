<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index()
    {
        return Inertia::render('Orders/Index', [
            'orders' => Order::query()
                ->with('user:id,name,email')
                ->withCount('orderItems')
                ->latest()
                ->paginate(12),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:Sales Order,Quotation,Purchase Order',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'status' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $prefix = match($validated['type']) {
            'Quotation' => 'QT-',
            'Purchase Order' => 'PO-',
            default => 'SO-',
        };

        $orderNumber = $prefix . strtoupper(uniqid());

        Order::create(array_merge($validated, [
            'order_number' => $orderNumber,
            'user_id' => $request->user()->id,
        ]));

        return to_route('orders.index')->with('success', 'Order created successfully.');
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:Sales Order,Quotation,Purchase Order',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'status' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);

        return to_route('orders.index')->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return to_route('orders.index')->with('success', 'Order deleted successfully.');
    }

    public function downloadPdf(Order $order)
    {
        $order->load(['orderItems.variant.product', 'user']);
        
        $pdf = Pdf::loadView('pdf.invoice', compact('order'));
        
        return $pdf->download($order->type . '-' . $order->order_number . '.pdf');
    }
}
