<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;


class CategoryReportService{

public function totalCategories(){
    $totalCategories = DB::table('categories')->count();
    return $totalCategories;

}
public function totalProductsSoldinOneCategory(){
    $totalProductsSoldinOneCategory = $totalProductsSoldinOneCategory = DB::table('categories')
    ->leftJoin('products', 'categories.id', '=', 'products.category_id')
    ->select('categories.id', 'categories.name', DB::raw('COUNT(products.id) as total_sold'))
    ->groupBy('categories.id', 'categories.name')
    ->get();
    return $totalProductsSoldinOneCategory;
}


public function totalProductsRevenueInOneCategory(){
    $totalProductsRevenueInOneCategory = DB::table('categories')
    ->leftJoin('products', 'categories.id', '=', 'products.category_id')
    ->select('categories.id', 'categories.name', DB::raw('SUM(products.price) as total_revenue'))
    ->groupBy('categories.id', 'categories.name')
    ->get();
    return $totalProductsRevenueInOneCategory;

}

public function totalSubCategories(){
    $totalSubCategories = DB::table('categories')
    ->whereNotNull('parent_id')
    ->count();
    return $totalSubCategories;
}

public function totalParentCategories(){
    $totalParentCategories = DB::table('categories')
    ->whereNull('parent_id')
    ->count();
    return $totalParentCategories;
}
}


