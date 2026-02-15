<?php

namespace App\Http\Controllers;

use App\Services\CustomersReportService;
use Illuminate\Http\JsonResponse;

class CustomersReportController extends Controller
{
    public function __construct(protected CustomersReportService $service)
    {
    }

    /**
     * GET /reports/customers
     * Returns total customers count.
     */
    public function index(): JsonResponse
    {
        $data = $this->service->totalCountOfCustomers();
        return response()->json($data);
    }

    /**
     * GET /reports/customers/by-role
     * Returns customers count by role.
     */
    public function byRole(): JsonResponse
    {
        $data = $this->service->totalCountOfCustmersByRole();
        return response()->json($data);
    }

    /**
     * GET /reports/customers/top-vendors
     * Returns top performance vendors.
     */
    public function topVendors(): JsonResponse
    {
        $data = $this->service->topPerformanceVendors();
        return response()->json($data);
    }

    /**
     * GET /reports/customers/top-customers
     * Returns top performance customers.
     */
    public function topCustomers(): JsonResponse
    {
        $data = $this->service->topPerformanceCustomers();
        return response()->json($data);
    }

    /**
     * GET /reports/customers/top-all
     * Returns top performance users overall.
     */
    public function topAll(): JsonResponse
    {
        $data = $this->service->topPerformanceAtAll();
        return response()->json($data);
    }
}
