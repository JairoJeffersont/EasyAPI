<?php

use App\Helpers\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpException;
use Slim\App;

return function (App $app) {

    $errorMiddleware = $app->addErrorMiddleware(false, true, true);

    $errorMiddleware->setErrorHandler(HttpNotFoundException::class, function (Request $request, Throwable $exception) use ($app): Response {
        $response = $app->getResponseFactory()->createResponse();

        return ResponseHelper::json($response, 'not_found', 404, 'Rota não encontrada.');
    });

    $errorMiddleware->setErrorHandler(HttpMethodNotAllowedException::class, function (Request $request, Throwable $exception) use ($app): Response {
        $response = $app->getResponseFactory()->createResponse();

        return ResponseHelper::json($response, 'method_not_allowed', 405, 'Método HTTP não permitido.');
    });

    $errorMiddleware->setErrorHandler(HttpException::class, function (Request $request, Throwable $exception) use ($app): Response {
        $response = $app->getResponseFactory()->createResponse();

        return ResponseHelper::json($response, 'http_error', $exception->getCode(), $exception->getMessage());
    });

    $errorMiddleware->setErrorHandler(Throwable::class, function (Request $request, Throwable $exception) use ($app): Response {
        $response = $app->getResponseFactory()->createResponse();

        return ResponseHelper::json($response, 'server_error', 500, 'Ocorreu um erro interno.', null, $exception->getMessage());
    });
};
