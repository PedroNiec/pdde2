<?php

declare(strict_types=1);

namespace Validators;

class FornecedorValidator
{
    public function dataValidator($dados)
    {


        $nome        = trim((string)($dados['nome'] ?? ''));
        $emailRaw    = trim((string)($dados['email'] ?? ''));
        $email       = mb_strtolower($emailRaw);
        $senha       = (string)($dados['senha'] ?? '');
        $categorias  = $dados['categorias'] ?? [];

        if ($nome === '' || $email === '' || $senha === '') {
            http_response_code(422);
            exit('Preencha nome, e-mail e senha.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            exit('E-mail inválido.');
        }
        if (strlen($senha) < 6) {
            http_response_code(422);
            exit('Senha deve ter pelo menos 6 caracteres.');
        }
        if (!is_array($categorias) || count($categorias) === 0) {
            http_response_code(422);
            exit('Selecione ao menos uma categoria.');
        }

        $uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        $categorias = array_values(array_unique(array_filter(array_map(
            fn($v) => trim((string)$v),
            $categorias
        ), fn($v) => $v !== '' && preg_match($uuidRegex, $v))));

        if (count($categorias) === 0) {
            http_response_code(422);
            exit('Categorias inválidas.');
        }

    }
}