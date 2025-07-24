<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Not found exception
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $previous = $e->getPrevious();
                    if ($previous instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                        $model = class_basename($previous->getModel());
                        $ids = $previous->getIds();
                        $id = !empty($ids) ? $ids[0] : 'specified';
                        return response()->json([
                            'success' => false,
                            'message' => $model . ' with ID ' . $id . ' not found',
                        ], 404);
                    }
                    return response()->json([
                        'success' => false,
                        'message' => 'Resource not found',
                    ], 404);
                }
            }
        });

        // Handle Validation exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
           if($request->is('api/*') || $request->expectsJson()) {
               return response()->json([
                   'success' => false,
                   'message' => 'Validation failed',
                   'errors' => $e->errors()
               ], 422);
           }
        });
    })->create();
