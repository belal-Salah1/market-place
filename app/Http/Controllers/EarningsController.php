<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EarningsController extends Controller
{
    public function index(Request $request)
    {
        $productIds = Product::where('vendor_id', $request->user()->id)->pluck('id');

        $totalEarnings = OrderItem::whereIn('product_id', $productIds)
            ->sum(DB::raw('price * quantity'));

        $monthlyEarnings = OrderItem::whereIn('product_id', $productIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum(DB::raw('price * quantity'));

        $topProducts = OrderItem::whereIn('product_id', $productIds)
            ->select('product_id', DB::raw('SUM(price * quantity) as revenue'), DB::raw('SUM(quantity) as units_sold'))
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('revenue')
            ->take(10)
            ->get();

        $recentSales = OrderItem::whereIn('product_id', $productIds)
            ->with(['product', 'order.customer'])
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Vendor/Earnings/Index', [
            'totalEarnings' => round($totalEarnings, 2),
            'monthlyEarnings' => round($monthlyEarnings, 2),
            'topProducts' => $topProducts,
            'recentSales' => $recentSales,
        ]);
    }
}
