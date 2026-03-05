<?php

namespace App\Http\Middleware;

use App\Services\MaintenanceService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    private const EXEMPT_PREFIXES = [
        'admin',
        'login',
        'register',
        'logout',
        'forgot-password',
        'reset-password',
        'email',
        'two-factor',
        'user',
        'dashboard',
        'settings',
        'password',
        'password-confirm',
        'verification',
        'sanctum',
        'impressum',
        'datenschutz',
    ];

    public function __construct(
        private MaintenanceService $maintenance
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->maintenance->isEnabled()) {
            return $next($request);
        }

        if ($request->user() !== null) {
            return $next($request);
        }

        if ($this->isExemptPath($request->path())) {
            return $next($request);
        }

        if ($request->path() === 'up') {
            return $next($request);
        }

        $endsAt = $this->maintenance->getEndsAt();
        $endsAtValue = null;
        if ($endsAt !== null && $endsAt->isFuture()) {
            $endsAtValue = $endsAt->toIso8601String();
        }

        return Inertia::render('Maintenance', [
            'message' => $this->maintenance->getMessage(),
            'endsAt' => $endsAtValue,
        ])->toResponse($request);
    }

    private function isExemptPath(string $path): bool
    {
        $path = trim($path, '/');

        foreach (self::EXEMPT_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }
}
