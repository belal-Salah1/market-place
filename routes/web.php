<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryReportController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomersReportController;
use App\Http\Controllers\EarningsController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrdersReportController;
use App\Http\Controllers\PaymentsReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductsReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewsReportController;
use App\Http\Controllers\VendorController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::middleware('guest')->group(function(){
    Route::get('/auth/google/redirect',[GoogleAuthController::class,'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback',[GoogleAuthController::class,'handleGoogleCallback'])->name('auth.google.callback');
});


Route::middleware(['auth', 'verified'])->group(function () {
    // Generic dashboard - redirects via middleware based on role
    Route::get('/dashboard', function () {})->middleware(['vendor.approval', 'redirect.dashboard'])->name('dashboard');

    // Default fallback dashboard
    Route::get('/dashboard/default', function (Request $request) {
        return Inertia::render('Dashboard', ['user' => $request->user()]);
    })->name('dashboard.default');

    // Admin dashboard - pass pending vendors
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->middleware('role:admin')->name('admin.dashboard');

    // Approve vendor (admin)
    Route::post('/admin/vendors/{user}/approve', [AdminController::class, 'approve'])
        ->middleware('role:admin')
        ->name('admin.vendors.approve');

    // Admin: view all vendors and vendor details
    Route::get('/admin/vendors', [AdminController::class, 'vendors'])
        ->middleware('role:admin')
        ->name('admin.vendors.index');

    Route::get('/admin/vendors/{user}', [AdminController::class, 'vendorShow'])
        ->middleware('role:admin')
        ->name('admin.vendors.show');

    // Vendor dashboard & orders
    Route::get('/vendor/dashboard', [VendorController::class, 'index'])->middleware('role:vendor')->name('vendor.dashboard');
    Route::get('/vendor/orders', [VendorController::class, 'orders'])->middleware('role:vendor')->name('vendor.orders.index');
    Route::get('/vendor/orders/{order}', [VendorController::class, 'orderShow'])->middleware('role:vendor')->name('vendor.orders.show');

    // Customer dashboard
    Route::get('/customer/dashboard', [CustomerController::class, 'index'])->middleware('role:customer')->name('customer.dashboard');
});

// Customer routes
Route::middleware(['auth', 'verified', 'role:customer'])->group(function () {
    Route::get('/customer/products', [CustomerController::class, 'products'])->name('customer.products.index');
    Route::get('/customer/products/{product}', [CustomerController::class, 'productShow'])->name('customer.products.show');
    Route::get('/customer/orders', [CustomerController::class, 'orders'])->name('customer.orders.index');
    Route::get('/customer/orders/{order}', [CustomerController::class, 'orderShow'])->name('customer.orders.show');
    Route::post('/customer/orders', [CustomerController::class, 'storeOrder'])->name('customer.orders.store');
});

// Vendor pending approval (not protected by vendor.approval to avoid loop)
Route::middleware('auth')->get('/vendor/pending-approval', [VendorController::class, 'pendingApproval'])->name('vendor.pending-approval');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route::post('/admin/vendors/{user}/reject', [AdminController::class, 'reject'])
//     ->middleware('role:admin')
//     ->name('admin.vendors.reject');

// Reports - Reviews

Route::get('/reports/reviews', [ReviewsReportController::class, 'index'])->name('reports.reviews.index');
Route::get('/reports/reviews/by-customer', [ReviewsReportController::class, 'byCustomer'])->name('reports.reviews.by-customer');

// Reports - Categories

Route::get('/reports/categories', [CategoryReportController::class, 'index'])->name('reports.categories.index');
Route::get('/reports/categories/products', [CategoryReportController::class, 'productsSold'])->name('reports.categories.products');
Route::get('/reports/categories/revenue', [CategoryReportController::class, 'revenue'])->name('reports.categories.revenue');
Route::get('/reports/categories/parent', [CategoryReportController::class, 'parent'])->name('reports.categories.parent');
Route::get('/reports/categories/sub', [CategoryReportController::class, 'sub'])->name('reports.categories.sub');

// Reports - Customers

Route::get('/reports/customers', [CustomersReportController::class, 'index'])->name('reports.customers.index');
Route::get('/reports/customers/by-role', [CustomersReportController::class, 'byRole'])->name('reports.customers.by-role');
Route::get('/reports/customers/top-vendors', [CustomersReportController::class, 'topVendors'])->name('reports.customers.top-vendors');
Route::get('/reports/customers/top-customers', [CustomersReportController::class, 'topCustomers'])->name('reports.customers.top-customers');
Route::get('/reports/customers/top-all', [CustomersReportController::class, 'topAll'])->name('reports.customers.top-all');

// Reports - Orders

Route::get('/reports/orders', [OrdersReportController::class, 'index'])->name('reports.orders.index');
Route::get('/reports/orders/daily', [OrdersReportController::class, 'daily'])->name('reports.orders.daily');
Route::get('/reports/orders/weekly', [OrdersReportController::class, 'weekly'])->name('reports.orders.weekly');
Route::get('/reports/orders/monthly', [OrdersReportController::class, 'monthly'])->name('reports.orders.monthly');
Route::get('/reports/orders/total-price', [OrdersReportController::class, 'totalPrice'])->name('reports.orders.total-price');
Route::get('/reports/orders/average-price', [OrdersReportController::class, 'averagePrice'])->name('reports.orders.average-price');
Route::get('/reports/orders/by-status', [OrdersReportController::class, 'byStatus'])->name('reports.orders.by-status');
Route::get('/reports/orders/price-by-status', [OrdersReportController::class, 'priceByStatus'])->name('reports.orders.price-by-status');

// Reports - Payments

Route::get('/reports/payments/by-status', [PaymentsReportController::class, 'byStatus'])->name('reports.payments.by-status');
Route::get('/reports/payments/by-method', [PaymentsReportController::class, 'byMethod'])->name('reports.payments.by-method');

// Reports - Products

Route::get('/reports/products', [ProductsReportController::class, 'index'])->name('reports.products.index');
Route::get('/reports/products/revenue', [ProductsReportController::class, 'revenue'])->name('reports.products.revenue');

// Route for vendor products

Route::middleware(['auth', 'verified', 'role:vendor'])->group(function () {
    Route::get('/vendor/products', [ProductController::class, 'index'])->name('vendor.products.index');
    Route::get('/vendor/products/create', [ProductController::class, 'create'])->name('vendor.products.create');
    Route::post('/vendor/products', [ProductController::class, 'store'])->name('vendor.products.store');
    Route::get('/vendor/products/{product}/edit', [ProductController::class, 'edit'])->name('vendor.products.edit');
    Route::put('/vendor/products/{product}', [ProductController::class, 'update'])->name('vendor.products.update');
    Route::delete('/vendor/products/{product}', [ProductController::class, 'destroy'])->name('vendor.products.destroy');
    Route::get('/vendor/categories', [CategoryController::class, 'index'])->name('vendor.categories.index');
    Route::get('/vendor/categories/create', [CategoryController::class, 'create'])->name('vendor.categories.create');
    Route::post('/vendor/categories/', [CategoryController::class, 'store'])->name('vendor.categories.store');
    Route::get('/vendor/categories/{category}/edit', [CategoryController::class, 'edit'])->name('vendor.categories.edit');
    Route::put('/vendor/categories/{category}', [CategoryController::class, 'update'])->name('vendor.categories.update');
    Route::delete('/vendor/categories/{category}', [CategoryController::class, 'destroy'])->name('vendor.categories.destroy');

    // Vendor coupons
    Route::get('/vendor/coupons', [CouponController::class, 'index'])->name('vendor.coupons.index');
    Route::get('/vendor/coupons/create', [CouponController::class, 'create'])->name('vendor.coupons.create');
    Route::post('/vendor/coupons', [CouponController::class, 'store'])->name('vendor.coupons.store');
    Route::delete('/vendor/coupons/{coupon}', [CouponController::class, 'destroy'])->name('vendor.coupons.destroy');
    Route::patch('/vendor/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('vendor.coupons.toggle');

    // Vendor earnings
    Route::get('/vendor/earnings', [EarningsController::class, 'index'])->name('vendor.earnings.index');

    // Vendor reviews
    Route::get('/vendor/reviews', [ReviewController::class, 'vendorReviews'])->name('vendor.reviews.index');
});

// Customer routes
Route::middleware(['auth', 'verified', 'role:customer'])->group(function () {
    // Reviews
    Route::get('/customer/products/{product}/review', [ReviewController::class, 'create'])->name('customer.reviews.create');
    Route::post('/customer/products/{product}/review', [ReviewController::class, 'store'])->name('customer.reviews.store');
});

// Messages (shared between vendor and customer)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
});

require __DIR__.'/auth.php';
