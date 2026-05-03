<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\StockMovement;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index', [
            'stockValue' => ProductVariant::sum(\DB::raw('stock_qty * cost_price')),
            'totalOrders' => Order::count(),
            'recentMovements' => StockMovement::count()
        ]);
    }
}
