<?php

namespace App\Controllers;

use App\Exceptions\NenhumRegistroEncontrado;
use App\Exceptions\RegistroDuplicadoException;
use App\Helpers\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\UsuarioService;
use Exception;
use JairoJeffersont\EasyLogger\Logger;

class UsuarioController {

    private UsuarioService $usuario_service;

    public function __construct() {
        $this->usuario_service = new UsuarioService();
    }

    public function index(Request $request, Response $response): Response {
        try {
            $dados = $request->getQueryParams();

            $porPagina = isset($dados['por_pagina']) ? (int) $dados['por_pagina'] : 15;
            $coluna = $dados['coluna'] ?? 'nome';
            $direcao = $dados['direcao'] ?? 'asc';

            $usuarios = $this->usuario_service->listarUsuarios($porPagina, $coluna, $direcao);

            $data = [
                'items' => $usuarios->items(),
                'pagination' => [
                    'current_page' => $usuarios->currentPage(),
                    'per_page' => $usuarios->perPage(),
                    'total' => $usuarios->total(),
                    'last_page' => $usuarios->lastPage(),
                    'from' => $usuarios->firstItem(),
                    'to' => $usuarios->lastItem()
                ]
            ];

            return ResponseHelper::json($response, 'success', 200, 'Usuários encontrados com sucesso.', $data);
        } catch (NenhumRegistroEncontrado $e) {
            return ResponseHelper::json($response, 'info', 200, $e->getMessage(), []);
        } catch (Exception $e) {
            $log_id = Logger::newLog(LOG_FOLDER, 'ERROR', $e->getMessage(), 'ERROR');

            return ResponseHelper::json($response, 'server_error', 500, 'Ocorreu um erro interno.', null, $log_id);
        }
    }

    public function novoUsuario(Request $request, Response $response): Response {
        $dados = json_decode((string) $request->getBody(), true);

        if (!is_array($dados)) {
            return ResponseHelper::json($response, 'bad_request', 400, 'JSON inválido.');
        }

        $campos = ['nome', 'email', 'senha'];

        foreach ($campos as $campo) {
            if (!isset($dados[$campo]) || empty($dados[$campo])) {
                return ResponseHelper::json($response, 'bad_request', 400, "O campo {$campo} é obrigatório.");
            }
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return ResponseHelper::json($response, 'bad_request', 400, 'O email é inválido.');
        }

        try {
            $this->usuario_service->criarUsuario($dados);
            return ResponseHelper::json($response, 'success', 200, 'Usuário cadastrado com sucesso.');
        } catch (RegistroDuplicadoException $e) {
            return ResponseHelper::json($response, 'conflict', 409, $e->getMessage());
        } catch (Exception $e) {
            $log_id = Logger::newLog(LOG_FOLDER, 'ERROR', $e->getMessage(), 'ERROR');
            return ResponseHelper::json($response, 'server_error', 500, 'Ocorreu um erro interno.', null, $log_id);
        }
    }
}
