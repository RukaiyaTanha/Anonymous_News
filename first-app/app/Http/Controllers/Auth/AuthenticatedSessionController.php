<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): RedirectResponse
    {
        return redirect()->to($this->resolveOverlayRedirect($request, 'login'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $redirectTo = Auth::user()?->role === 'admin'
            ? route('admin.dashboard', absolute: false)
            : route('dashboard', absolute: false);

        return redirect()->intended($redirectTo);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
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
