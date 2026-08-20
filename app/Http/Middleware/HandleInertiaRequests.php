<?php

namespace App\Http\Middleware;

use App\Enums\RoleStatus;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->load('role'),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'cartCount' => fn () => $this->cartCount($request),
            'metaEvent' => fn () => session('meta_event'),
        ];
    }

    /**
     * Cart badge count, only meaningful for signed-in customers.
     */
    protected function cartCount(Request $request): int
    {
        $user = $request->user();

        if (! $user || $user->role?->name !== RoleStatus::CUSTOMER->value) {
            return 0;
        }

        return app(CartService::class)->itemCount($user);
    }
}
