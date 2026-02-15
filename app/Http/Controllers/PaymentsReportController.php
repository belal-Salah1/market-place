<?php

namespace App\Http\Controllers;

use App\Services\PaymentReportService;
use Illuminate\Http\JsonResponse;

class PaymentsReportController extends Controller
{
    public function __construct(protected PaymentReportService $service)
    {
    }

    /**
     * GET /reports/payments/by-status
     * Returns payment count by status.
     */
    public function byStatus(): JsonResponse
    {
        $data = $this->service->totalCountInPaymentStatues();
        return response()->json($data);
    }

    /**
     * GET /reports/payments/by-method
     * Returns payment count by method.
     */
    public function byMethod(): JsonResponse
    {
        $data = $this->service->paymentMethodsStatus();
        return response()->json($data);
    }
}
