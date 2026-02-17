<?php
namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Order;
use App\Enums\RoleStatus;
use App\Services\CustomersReportService;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $adminRole = Role::factory()->create(['name' => RoleStatus::ADMIN->value]);
    $vendorRole = Role::factory()->create(['name' => RoleStatus::VENDOR->value]);
    $customerRole = Role::factory()->create(['name' => RoleStatus::CUSTOMER->value]);

    // 2 admins
    User::factory()->count(2)->create(['role_id' => $adminRole->id]);
    // 3 vendors
    User::factory()->count(3)->create(['role_id' => $vendorRole->id]);
    // 1 customer
    User::factory()->create(['role_id' => $customerRole->id]);
});

test('should return total number of customers', function () {
    expect(User::count())->toBe(6);
});

test('should return total number of customers by role', function () {
    expect(User::where('role_id', Role::where('name', RoleStatus::CUSTOMER->value)->first()->id)->count())->toBe(1);
    expect(User::where('role_id', Role::where('name', RoleStatus::VENDOR->value)->first()->id)->count())->toBe(3);
    expect(User::where('role_id', Role::where('name', RoleStatus::ADMIN->value)->first()->id)->count())->toBe(2);
});

test('should return top performance vendors', function () {
    $vendorRole = Role::firstWhere('name', RoleStatus::VENDOR->value);

    $vendor1 = User::factory()->create(['name' => 'Vendor One', 'role_id' => $vendorRole->id]);
    $vendor2 = User::factory()->create(['name' => 'Vendor Two', 'role_id' => $vendorRole->id]);
    $vendor3 = User::factory()->create(['name' => 'Vendor Three', 'role_id' => $vendorRole->id]);

    Order::factory()->create(['customer_id' => $vendor1->id, 'total_price' => 100]);
    Order::factory()->create(['customer_id' => $vendor1->id, 'total_price' => 200]); // vendor1 total = 300

    Order::factory()->create(['customer_id' => $vendor2->id, 'total_price' => 500]); // vendor2 total = 500

    Order::factory()->create(['customer_id' => $vendor3->id, 'total_price' => 50]);  // vendor3 total = 50

    $result = (new CustomersReportService())->topPerformanceVendors();

    expect($result)->toHaveCount(3);
    expect($result[0]->name)->toBe('Vendor Two');
    expect((float) $result[0]->total)->toBe(500.0);
    expect($result[1]->name)->toBe('Vendor One');
    expect((float) $result[1]->total)->toBe(300.0);
    expect($result[2]->name)->toBe('Vendor Three');
    expect((float) $result[2]->total)->toBe(50.0);
});

test('should reutrn top performance customers', function () {
    $customerRole = Role::firstWhere('name', RoleStatus::CUSTOMER->value);

    $customer1 = User::factory()->create(['name' => 'Customer One', 'role_id' => $customerRole->id]);
    $customer2 = User::factory()->create(['name' => 'Customer Two', 'role_id' => $customerRole->id]);
    $customer3 = User::factory()->create(['name' => 'Customer Three', 'role_id' => $customerRole->id]);

    Order::factory()->create(['customer_id' => $customer1->id, 'total_price' => 150]);
    Order::factory()->create(['customer_id' => $customer1->id, 'total_price' => 250]); // customer1 total = 400

    Order::factory()->create(['customer_id' => $customer2->id, 'total_price' => 600]); // customer2 total = 600

    Order::factory()->create(['customer_id' => $customer3->id, 'total_price' => 75]);  // customer3 total = 75

    $result = (new CustomersReportService())->topPerformanceCustomers();

    expect($result)->toHaveCount(3);
    expect($result[0]->name)->toBe('Customer Two');
    expect((float) $result[0]->total)->toBe(600.0);
    expect($result[1]->name)->toBe('Customer One');
    expect((float) $result[1]->total)->toBe(400.0);
    expect($result[2]->name)->toBe('Customer Three');
    expect((float) $result[2]->total)->toBe(75.0);
});

test('should return topPerformance at all', function () {
    $vendorRole = Role::firstWhere('name', RoleStatus::VENDOR->value);
    $customerRole = Role::firstWhere('name', RoleStatus::CUSTOMER->value);
    $adminRole = Role::firstWhere('name', RoleStatus::ADMIN->value);

    $vendor = User::factory()->create(['name' => 'Top Vendor', 'role_id' => $vendorRole->id]);
    $customer = User::factory()->create(['name' => 'Top Customer', 'role_id' => $customerRole->id]);
    $admin = User::factory()->create(['name' => 'Top Admin', 'role_id' => $adminRole->id]);

    Order::factory()->create(['customer_id' => $vendor->id, 'total_price' => 300]);
    Order::factory()->create(['customer_id' => $customer->id, 'total_price' => 800]);
    Order::factory()->create(['customer_id' => $admin->id, 'total_price' => 100]);

    $result = (new CustomersReportService())->topPerformanceAtAll();

    expect($result)->toHaveCount(3);
    expect($result[0]->name)->toBe('Top Customer');
    expect((float) $result[0]->total)->toBe(800.0);
    expect($result[1]->name)->toBe('Top Vendor');
    expect((float) $result[1]->total)->toBe(300.0);
    expect($result[2]->name)->toBe('Top Admin');
    expect((float) $result[2]->total)->toBe(100.0);
});
