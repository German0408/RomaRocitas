<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (Auth::check() && $user->isAdmin()) {
            $lastActivity = session('admin_last_activity');
            $timeout = config('session.admin_lifetime', 60); // minutes

            if ($lastActivity && now()->diffInMinutes($lastActivity) > $timeout) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Your admin session has expired due to inactivity.');
            }

            session(['admin_last_activity' => now()]);
        }

        return $next($request);
    }
}
