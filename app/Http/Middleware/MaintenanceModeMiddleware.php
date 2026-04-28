<?php

namespace App\Http\Middleware;

use App\Services\SiteSettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeMiddleware
{
    public function __construct(private readonly SiteSettingsService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->settings->get('maintenance_mode', false)) {
            return $next($request);
        }

        // Admins always bypass maintenance mode
        $user = $request->user();
        if ($user?->is_admin) {
            return $next($request);
        }

        // Admin panel, health check, and webhooks always pass through
        if ($request->is('admin/*', 'up', 'up/*', 'webhooks/*')) {
            return $next($request);
        }

        // For Inertia XHR requests return JSON so the frontend can handle it
        if ($request->header('X-Inertia')) {
            return response()->json([
                'message' => 'الموقع قيد الصيانة حالياً. يرجى العودة قريباً.',
            ], 503);
        }

        return response(
            view('errors.maintenance')->render(),
            503,
            ['Content-Type' => 'text/html; charset=UTF-8'],
        );
    }
}
