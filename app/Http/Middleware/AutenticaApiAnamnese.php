<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige "Authorization: Bearer <token>" e compara o hash sha256 com o do ambiente
 * (contrato, seção 4). Sem token configurado, a API fica fechada.
 */
class AutenticaApiAnamnese
{
    public function handle(Request $request, Closure $next): Response
    {
        $esperado = config('anamnese.api_token_hash');
        $token = (string) $request->bearerToken();

        if (! $esperado || $token === '' || ! hash_equals($esperado, hash('sha256', $token))) {
            return response()->json(['erro' => 'não autorizado'], 401);
        }

        return $next($request);
    }
}
