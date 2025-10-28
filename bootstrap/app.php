<?php

use App\Http\Middleware\FinanceAdminMiddleware;
use App\Http\Middleware\OrganizerMiddleware;
use App\Http\Middleware\PlayerMiddleware;
use App\Http\Middleware\PlayerOrganizerMiddleware;
use App\Http\Middleware\SuperAdminFinanceAdminMiddleware;
use App\Http\Middleware\SuperAdminFinanceAdminSupportAdminMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\SuperAdminSupportAdminMiddleware;
use App\Http\Middleware\SupportAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->alias([
            'admin' => SuperAdminMiddleware::class,
            'finance' => FinanceAdminMiddleware::class,
            'support' => SupportAdminMiddleware::class,
            'player' => PlayerMiddleware::class,
            'organizer' => OrganizerMiddleware::class,
            'player.organizer' => PlayerOrganizerMiddleware::class,
            'admin.finance' => SuperAdminFinanceAdminMiddleware::class,
            'admin.support' => SuperAdminSupportAdminMiddleware::class,
            'admin.finance.support' => SuperAdminFinanceAdminSupportAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
