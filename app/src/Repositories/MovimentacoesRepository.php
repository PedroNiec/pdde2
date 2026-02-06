<?php

declare(strict_types=1);

class MovimentacoesRepository
{
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function criarMovimentacao($dados)
    {

        $sql = "INSERT INTO movimentacoes (escola_id, pdde_id, produto, valor_total)
                VALUES (:escola_id, :pdde_id, :produto, :valor_total)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'pdde_id' => $dados['pdde_id'],
            'escola_id' => $dados['escola_id'],
            'produto' => $dados['produto'],
            'valor_total' => $dados['valor_total'],
        ]);
    }
}