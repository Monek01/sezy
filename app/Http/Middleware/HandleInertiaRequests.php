<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $cartService = app(CartService::class);
        $cart = $cartService->current()->loadMissing('items');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'full_name' => $request->user()->full_name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'loyalty_points' => $request->user()->loyalty_points,
                    'loyalty_tier' => $request->user()->loyalty_tier,
                    'avatar' => $request->user()->avatar,
                ] : null,
            ],
            'cart' => [
                'count' => $cart->total_items ?? 0,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'appName' => config('app.name'),
        ];
    }
}
