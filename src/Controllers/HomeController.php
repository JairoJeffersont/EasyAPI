<?php

namespace App\Controllers;

use App\Helpers\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController {
    public function index(Request $request, Response $response): Response {
        return ResponseHelper::json($response, 'success', 200, 'API em funcionamento');
    }
}
