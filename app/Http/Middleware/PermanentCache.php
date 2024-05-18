<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Litespeed\LSCache\LSCache;

class PermanentCache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Exclude /admin pages from being cached
        if (!$request->is('admin/*')) {
            // Set the cache headers for one year (31536000 seconds)
            $response->headers->set('Cache-Control', 'public, max-age=31536000');
            LSCache::setCacheControl('public,max-age=31536000');
        } else {
            LSCache::setNoCache();
        }
        
        return $response;
    }
}
