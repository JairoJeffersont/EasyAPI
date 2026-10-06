<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper {
    private const ALGORITHM = 'HS256';

    public static function gerar(array $dados, int $expiracao = 3600): string {
        $agora = time();

        $payload = [
            'iat' => $agora,
            'exp' => $agora + $expiracao,
            'data' => $dados
        ];

        return JWT::encode($payload, $_ENV['JWT_SECRET'], self::ALGORITHM);
    }

    public static function validar(string $token): object {
        return JWT::decode($token, new Key($_ENV['JWT_SECRET'], self::ALGORITHM));
    }
}
