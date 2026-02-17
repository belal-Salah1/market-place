<?php
use \App\Services\OrderReportsService;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;


uses(TestCase::class, RefreshDatabase::class);

test('should return total number of orders', function () {
    $orderA = Order::factory()->create();
    $orderB = Order::factory()->create();
    $orderC = Order::factory()->create();
    $orderD = Order::factory()->create();

    expect((new OrderReportsService())->ordersCount())->toBe(4);
});

describe('total oders count in a specific time frame', function () {
    beforeEach(function () {
        $this->travelTo(Carbon::create(2026, 6, 20, 12, 0, 0));

        Order::factory()->create(['created_at' => now()]);
        Order::factory()->create(['created_at' => now()]);

        Order::factory()->create(['created_at' => now()->subDays(2)]);

        Order::factory()->create(['created_at' => now()->subDays(10)]);
        Order::factory()->create(['created_at' => now()->subDays(12)]);
        Order::factory()->create(['created_at' => now()->subDays(14)]);
    });

    test('should return total number of daily orders', function () {
        expect((new OrderReportsService())->dailyOrdersReport())->toBe(2);
    });

    test('should return weekly orders total price', function () {
        expect((new OrderReportsService())->weeklyOrdersReport())->toBe(3);
    });

    test('should return total number of monthly orders', function () {
        expect((new OrderReportsService())->monthlyOrdersReport())->toBe(6);
    });
});



test('should return orders total price', function () {
    Order::factory()->create(['total_price' => 100]);
    Order::factory()->create(['total_price' => 200]);
    expect((int)(new OrderReportsService())->ordersTotalPrice())->toBe(300);
});


test('should return average order price', function () {
    Order::factory()->create(['total_price' => 100]);
    Order::factory()->create(['total_price' => 200]);
    expect((int)(new OrderReportsService())->averageOrderPrice())->toBe(150);
});

test('should return grouped orders by status', function () {
    Order::factory()->create(['status' => 'pending']);
    Order::factory()->create(['status' => 'pending']);
    Order::factory()->create(['status' => 'completed']);
    $ordersCountByStatus = (new OrderReportsService())->ordersCountByStatus();
    expect($ordersCountByStatus->where('status', 'pending')->first()->total)->toBe(2);
    expect($ordersCountByStatus->where('status', 'completed')->first()->total)->toBe(1);

});

test('should return orders total price by status', function () {
    Order::factory()->create(['status' => 'pending', 'total_price' => 100]);
    Order::factory()->create(['status' => 'pending', 'total_price' => 200]);
    Order::factory()->create(['status' => 'completed', 'total_price' => 300]);
    $ordersTotalPriceByStatus = (new OrderReportsService())->ordersTotalPriceByStatus();
    expect((int)$ordersTotalPriceByStatus->where('status', 'pending')->first()->total_price)->toBe(300);
    expect((int)$ordersTotalPriceByStatus->where('status', 'completed')->first()->total_price)->toBe(300);

});
