<?php

use App\Controllers\AuthController;
use App\Controllers\DocsController;
use App\Controllers\HomeController;
use App\Controllers\UsuarioController;
use App\Middlewares\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

    $app->post('/login', [AuthController::class, 'login']);
    $app->post('/novo-usuario', [UsuarioController::class, 'novoUsuario']);



    $app->group('', function ($group) {
        $group->get('/', [HomeController::class, 'index']);
    })->add(AuthMiddleware::class);




    //ROTAS DE DOCUMENTACAO
    $app->get('/docs', [DocsController::class, 'index']);
    $app->get('/openapi.json', function (Request $request, Response $response): Response {
        $specification = file_get_contents(dirname(__DIR__, 2) . '/openapi.json');

        if ($specification === false) {
            throw new RuntimeException('Não foi possível carregar a especificação OpenAPI.');
        }

        $response->getBody()->write($specification);
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    });
};
