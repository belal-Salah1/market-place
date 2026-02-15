<?php

namespace App\Http\Controllers;

use App\Services\OrderReportsService;
use Illuminate\Http\JsonResponse;

class OrdersReportController extends Controller
{
    public function __construct(protected OrderReportsService $service)
    {
    }

    /**
     * GET /reports/orders
     * Returns total orders count.
     */
    public function index(): JsonResponse
    {
        $data = $this->service->ordersCount();
        return response()->json($data);
    }

    /**
     * GET /reports/orders/daily
     * Returns daily orders count.
     */
    public function daily(): JsonResponse
    {
        $data = $this->service->dailyOrdersReport();
        return response()->json($data);
    }

    /**
     * GET /reports/orders/weekly
     * Returns weekly orders count.
     */
    public function weekly(): JsonResponse
    {
        $data = $this->service->weeklyOrdersReport();
        return response()->json($data);
    }

    /**
     * GET /reports/orders/monthly
     * Returns monthly orders count.
     */
    public function monthly(): JsonResponse
    {
        $data = $this->service->monthlyOrdersReport();
        return response()->json($data);
    }

    /**
     * GET /reports/orders/total-price
     * Returns total orders price.
     */
    public function totalPrice(): JsonResponse
    {
        $data = $this->service->ordersTotalPrice();
        return response()->json($data);
    }

    /**
     * GET /reports/orders/average-price
     * Returns average order price.
     */
    public function averagePrice(): JsonResponse
    {
        $data = $this->service->averageOrderPrice();
        return response()->json($data);
    }

    /**
     * GET /reports/orders/by-status
     * Returns orders count by status.
     */
    public function byStatus(): JsonResponse
    {
        $data = $this->service->ordersCountByStatus();
        return response()->json($data);
    }

    /**
     * GET /reports/orders/price-by-status
     * Returns orders total price by status.
     */
    public function priceByStatus(): JsonResponse
    {
        $data = $this->service->ordersTotalPriceByStatus();
        return response()->json($data);
    }
}
