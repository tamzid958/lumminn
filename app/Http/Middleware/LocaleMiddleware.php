<?php
// app/Http/Middleware/SetLocale.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $excludedPaths = [
            'admin', // Example: Exclude routes under '/admin'
            // Add more excluded paths as needed
        ];

        foreach ($excludedPaths as $path) {
            if ($request->is($path . '*')) {
                app()->setLocale('en');
                return $next($request);
            }
        }

        // Your existing localization logic here...
        return $next($request);
    }
}
