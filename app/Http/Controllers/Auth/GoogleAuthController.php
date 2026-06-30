<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(CartService $cartService): RedirectResponse
    {
        $sessionId = session()->getId();

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Connexion Google impossible. Réessayez.');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            [$firstName, $lastName] = $this->splitName($googleUser->getName());

            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => null,
                'phone' => null,
            ]);
        } elseif (! $user->google_id) {
            $user->update(['google_id' => $googleUser->getId(), 'avatar' => $googleUser->getAvatar()]);
        }

        if ($user->is_blocked) {
            return redirect()->route('login')->with('error', 'Ce compte a été suspendu.');
        }

        Auth::login($user, true);
        $cartService->mergeGuestCartIntoUser($user->id, $sessionId);

        return redirect()->intended($user->isAdmin() ? route('admin.dashboard') : route('home'));
    }

    private function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }
}
