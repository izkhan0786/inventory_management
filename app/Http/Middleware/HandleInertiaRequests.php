<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\ProductVariant;
use App\Models\Batch;
use Carbon\Carbon;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $alertCount = 0;
        $latestAlerts = [];

        if ($request->user()) {
            $lowStock = ProductVariant::whereColumn('stock_qty', '<=', 'min_stock_level')->count();
            $expiringSoon = Batch::where('current_qty', '>', 0)
                ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addMonth()])
                ->count();
            
            $alertCount = $lowStock + $expiringSoon;

            // Get last 5 for the bell icon
            $latestAlerts = collect();
            
            ProductVariant::with('product')
                ->whereColumn('stock_qty', '<=', 'min_stock_level')
                ->latest()
                ->limit(3)
                ->get()
                ->each(function($v) use ($latestAlerts) {
                    $latestAlerts->push([
                        'id' => 'ls-' . $v->id,
                        'title' => 'Low Stock',
                        'message' => "{$v->product->name} is low ({$v->stock_qty})",
                        'time' => $v->updated_at->diffForHumans(),
                        'type' => 'warning'
                    ]);
                });

            Batch::with('variant.product')
                ->where('current_qty', '>', 0)
                ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addMonth()])
                ->latest()
                ->limit(2)
                ->get()
                ->each(function($b) use ($latestAlerts) {
                    $latestAlerts->push([
                        'id' => 'ex-' . $b->id,
                        'title' => 'Expiring',
                        'message' => "Batch #{$b->batch_number} expires soon",
                        'time' => $b->updated_at->diffForHumans(),
                        'type' => 'error'
                    ]);
                });
        }

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'plan' => $request->user()->plan_type,
                    'subscription_expires_at' => $request->user()->subscription_expires_at ? $request->user()->subscription_expires_at->toDateTimeString() : null,
                    'days_left' => $request->user()->subscription_expires_at ? now()->diffInDays($request->user()->subscription_expires_at, false) : null,
                ] : null,
            ],
            'alerts' => [
                'count' => (int) $alertCount,
                'latest' => is_array($latestAlerts) ? $latestAlerts : $latestAlerts->values()->toArray()
            ],
            'locale' => app()->getLocale(),
            'translations' => (function() {
                $path = lang_path(app()->getLocale() . '.json');
                if (file_exists($path)) {
                    return json_decode(file_get_contents($path), true) ?: [];
                }
                return [];
            })(),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}
