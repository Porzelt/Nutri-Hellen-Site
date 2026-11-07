<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Produção fica atrás do Traefik (Coolify): confiar no proxy para obter o IP real do visitante
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API: erros curtos em JSON, sem detalhes internos (contrato, seção 5)
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            [$status, $erro] = match (true) {
                $e instanceof ValidationException => [422, collect($e->errors())->flatten()->first() ?? 'dados inválidos'],
                $e instanceof ThrottleRequestsException => [429, 'muitas requisições'],
                $e instanceof HttpExceptionInterface => [$e->getStatusCode(), match ($e->getStatusCode()) {
                    401 => 'não autorizado', 404 => 'não encontrado', 405 => 'método não permitido', default => 'erro',
                }],
                default => [500, 'erro interno'],
            };

            return response()->json(['erro' => $erro], $status, $e instanceof ThrottleRequestsException ? $e->getHeaders() : []);
        });
    })->create();
