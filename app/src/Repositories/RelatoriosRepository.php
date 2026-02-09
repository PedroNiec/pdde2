<?php

declare(strict_types=1);

class RelatoriosRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function relatorioMensal(string $mes, string $escola_id)
    {
        $inicio = $mes . '-01';
        $fim = date('Y-m-d', strtotime($inicio . ' +1 month'));

        $sql = "SELECT *
            FROM movimentacoes
            WHERE created_at >= :inicio
              AND created_at <  :fim
              AND escola_id = :escola_id
            ORDER BY created_at ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':inicio' => $inicio,
            ':fim'    => $fim,
            ':escola_id' => $escola_id
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

     public function relatorioPorPdde(string $pddeId): ?array
    {
        $sql = "
        SELECT *
        FROM movimentacoes m
        WHERE m.pdde_id = :pdde_id
        ORDER BY m.created_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pdde_id' => $pddeId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


}