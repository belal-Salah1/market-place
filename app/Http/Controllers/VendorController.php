<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = $request->user()->id;
        $productIds = Product::where('vendor_id', $vendorId)->pluck('id');

        $totalSales = OrderItem::whereIn('product_id', $productIds)
            ->sum(\DB::raw('price * quantity'));

        $productCount = $productIds->count();

        $pendingOrders = Order::whereHas('items', fn ($q) => $q->whereIn('product_id', $productIds))
            ->where('status', 'pending')
            ->count();

        $avgRating = Review::whereIn('product_id', $productIds)->avg('rating');

        $recentOrders = Order::whereHas('items', fn ($q) => $q->whereIn('product_id', $productIds))
            ->with(['items' => fn ($q) => $q->whereIn('product_id', $productIds)->with('product'), 'customer'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Vendor/Dashboard', [
            'user' => $request->user(),
            'stats' => [
                'totalSales' => round($totalSales, 2),
                'productCount' => $productCount,
                'pendingOrders' => $pendingOrders,
                'avgRating' => $avgRating ? round($avgRating, 1) : null,
            ],
            'recentOrders' => $recentOrders,
        ]);
    }

    public function orders(Request $request)
    {
        $vendorId = $request->user()->id;
        $productIds = Product::where('vendor_id', $vendorId)->pluck('id');

        $orders = Order::whereHas('items', fn ($q) => $q->whereIn('product_id', $productIds))
            ->with(['items' => fn ($q) => $q->whereIn('product_id', $productIds)->with('product'), 'customer', 'payment'])
            ->latest()
            ->get();

        return Inertia::render('Vendor/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function orderShow(Order $order)
    {
        $vendorId = auth()->id();
        $productIds = Product::where('vendor_id', $vendorId)->pluck('id');

        abort_unless(
            $order->items()->whereIn('product_id', $productIds)->exists(),
            403
        );

        $order->load(['items' => fn ($q) => $q->whereIn('product_id', $productIds)->with('product'), 'customer', 'payment']);

        return Inertia::render('Vendor/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function pendingApproval(Request $request)
    {
        return Inertia::render('Auth/PendingApproval', ['user' => $request->user()]);
    }
}
