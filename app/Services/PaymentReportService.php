<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PaymentReportService
{
    public function totalCountInPaymentStatues(){
    $totalCountInPaymentStatues= DB::table('payments')->select('status', DB::raw('count(*) as total'))
    ->groupBy('status')
    ->get();
    return $totalCountInPaymentStatues;
    }
    
    public function paymentMethodsStatus(){
        $paymentMethodsStatus = DB::table('payments')->select('method', DB::raw('count(*) as total'))
        ->groupBy('method')
        ->get();
        return $paymentMethodsStatus;
    }
}