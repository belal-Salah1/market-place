<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('customer_id', $request->user()->id)
            ->with('items.product')
            ->latest()
            ->take(5)
            ->get();

        $orderCount = Order::where('customer_id', $request->user()->id)->count();

        return Inertia::render('Customer/Dashboard', [
            'user' => $request->user(),
            'recentOrders' => $orders,
            'orderCount' => $orderCount,
        ]);
    }

    public function products(Request $request)
    {
        $query = Product::with(['category', 'vendor'])
            ->where('stock', '>', 0);

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $products = $query->latest()->get();
        $categories = Category::withCount('products')->get();

        return Inertia::render('Customer/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'category' => $request->category,
                'search' => $request->search,
            ],
        ]);
    }

    public function productShow(Product $product)
    {
        $product->load(['category', 'vendor']);

        return Inertia::render('Customer/Products/Show', [
            'product' => $product,
        ]);
    }

    public function orders(Request $request)
    {
        $orders = Order::where('customer_id', $request->user()->id)
            ->with(['items.product', 'payment'])
            ->latest()
            ->get();

        return Inertia::render('Customer/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function orderShow(Order $order)
    {
        abort_unless($order->customer_id === auth()->id(), 403);

        $order->load(['items.product', 'payment']);

        return Inertia::render('Customer/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function storeOrder(StoreOrderRequest $request, CartService $cartService)
    {
        $paymentMethod = $request->validated('payment_method');
        $cart = $cartService->loadedForCustomer($request->user());

        if ($cart->items->isEmpty()) {
            return redirect()->route('customer.cart.index')
                ->with('error', 'Your cart is empty.');
        }

        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                return back()->withErrors([
                    'items' => "Not enough stock for {$item->product->name}. Available: {$item->product->stock}",
                ]);
            }
        }

        return DB::transaction(function () use ($cart, $cartService, $paymentMethod, $request) {
            $totalPrice = 0;
            $itemsData = [];

            foreach ($cart->items as $item) {
                $product = $item->product;
                $totalPrice += $product->price * $item->quantity;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                ];

                $product->decrement('stock', $item->quantity);
            }

            $order = Order::create([
                'customer_id' => $request->user()->id,
                'total_price' => $totalPrice,
                'status' => OrderStatus::PENDING,
            ]);

            $order->items()->createMany($itemsData);

            $order->payment()->create([
                'amount' => $totalPrice,
                'method' => $paymentMethod,
                'status' => PaymentStatus::COMPLETED,
            ]);

            $cartService->clear($cart);

            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Order placed successfully!');
        });
    }
}
