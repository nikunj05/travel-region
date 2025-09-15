<?php

use App\Traits\ApiResponseTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Using the trait for a consistent API response
        $trait = new class
        {
            use ApiResponseTrait;
        };

        $exceptions->render(function (AuthenticationException $e, $request) use ($trait) {
            if ($request->is('api/*')) {
                return $trait->sendApiResponse(false, __('messages.invalid_credentials'), [
                    'message' => $e->getMessage(),
                ], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, $request) use ($trait) {
            if ($request->is('api/*')) {
                $messages = collect($e->errors())
                    ->flatten()
                    ->map(fn ($msg) => "<li>$msg</li>")
                    ->implode('');

                $formattedMessage = "<ul>{$messages}</ul>";

                return $trait->sendApiResponse(false, $formattedMessage, $e->errors(), 422);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) use ($trait) {
            if ($request->is('api/*')) {
                return $trait->sendApiResponse(false, __('messages.record_not_found'), [], 404);
            }
        });

        $exceptions->render(function (Throwable $e, $request) use ($trait) {
            if ($request->is('api/*')) {
                return $trait->sendApiResponse(false, __('messages.catch'), [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'trace' => $e->getTraceAsString(),
                ], 500);
            }
        });
    })
    ->create();
