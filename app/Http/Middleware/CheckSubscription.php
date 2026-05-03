<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // If subscription exists and is expired
            if ($user->subscription_expires_at && Carbon::parse($user->subscription_expires_at)->isPast()) {
                
                // Allow only logout and subscription renewal related routes (if any)
                if (!$request->is('logout') && !$request->is('subscription*')) {
                    // Log out the user if the subscription is expired
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->with('error', 'Your subscription has expired. Please contact support to renew.');
                }
            }

            // If manually suspended
            if ($user->is_suspended) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('error', 'Your account has been suspended.');
            }
        }

        return $next($request);
    }
}
