<?php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryReportService;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Create parent categories
    $this->parentCategory1 = Category::factory()->create(['name' => 'Electronics', 'parent_id' => null]);
    $this->parentCategory2 = Category::factory()->create(['name' => 'Clothing', 'parent_id' => null]);
    $this->parentCategory3 = Category::factory()->create(['name' => 'Books', 'parent_id' => null]);

    // Create sub categories
    $this->subCategory1 = Category::factory()->create(['name' => 'Laptops', 'parent_id' => $this->parentCategory1->id]);
    $this->subCategory2 = Category::factory()->create(['name' => 'Phones', 'parent_id' => $this->parentCategory1->id]);
    $this->subCategory3 = Category::factory()->create(['name' => 'T-Shirts', 'parent_id' => $this->parentCategory2->id]);

    // Create products in different categories with prices
    // Electronics category - 3 products
    Product::factory()->create(['category_id' => $this->parentCategory1->id, 'price' => 100]);
    Product::factory()->create(['category_id' => $this->parentCategory1->id, 'price' => 200]);
    Product::factory()->create(['category_id' => $this->parentCategory1->id, 'price' => 300]); // total = 600

    // Laptops subcategory - 2 products
    Product::factory()->create(['category_id' => $this->subCategory1->id, 'price' => 500]);
    Product::factory()->create(['category_id' => $this->subCategory1->id, 'price' => 700]); // total = 1200

    // Clothing category - 1 product
    Product::factory()->create(['category_id' => $this->parentCategory2->id, 'price' => 50]); // total = 50

    // Books category - no products (to test categories with 0 products)
});

test('should return total categories', function () {
    expect((new CategoryReportService())->totalCategories())->toBe(6);
});

test('should return total products sold in one category', function () {
    $report = (new CategoryReportService())->totalProductsSoldinOneCategory();

    // Electronics category should have 3 products
    $electronics = $report->firstWhere('name', 'Electronics');
    expect($electronics)->not()->toBeNull();
    expect($electronics->total_sold)->toBe(3);

    // Laptops subcategory should have 2 products
    $laptops = $report->firstWhere('name', 'Laptops');
    expect($laptops)->not()->toBeNull();
    expect($laptops->total_sold)->toBe(2);

    // Clothing category should have 1 product
    $clothing = $report->firstWhere('name', 'Clothing');
    expect($clothing)->not()->toBeNull();
    expect($clothing->total_sold)->toBe(1);

    // Books category should have 0 products
    $books = $report->firstWhere('name', 'Books');
    expect($books)->not()->toBeNull();
    expect($books->total_sold)->toBe(0);
});

test('should return total products revenue in one category', function () {
    $report = (new CategoryReportService())->totalProductsRevenueInOneCategory();

    // Electronics category should have total revenue of 600
    $electronics = $report->firstWhere('name', 'Electronics');
    expect($electronics)->not()->toBeNull();
    expect((float) $electronics->total_revenue)->toBe(600.0);

    // Laptops subcategory should have total revenue of 1200
    $laptops = $report->firstWhere('name', 'Laptops');
    expect($laptops)->not()->toBeNull();
    expect((float) $laptops->total_revenue)->toBe(1200.0);

    // Clothing category should have total revenue of 50
    $clothing = $report->firstWhere('name', 'Clothing');
    expect($clothing)->not()->toBeNull();
    expect((float) $clothing->total_revenue)->toBe(50.0);

    // Books category should have total revenue of 0
    $books = $report->firstWhere('name', 'Books');
    expect($books)->not()->toBeNull();
    expect((float) $books->total_revenue)->toBe(0.0);
});

test('should return total sub categories', function () {
    expect((new CategoryReportService())->totalSubCategories())->toBe(3);
});

test('should return total parent categories', function () {
    expect((new CategoryReportService())->totalParentCategories())->toBe(3);
});

