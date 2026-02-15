<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewsReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


uses(TestCase::class, RefreshDatabase::class);

it('calculates average rating per product correctly', function () {
    // Arrange - انشاء بيانات وهمية
    $productA = Product::factory()->create(['name' => 'Product A']);
    Review::factory()->create(['product_id' => $productA->id, 'rating' => 4]);
    Review::factory()->create(['product_id' => $productA->id, 'rating' => 2]);

    $productB = Product::factory()->create(['name' => 'Product B']);
    Review::factory()->create(['product_id' => $productB->id, 'rating' => 5]);

    // Act - استدعاء الدالة
    $reportService = new ReviewsReportService();
    $report = $reportService->averageRatingPerProduct();

    // Assert - التأكد من النتيجة
    expect((float) $report->firstWhere('name', 'Product A')->average_rating)->toBe(3.0);
    expect((float) $report->firstWhere('name', 'Product B')->average_rating)->toBe(5.0);
});

it('should calculate total reviews per customer correctly',function(){
    //create customers and reviews fake data
    $customerA = User::factory()->create(['name' => 'Customer A']);
    Review::factory()->create(['customer_id' => $customerA->id]);
    Review::factory()->create(['customer_id' => $customerA->id]);
    Review::factory()->create(['customer_id' => $customerA->id]);

    $customerB = User::factory()->create(['name' => 'Customer B']);
    Review::factory()->create(['customer_id' => $customerB->id]);


    expect($customerA->reviews()->count())->toBe(3);
    expect($customerB->reviews()->count())->toBe(1);
    
});