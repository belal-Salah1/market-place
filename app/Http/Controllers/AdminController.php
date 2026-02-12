<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index (Request $request) {
        $pendingVendors = User::whereHas('role', fn ($q) => $q->where('name', 'vendor'))
            ->where('is_approved', false)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'user' => $request->user(),
            'pendingVendors' => $pendingVendors,
        ]);
}
}