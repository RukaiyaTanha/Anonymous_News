<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): RedirectResponse
    {
        return redirect()->to($this->resolveOverlayRedirect($request, 'register'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Build a safe redirect URL that opens auth as an in-page overlay.
     */
    private function resolveOverlayRedirect(Request $request, string $authModal): string
    {
        $fallback = route('home', ['auth' => $authModal]);
        $referer = (string) $request->headers->get('referer', '');

        if ($referer === '') {
            return $fallback;
        }

        $parts = parse_url($referer);

        if ($parts === false || ($parts['host'] ?? null) !== $request->getHost()) {
            return $fallback;
        }

        $path = trim((string) ($parts['path'] ?? ''), '/');

        if ($path === '' || $path === 'login' || $path === 'register' || str_starts_with($path, 'forgot-password') || str_starts_with($path, 'reset-password')) {
            return $fallback;
        }

        $query = [];

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query['auth'] = $authModal;

        $url = ($parts['scheme'] ?? $request->getScheme()).'://'.$parts['host'];

        if (isset($parts['port'])) {
            $url .= ':'.$parts['port'];
        }

        $url .= '/'.$path;
        $url .= '?'.http_build_query($query);

        if (isset($parts['fragment'])) {
            $url .= '#'.$parts['fragment'];
        }

        return $url;
    }
}
