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

    public function totalPorEscola($escolaId)
    {
        $sql = "
        SELECT
          *
        FROM movimentacoes
        WHERE escola_id = :escola_id
      ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['escola_id' => $escolaId]);
        $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}