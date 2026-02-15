<?php

namespace App\Http\Controllers;

use App\Services\ProductReportsServices;
use Illuminate\Http\JsonResponse;

class ProductsReportController extends Controller
{
    public function __construct(protected ProductReportsServices $service)
    {
    }

    /**
     * GET /reports/products
     * Returns total products count.
     */
    public function index(): JsonResponse
    {
        $data = $this->service->totalProducts();
        return response()->json($data);
    }

    /**
     * GET /reports/products/revenue
     * Returns total products revenue.
     */
    public function revenue(): JsonResponse
    {
        $data = $this->service->totalRevenue();
        return response()->json($data);
    }
}
