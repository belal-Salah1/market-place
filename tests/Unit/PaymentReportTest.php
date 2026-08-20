<?php

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('should calculate total payments in every status', function () {
    $paymentReportService = new \App\Services\PaymentReportService;
    $payment = Payment::factory()->create(['status' => 'completed']);
    $payment = Payment::factory()->create(['status' => 'completed']);
    $payment = Payment::factory()->create(['status' => 'completed']);
    $payment = Payment::factory()->create(['status' => 'pending']);
    $payment = Payment::factory()->create(['status' => 'failed']);
    $payment = Payment::factory()->create(['status' => 'failed']);
    $result = $paymentReportService->totalCountInPaymentStatues()
        ->map(fn ($item) => (array) $item)
        ->toArray();
    // The query groups without an ORDER BY, so row order is not guaranteed.
    expect($result)->toEqualCanonicalizing([
        ['status' => 'completed', 'total' => 3],
        ['status' => 'pending', 'total' => 1],
        ['status' => 'failed', 'total' => 2],
    ]);
});
