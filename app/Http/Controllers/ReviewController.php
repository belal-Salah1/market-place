<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function create(Product $product)
    {
        $customerId = auth()->id();

        $hasPurchased = Order::where('customer_id', $customerId)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        abort_unless($hasPurchased, 403, 'You can only review products you have purchased.');

        $existingReview = Review::where('product_id', $product->id)
            ->where('customer_id', $customerId)
            ->first();

        return Inertia::render('Customer/Reviews/Create', [
            'product' => $product->load('vendor'),
            'existingReview' => $existingReview,
        ]);
    }

    public function store(StoreReviewRequest $request, Product $product)
    {
        $customerId = $request->user()->id;

        $hasPurchased = Order::where('customer_id', $customerId)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        abort_unless($hasPurchased, 403);

        Review::updateOrCreate(
            ['product_id' => $product->id, 'customer_id' => $customerId],
            $request->validated()
        );

        return redirect()->route('customer.products.show', $product->id)
            ->with('success', 'Review submitted successfully');
    }

    public function vendorReviews(Request $request)
    {
        $productIds = $request->user()->products()->pluck('id');

        $reviews = Review::whereIn('product_id', $productIds)
            ->with(['product', 'customer'])
            ->latest()
            ->get();

        $avgRating = $reviews->avg('rating');

        return Inertia::render('Vendor/Reviews/Index', [
            'reviews' => $reviews,
            'avgRating' => $avgRating ? round($avgRating, 1) : null,
        ]);
    }
}
