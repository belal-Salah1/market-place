<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CustomersReportService{

public function totalCountOfCustomers(){
    $totalCountOfCustomers = DB::table('users')->count();
    return $totalCountOfCustomers;
}

public function totalCountOfCustmersByRole(){
    $totalCountOfCustmersByRole = DB::table('users')->join('roles','users.role_id','=','roles.id')->
    select('roles.name', DB::raw('count(*) as total'))
    ->groupBy('roles.name')
    ->get();
    return $totalCountOfCustmersByRole;
}

public function topPerformanceVendors(){
    $topPerformanceVendors = DB::table('users')
    ->join('orders','users.id','=','orders.customer_id')
    ->join('roles','users.role_id','=','roles.id')
    ->where('roles.name', 'vendor')
    ->select('users.name', DB::raw('sum(orders.total_price) as total'))
    ->groupBy('users.name')
    ->orderByDesc('total')
    ->get();
    return $topPerformanceVendors;
}
public function topPerformanceCustomers(){
    $topPerformanceCustomers = DB::table('users')
    ->join('orders','users.id','=','orders.customer_id')
    ->join('roles','users.role_id','=','roles.id')
    ->where('roles.name', 'customer')
    ->select('users.name', DB::raw('sum(orders.total_price) as total'))
    ->groupBy('users.name')
    ->orderByDesc('total')
    ->get();
    return $topPerformanceCustomers;
}

public function topPerformanceAtAll(){
    $topPerformanceVendors = DB::table('users')
        ->join('orders','users.id','=','orders.customer_id')
        ->join('roles','users.role_id','=','roles.id')
        ->select('users.id', 'users.name', DB::raw('SUM(orders.total_price) as total'))
        ->groupBy('users.id', 'users.name')
        ->orderByDesc('total')
        ->get();

    return $topPerformanceVendors;
}
}