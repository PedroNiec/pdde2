<?php
declare(strict_types=1);

class RequisicaoRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lista todas as requisições de uma escola
     */
    public function listarPorEscola(string $escolaId): array
    {
        $sql = "
            SELECT
                r.id,
                r.produto,
                r.quantidade,
                r.status,
                r.created_at,
                p.nome AS pdde_nome
            FROM requisicoes r
            INNER JOIN pddes p ON p.id = r.pdde_id
            WHERE r.escola_id = :escola_id
            ORDER BY r.created_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'escola_id' => $escolaId
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
