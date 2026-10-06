<?php

namespace App\Controllers;

use App\Exceptions\LoginException;
use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use Exception;
use JairoJeffersont\EasyLogger\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController {

    private AuthService $auth_service;

    public function __construct() {
        $this->auth_service = new AuthService();
    }

    public function login(Request $request, Response $response): Response {
        $dados = json_decode((string) $request->getBody(), true);

        if (!is_array($dados)) {
            return ResponseHelper::json($response, 'bad_request', 400, 'JSON inválido.');
        }

        $campos = ['senha', 'email'];

        foreach ($campos as $campo) {
            if (!isset($dados[$campo]) || empty($dados[$campo])) {
                return ResponseHelper::json($response, 'bad_request', 400, "O campo {$campo} é obrigatório.");
            }
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return ResponseHelper::json($response, 'bad_request', 400, 'O email é inválido.');
        }

        try {
            $token = $this->auth_service->login($dados['email'], $dados['senha']);
            return ResponseHelper::json($response, 'success', 200, 'Login realizado com sucesso.', ['token' => $token]);
        } catch (LoginException $e) {
            return ResponseHelper::json($response, 'info', 401, 'Credenciais inválidas.');
        } catch (Exception $e) {
            $log_id = Logger::newLog(LOG_FOLDER, 'ERROR', $e->getMessage(), 'ERROR');
            return ResponseHelper::json($response, 'server_error', 500, 'Ocorreu um erro interno.', $log_id);
        }
    }
}
