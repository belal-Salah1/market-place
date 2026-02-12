<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CustomerController;
use App\Models\User;
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

Route::middleware(['auth', 'verified'])->group(function () {
    // Generic dashboard - redirects via middleware based on role
    Route::get('/dashboard', function () {
    })->middleware(['vendor.approval', 'redirect.dashboard'])->name('dashboard');

    // Default fallback dashboard
    Route::get('/dashboard/default', function (Request $request) {
        return Inertia::render('Dashboard', ['user' => $request->user()]);
    })->name('dashboard.default');

    // Admin dashboard - pass pending vendors
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->middleware('role:admin')->name('admin.dashboard');

    // Vendor dashboard
    Route::get('/vendor/dashboard', [VendorController::class, 'index'])->middleware('role:vendor')->name('vendor.dashboard');

    // Customer dashboard
    Route::get('/customer/dashboard', [CustomerController::class, 'index'])->middleware('role:customer')->name('customer.dashboard');
});

// Vendor pending approval (not protected by vendor.approval to avoid loop)
Route::middleware('auth')->get('/vendor/pending-approval',[VendorController::class, 'pendingApproval'] )->name('vendor.pending-approval');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
