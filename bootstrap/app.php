<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'placement' => \App\Http\Middleware\EnsurePlacementTestDone::class,
            'level.access' => \App\Http\Middleware\CheckLevelAccess::class,
            'school.approved' => \App\Http\Middleware\CheckSchoolApproval::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '/payments/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
