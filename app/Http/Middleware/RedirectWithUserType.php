<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectWithUserType
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return redirect()->route('dashboard.default');
        }

        return match ($user->role->name) {
            'admin'    => redirect()->route('admin.dashboard'),
            'vendor'   => redirect()->route('vendor.dashboard'),
            'customer' => redirect()->route('customer.dashboard'),
            default    => redirect()->route('dashboard.default'),
        };
    }
}
