<?php

namespace App\Services;

use App\Exceptions\LoginException;
use App\Exceptions\NenhumRegistroEncontrado;
use App\Helpers\JwtHelper;

class AuthService {
    private UsuarioService $usuario_service;

    public function __construct() {
        $this->usuario_service = new UsuarioService();
    }

    public function login(string $email, string $senha): string {
        try {
            $usuario = $this->usuario_service->buscarUsuario($email, 'email');

            if (!password_verify($senha, $usuario->senha)) {
                throw new LoginException('E-mail ou senha incorretos.');
            }

            return JwtHelper::gerar([
                'id' => $usuario->id,
                'email' => $usuario->email
            ]);

        } catch (NenhumRegistroEncontrado $e) {
            throw new LoginException('E-mail ou senha incorretos.');
        }
    }
}
