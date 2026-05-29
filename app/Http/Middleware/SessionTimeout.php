<?php

namespace App\Http\Middleware;

use App\Models\Configuration;
use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $timeoutMinutes = (int) Configuration::getValue('session_timeout_minutes', 120);
        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity && Carbon::parse($lastActivity)->lte(now()->subMinutes($timeoutMinutes))) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }

        $request->session()->put('last_activity_at', now()->toDateTimeString());

        return $next($request);
    }
}
