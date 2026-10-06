<?php

namespace App\Services;

use App\Exceptions\NenhumRegistroEncontrado;
use App\Exceptions\RegistroDuplicadoException;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class UsuarioService
 *
 * Camada de serviço responsável por concentrar a regra de negócio
 * e a manipulação dos dados referentes à entidade de Usuários.
 *
 * @package App\Services
 */
class UsuarioService {
    /**
     * Retorna a lista paginada de usuários com ordenação customizável.
     *
     * Este método utiliza a paginação nativa do Eloquent. O número da página atual
     * é resolvido automaticamente a partir do parâmetro `page` presente na URL
     * da requisição HTTP (ex: `?page=2`).
     *
     * @param int $porPagina Quantidade de registros por página (padrão: 15).
     * @param string $coluna Nome da coluna para ordenação (padrão: 'nome').
     * @param string $direcao Direção da ordenação: 'asc' ou 'desc' (padrão: 'asc').
     * @return LengthAwarePaginator<Usuario> Instância do paginador contendo a coleção de modelos `Usuario`.
     *
     * @throws NenhumRegistroEncontrado Lançada caso a consulta não retorne nenhum registro.
     */
    public function listarUsuarios(int $porPagina = 15, string $coluna = 'nome', string $direcao = 'asc'): LengthAwarePaginator {

        $direcao = strtolower($direcao) === 'desc' ? 'desc' : 'asc';

        $usuarios = Usuario::orderBy($coluna, $direcao)->paginate($porPagina);

        if ($usuarios->isEmpty()) {
            throw new NenhumRegistroEncontrado('Nenhum usuário encontrado.');
        }

        return $usuarios;
    }

    /**
     * Busca um usuário específico com base no valor e na coluna informados.
     *
     * @param int|string $valor O valor a ser buscado (ex: ID, e-mail, CPF, UUID).
     * @param string $coluna O nome da coluna do banco de dados (padrão: 'id').
     * @return Usuario Instância do modelo do usuário encontrado.
     *
     * @throws NenhumRegistroEncontrado Lançada quando nenhum usuário for encontrado.
     */
    public function buscarUsuario(int|string $valor, string $coluna = 'id'): Usuario {

        $usuario = Usuario::where($coluna, $valor)->first();

        if (!$usuario) {
            throw new NenhumRegistroEncontrado("Usuário não encontrado.");
        }

        return $usuario;
    }

    /**
     * Cadastra um novo usuário no sistema.
     *
     * Realiza a verificação prévia para garantir que o e-mail não esteja
     * cadastrado antes de efetuar a inserção.
     *
     * @param array<string, mixed> $dados Atributos do usuário a ser criado.
     * @return Usuario Instância do modelo recém-criado.
     *
     * @throws RegistroDuplicadoException Lançada caso o e-mail informado já exista.
     */
    public function criarUsuario(array $dados): Usuario {

        if (Usuario::where('email', $dados['email'])->exists()) {
            throw new RegistroDuplicadoException('Já existe um usuário cadastrado com este e-mail.');
        }

        return Usuario::create($dados);
    }

    /**
     * Atualiza os dados de um usuário existente.
     *
     * Valida se o e-mail está sendo alterado e se o novo e-mail
     * já pertence a outro usuário cadastrado no sistema.
     *
     * @param int|string $id Identificador único do usuário a ser atualizado.
     * @param array<string, mixed> $dados Atributos a serem atualizados no usuário.
     * @return Usuario Instância do modelo atualizada.
     *
     * @throws NenhumRegistroEncontrado Lançada se o usuário a ser atualizado não for encontrado.
     * @throws RegistroDuplicadoException Lançada caso o novo e-mail já pertença a outro usuário.
     */
    public function atualizarUsuario(int|string $id, array $dados): Usuario {

        $usuario = $this->buscarUsuario($id);

        if (isset($dados['email']) && $dados['email'] !== $usuario->email) {
            $emailExiste = Usuario::where('email', $dados['email'])
                ->where('id', '!=', $usuario->id)
                ->exists();

            if ($emailExiste) {
                throw new RegistroDuplicadoException('O e-mail informado já está em uso por outro usuário.');
            }
        }

        $usuario->update($dados);
        return $usuario;
    }

    /**
     * Remove um usuário do banco de dados.
     *
     * @param int|string $id Identificador único do usuário a ser excluído.
     * @return bool Retorna true se a exclusão for concluída com sucesso.
     *
     * @throws NenhumRegistroEncontrado Lançada caso o usuário a ser removido não exista.
     */
    public function deletarUsuario(int|string $id): bool {
        $usuario = $this->buscarUsuario($id);
        return (bool) $usuario->delete();
    }
}
