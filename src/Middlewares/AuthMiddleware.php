<?php

namespace App\Middlewares;

use App\Helpers\JwtHelper;
use App\Helpers\ResponseHelper;
use Nyholm\Psr7\Response as NyholmResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware {
    public function __invoke(Request $request, RequestHandler $handler): Response {
        $authorization = $request->getHeaderLine('Authorization');

        if (empty($authorization)) {
            return ResponseHelper::json(new NyholmResponse(), 'unauthorized', 401, 'Token não informado.');
        }

        if (!str_starts_with($authorization, 'Bearer ')) {
            return ResponseHelper::json(new NyholmResponse(), 'unauthorized', 401, 'Token inválido.');
        }

        $token = substr($authorization, 7);

        try {
            $payload = JwtHelper::validar($token);

            $request = $request->withAttribute('auth', $payload);

            return $handler->handle($request);
        } catch (\Exception $e) {
            return ResponseHelper::json(new NyholmResponse(), 'unauthorized', 401, 'Token inválido ou expirado.');
        }
    }
}
