<?php
declare(strict_types=1);

class PddeRepository
{
    public function __construct(private \PDO $pdo) {}

    /** @return array<int, array{id:string,nome:string}> */
   public function listarPorEscola(string $escolaId): array
{
    $sql = "
        SELECT
            id,
            nome,
            saldo_inicial,
            saldo_disponivel,
            saldo_bloqueado,
            saldo_gasto,
            created_at
        FROM pddes
        WHERE escola_id = :escola_id
        ORDER BY created_at DESC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['escola_id' => $escolaId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}


    public function pertenceAEscola(string $pddeId, string $escolaId): bool
    {
        $sql = "SELECT 1 FROM pddes WHERE id = :id AND escola_id = :escola_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $pddeId, 'escola_id' => $escolaId]);
        return (bool)$stmt->fetchColumn();
    }

    public function criar(array $data)
    {
        $sql = "INSERT INTO pddes (escola_id, nome, created_at, saldo_inicial, saldo_disponivel)
                VALUES (:escola_id, :nome, :created_at, :saldo_inicial, :saldo_disponivel)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'escola_id' => $data['escola_id'],
            'nome' => $data['nome'],
            'created_at' => $data['created_at'],
            'saldo_inicial' => $data['saldo_inicial'] ?? 0,
            'saldo_disponivel' => $data['saldo_inicial'] ?? 0,
        ]);
    }

    public function buscarPorIdDaEscola(string $pddeId, string $escolaId): ?array
{
    $sql = "
      SELECT id, escola_id, nome, saldo_inicial, saldo_disponivel, saldo_bloqueado, saldo_gasto, created_at
      FROM pddes
      WHERE id = :id AND escola_id = :escola_id
      LIMIT 1
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['id' => $pddeId, 'escola_id' => $escolaId]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    return $row ?: null;
}

    public function atualizarNomeESaldoAtual(string $pddeId, string $escolaId, string $nome, float $saldo_disponivel): void
    {
        $sql = "
        UPDATE pddes
        SET
            nome = :nome,
            saldo_disponivel = :saldo_disponivel
        WHERE id = :id
            AND escola_id = :escola_id
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'nome' => $nome,
            'saldo_disponivel' => $saldo_disponivel,
            'id' => $pddeId,
            'escola_id' => $escolaId,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('PDDE não encontrado ou sem permissão para editar.');
        }
    }

    public function debitarDisponivelEAdicionarGasto(string $pddeId, float $valor): void
    {
        if ($valor <= 0) {
            throw new \InvalidArgumentException('Valor inválido.');
        }

        $stmt = $this->pdo->prepare("SELECT saldo_disponivel FROM pddes WHERE id = :id FOR UPDATE");
        $stmt->execute(['id' => $pddeId]);
        $saldo= $stmt->fetchColumn();

        if ($saldo === false) {
            throw new \RuntimeException('PDDE não encontrado.');
        }


        

        $saldoDisponivel = (float)$saldo;
        if ($saldoDisponivel < $valor) {
            throw new \InvalidArgumentException('Saldo insuficiente no PDDE para concluir a compra.');
        }


        $upd = $this->pdo->prepare("
        UPDATE pddes
        SET
            saldo_gasto = saldo_gasto + :v
        WHERE id = :id
        ");
        $upd->execute(['v' => $valor, 'id' => $pddeId]);
    }

    public function buscarSaldoBloqueadoPorIdRequisicao(string $requisicaoId): ?array
    {
        $sql = "
      SELECT
        p.saldo_bloqueado
      FROM pddes p
      JOIN requisicoes r ON r.pdde_id = p.id
      WHERE r.id = :req_id
      LIMIT 1
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['req_id' => $requisicaoId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;

    }

    public function desbloquearSaldo(string $pddeId, float $valor): void
    {
        $sql = "
        UPDATE pddes
            SET saldo_bloqueado = :saldo_bloqueado
            WHERE id = :pdde_id
        ";  
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'saldo_bloqueado' => $valor,
            'pdde_id' => $pddeId
        ]);
    }

    public function buscarDadosPorID(string $pddeId): ?array
    {
        $sql = "SELECT * FROM pddes WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $pddeId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

}
