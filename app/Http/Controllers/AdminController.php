<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $pendingVendors = User::whereHas('role', fn ($q) => $q->where('name', 'vendor'))
            ->where('is_approved', false)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'user' => $request->user(),
            'pendingVendors' => $pendingVendors,
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
