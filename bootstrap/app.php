<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\TrustProxies;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            TrustProxies::class,
        ]);

        // Register Spatie Permission middleware aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle AuthorizationException to prevent Inertia 403 error page issues
        $exceptions->render(function (AuthorizationException $e, $request) {
            // For Inertia/AJAX requests, return JSON instead of trying to render Errors/403
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'No tienes permiso para realizar esta acción.',
                ], 403);
            }

            // For regular requests, redirect back with error message
            return redirect()->back()
                ->with('error', $e->getMessage() ?: 'No tienes permiso para realizar esta acción.');
        });
    })->create();
