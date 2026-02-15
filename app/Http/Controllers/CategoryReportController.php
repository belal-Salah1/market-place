<?php

namespace App\Http\Controllers;

use App\Services\CategoryReportService;
use Illuminate\Http\JsonResponse;

class CategoryReportController extends Controller
{
    public function __construct(protected CategoryReportService $service)
    {
    }

    /**
     * GET /reports/categories
     * Returns total categories count.
     */
    public function index(): JsonResponse
    {
        $data = $this->service->totalCategories();
        return response()->json($data);
    }

    /**
     * GET /reports/categories/products
     * Returns total products sold in each category.
     */
    public function productsSold(): JsonResponse
    {
        $data = $this->service->totalProductsSoldinOneCategory();
        return response()->json($data);
    }

    /**
     * GET /reports/categories/revenue
     * Returns total revenue per category.
     */
    public function revenue(): JsonResponse
    {
        $data = $this->service->totalProductsRevenueInOneCategory();
        return response()->json($data);
    }

    /**
     * GET /reports/categories/parent
     * Returns total parent categories.
     */
    public function parent(): JsonResponse
    {
        $data = $this->service->totalParentCategories();
        return response()->json($data);
    }

    /**
     * GET /reports/categories/sub
     * Returns total sub categories.
     */
    public function sub(): JsonResponse
    {
        $data = $this->service->totalSubCategories();
        return response()->json($data);
    }
}
