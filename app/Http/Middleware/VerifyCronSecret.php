<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCronSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.google_drive.cron_secret');

        if (! is_string($secret) || $secret === '') {
            return response()->json(['message' => 'Cron authentication is not configured.'], 503);
        }

        $providedSecret = $request->bearerToken();

        if (! is_string($providedSecret) || ! hash_equals($secret, $providedSecret)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
