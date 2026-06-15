<?php

namespace Webkul\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Webkul\Core\Repositories\VisitorLogRepository;

class TrackVisitor
{
    /**
     * Create a middleware instance.
     *
     * @return void
     */
    public function __construct(protected VisitorLogRepository $visitorLogRepository) {}

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only track GET requests to avoid logging form submissions, AJAX calls, etc.
        if (
            $request->isMethod('GET')
            && ! $request->ajax()
            && ! $request->wantsJson()
        ) {
            $this->visitorLogRepository->create([
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => substr((string) $request->userAgent(), 0, 512),
                'device_type' => $this->detectDevice($request->userAgent()),
                'referer' => $request->headers->get('referer') ? substr($request->headers->get('referer'), 0, 2048) : null,
                'customer_id' => Auth::guard('customer')->id(),
                'session_id' => $request->session()->getId(),
            ]);
        }

        return $next($request);
    }

    /**
     * Detect device type from user agent string.
     */
    protected function detectDevice(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/tablet|ipad|playbook|silk|(android(?!.*mobi))/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }
}
