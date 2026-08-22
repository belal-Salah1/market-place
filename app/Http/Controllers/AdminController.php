<?php

namespace App\Http\Controllers;

use App\Enums\MetaTrackingRange;
use App\Models\User;
use App\Services\Meta\MetaTrackingReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function __construct(private readonly MetaTrackingReportService $tracking) {}

    public function index(Request $request)
    {
        $pendingVendors = User::whereHas('role', fn ($q) => $q->where('name', 'vendor'))
            ->where('is_approved', false)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'user' => $request->user(),
            'pendingVendors' => $pendingVendors,
            'tracking' => $this->tracking->summary(MetaTrackingRange::WEEK->since()),
        ]);
    }

    public function approve(Request $request, User $user)
    {
        $user->is_approved = true;
        $user->save();
    }

    public function vendors()
    {
        $vendors = User::whereHas('role', fn ($q) => $q->where('name', 'vendor'))
            ->withCount(['products'])
            ->get();

        return Inertia::render('Admin/Vendors/Index', [
            'vendors' => $vendors,
        ]);
    }

    public function vendorShow(User $user)
    {
        $user->load(['products.category', 'role']);

        $categories = $user->products
            ->pluck('category')
            ->filter()
            ->unique('id')
            ->values();

        return Inertia::render('Admin/Vendors/Show', [
            'vendor' => $user,
            'products' => $user->products,
            'categories' => $categories,
        ]);
    }
}
