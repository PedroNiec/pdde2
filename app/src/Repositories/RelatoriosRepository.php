<?php

declare(strict_types=1);

class RelatoriosRepository
{
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function relatorioMensal(string $mes)
    {

        $inicio = $mes . '-01';
        $fim = date('Y-m-d', strtotime($inicio . ' +1 month'));

        $sql = "SELECT *
            FROM movimentacoes
            WHERE created_at >= :inicio
              AND created_at <  :fim
            ORDER BY created_at ASC";



        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':inicio' => $inicio,
            ':fim'    => $fim,
        ]);



        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


}