<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Verifique os campos informados.',
                'data' => null,
                'errors' => $exception->errors(),
            ], 422);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'O registro solicitado não foi encontrado ou já foi removido.',
                'data' => null,
                'errors' => (object) [],
            ], 404);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            $status = $exception->getStatusCode();
            $message = $status === 419
                ? 'Sua sessão expirou. Recarregue a página e tente novamente.'
                : 'Não foi possível concluir esta solicitação.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
                'errors' => (object) [],
            ], $status);
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro interno. Tente novamente em alguns instantes.',
                'data' => null,
                'errors' => (object) [],
            ], 500);
        });
    })->create();
