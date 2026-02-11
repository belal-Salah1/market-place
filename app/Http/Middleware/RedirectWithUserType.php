<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectWithUserType
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return \Inertia\Inertia::render('Dashboard');
        }

        switch ($user->role->name) {
            case 'admin':
                return \Inertia\Inertia::render('Admin/Dashboard');
            case 'vendor':
                return \Inertia\Inertia::render('Vendor/Dashboard');
            case 'customer':
                return \Inertia\Inertia::render('Customer/Dashboard');
            default:
                return \Inertia\Inertia::render('Dashboard');
        }
    }
}
