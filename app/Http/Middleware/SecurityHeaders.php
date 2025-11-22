<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline' https://fonts.bunny.net; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self';";

        if (app()->environment('local')) {
            $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://127.0.0.1:5173; style-src 'self' 'unsafe-inline' https://fonts.bunny.net http://127.0.0.1:5173; img-src 'self' data: https:; font-src 'self' data: https://fonts.bunny.net http://127.0.0.1:5173; connect-src 'self' ws://127.0.0.1:5173 http://127.0.0.1:5173;";
        }

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
