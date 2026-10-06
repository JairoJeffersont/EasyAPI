<?php

namespace App\Middlewares;

use App\Helpers\ResponseHelper;
use Nyholm\Psr7\Response as NyholmResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CorsMiddleware {
    private const ALLOWED_METHODS = ['GET', 'POST', 'OPTIONS'];
    private const ALLOWED_HEADERS = ['accept', 'authorization', 'content-type'];

    public function __invoke(Request $request, RequestHandler $handler): Response {
        $origin = $request->getHeaderLine('Origin');
        $allowedOrigins = array_filter(array_map(
            'trim',
            explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000,http://127.0.0.1:3000,http://localhost:5173,http://127.0.0.1:5173')
        ));

        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            $response = new NyholmResponse(204);

            if ($origin === '' || !in_array($origin, $allowedOrigins, true)) {
                return ResponseHelper::json($response, 'cors_error', 403, 'Origem não permitida.');
            }

            $requestedMethod = strtoupper($request->getHeaderLine('Access-Control-Request-Method'));
            if ($requestedMethod !== '' && !in_array($requestedMethod, self::ALLOWED_METHODS, true)) {
                return ResponseHelper::json($response, 'cors_error', 403, 'Método não permitido pela política CORS.');
            }

            $requestedHeaders = array_filter(array_map(
                static fn(string $header): string => strtolower(trim($header)),
                explode(',', $request->getHeaderLine('Access-Control-Request-Headers'))
            ));

            if (array_diff($requestedHeaders, self::ALLOWED_HEADERS) !== []) {
                return ResponseHelper::json($response, 'cors_error', 403, 'Cabeçalho não permitido pela política CORS.');
            }

            return $this->addCorsHeaders($response, $origin)
                ->withHeader('Access-Control-Allow-Methods', implode(', ', self::ALLOWED_METHODS))
                ->withHeader('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type')
                ->withHeader('Access-Control-Max-Age', '600')
                ->withHeader('Vary', 'Origin, Access-Control-Request-Method, Access-Control-Request-Headers');
        }

        $response = $handler->handle($request);

        if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
            return $this->addCorsHeaders($response, $origin);
        }

        return $response;
    }

    private function addCorsHeaders(Response $response, string $origin): Response {
        return $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Vary', 'Origin');
    }
}
