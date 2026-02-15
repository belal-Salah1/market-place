<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OrderReportsService
{


public function ordersCount(){
    $ordersCount = DB::table('orders')->count();
    return $ordersCount;
}

public function dailyOrdersReport(): int
{
    $dailyOrdersCount = DB::table('orders')->where('created_at', '>=', now()->startOfDay())->count();
    return $dailyOrdersCount;
}


public function weeklyOrdersReport(): int
{
    $weeklyOrdersCount = DB::table('orders')->where('created_at', '>=', now()->startOfWeek())->count();
    return $weeklyOrdersCount;
}

public function monthlyOrdersReport(): int
{
    $monthlyOrdersCount = DB::table('orders')->where('created_at','>=' , now()->startOfMonth())->count();
    return $monthlyOrdersCount;
}

public function ordersTotalPrice(){
    $totalPrice = DB::table('orders')->sum('total_price');
    return $totalPrice;
}

public function averageOrderPrice(){
    $averageOrderPrice = DB::table('orders')->avg('total_price');
    return $averageOrderPrice;
}

public function ordersCountByStatus(){
    $ordersCountByStatus = DB::table('orders')
    ->select('status', DB::raw('count(*) as total'))
    ->groupBy('status')
    ->get();
    return $ordersCountByStatus;
}   

public function ordersTotalPriceByStatus(){
    $ordersTotalPriceByStatus = DB::table('orders')
    ->select('status', DB::raw('sum(total_price) as total_price'))
    ->groupBy('status')
    ->get();
    return $ordersTotalPriceByStatus;
}


}