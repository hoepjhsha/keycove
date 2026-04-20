<?php

use App\Exceptions\Handler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin/*')) {
                return '/admin/auth/login';
            }

            return '/auth/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //        $exceptions->render(function (Throwable $e, $request) {
        //            return new Handler(app())->render($request, $e);
        //        });
    })->create();
