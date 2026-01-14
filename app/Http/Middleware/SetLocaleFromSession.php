<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SetLocaleFromSession
{
    public function handle(Request $request, Closure $next)
    {
        $supported = config('app.supported_locales', ['en', 'ar']);

        $requested = $request->get('lang') ?? $request->route('locale');
        $locale = $requested
            ?? $request->session()->get('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale');

        $locale = strtolower((string) $locale);
        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);
        View::share('currentLocale', $locale);
        View::share('isRtl', $locale === 'ar');

        return $next($request);
    }
}
