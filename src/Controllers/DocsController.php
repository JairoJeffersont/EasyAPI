<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DocsController {
    public function index(Request $request, Response $response): Response {
        $html = file_get_contents(dirname(__DIR__, 2) . '/public/docs/index.html');

        if ($html === false) {
            throw new \RuntimeException('Não foi possível carregar a interface da documentação.');
        }

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
