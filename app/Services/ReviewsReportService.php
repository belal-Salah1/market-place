<?php

use Illuminate\Support\Facades\DB;

class ReviewsReportService{
    public function averageRatingPerProduct(){
        $averageRatingPerProduct = DB::table('reviews')
        ->join('products', 'reviews.product_id', '=', 'products.id')
        ->select('products.name', DB::raw('AVG(reviews.rating) as average_rating'))
        ->groupBy('products.name')
        ->get();
        return $averageRatingPerProduct;
    }

    public function totalReviewsPerCustomer(){
        $totalReviewsPerCustomer = DB::table('reviews')
        ->join('users', 'reviews.customer_id', '=', 'users.id')
        ->select('users.name', DB::raw('count(*) as total_reviews'))
        ->groupBy('users.name')
        ->get();
        return $totalReviewsPerCustomer;
    }
}