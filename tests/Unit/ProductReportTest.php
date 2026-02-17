<?php
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\ProductReportsServices;
use Tests\TestCase;
uses(TestCase::class, RefreshDatabase::class);

test('should return total revenue for all products', function () {
    $productA = Product::factory()->create(['price' => 100]);
    $productB = Product::factory()->create(['price' => 100]);
    $totalRevenue = (new ProductReportsServices())->getTotalRevenue();
    expect((int) $totalRevenue)->toBe(200);
});

test('should return total number of products', function () {
    $productA = Product::factory()->create();
    $productA = Product::factory()->create();
    $productA = Product::factory()->create();
    $productB = Product::factory()->create();
    $totalProducts = (new ProductReportsServices())->totalProducts();
    expect($totalProducts)->toBe(4);
});