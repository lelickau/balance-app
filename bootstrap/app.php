<?php

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Application;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {})
    ->withExceptions(function (Illuminate\Foundation\Configuration\Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {

            if ($request->is('api/*') || $request->expectsJson()) {

                $status = 500;
                $message = 'Ошибка на сервере';

                if ($e instanceof ValidationException) {
                    $status = 422;
                    $message = 'Ошибка валидации';
                }

                if ($e instanceof HttpException) {
                    $status = $e->getStatusCode();
                    $message = $e->getMessage();
                }

                if ($e instanceof ModelNotFoundException) {
                    $status = 404;
                    $message = 'Пользователь не найден';
                }

                if ($e instanceof NotFoundHttpException) {
                    $status = 404;
                    $message = 'Ресурс не найден';
                }

                if ($e instanceof MethodNotAllowedHttpException) {
                    $status = 405;
                    $message = 'Метод не разрешён';
                }

                return response()->json([
                    'error' => $message,
                ], $status);
            }

            return null;
        });
    })->create();
