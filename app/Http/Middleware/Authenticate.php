<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API routes, don't redirect - let the exception handler handle it
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }

        return route('login');
    }
    
    /**
     * Handle unauthenticated requests for API routes
     */
    protected function unauthenticated($request, array $guards)
    {
        // For API routes, throw an authentication exception that will be caught by our handler
        if ($request->is('api/*') || $request->expectsJson()) {
            throw new \Illuminate\Auth\AuthenticationException(
                'Unauthenticated.', $guards, null
            );
        }
        
        parent::unauthenticated($request, $guards);
    }
}