<?php

namespace App\Http\Controllers;

use App\Services\ReviewsReportService;
use Illuminate\Http\JsonResponse;

class ReviewsReportController extends Controller
{
    public function __construct(protected ReviewsReportService $service)
    {
    }

    /**
     * GET /reports/reviews
     * Returns average rating per product.
     */
    public function index(): JsonResponse
    {
        $data = $this->service->averageRatingPerProduct();
        return response()->json($data);
    }

    /**
     * GET /reports/reviews/by-customer
     * Returns total reviews per customer.
     */
    public function byCustomer(): JsonResponse
    {
        $data = $this->service->totalReviewsPerCustomer();
        return response()->json($data);
    }
}
