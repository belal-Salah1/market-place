<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function index(Request $request) {
        return Inertia::render('Vendor/Dashboard', ['user' => $request->user()]);
    }
    public function pendingApproval(Request $request) {
    return Inertia::render('Auth/PendingApproval', ['user' => $request->user()]);
}
}
