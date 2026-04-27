<?php

use App\Console\Commands\DispatchDuePosts;
use Illuminate\Console\Scheduling\Schedule;
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
        $middleware->web(append: [
            \App\Http\Middleware\SetCurrentWorkspace::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias(['admin' => \App\Http\Middleware\AdminMiddleware::class]);

        // BIL-01: payment gateway webhooks must bypass CSRF
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // SCH-02: check for due posts every minute
        $schedule->command(DispatchDuePosts::class)->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
