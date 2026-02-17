<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProductReportsServices{


public function getTotalRevenue(){
    $totalRevenue = DB::table('products')->sum('price');
    return $totalRevenue;
}

public function totalProducts(){
    $totalProducts = DB::table('products')->count();
    return $totalProducts;
}


}