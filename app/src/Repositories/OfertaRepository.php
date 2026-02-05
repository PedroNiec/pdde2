<?php
declare(strict_types=1);

class OfertaRepository
{
    public function __construct(private \PDO $pdo) {}

    /** @return array<int, array<string,mixed>> */
    public function listarPorRequisicao(string $requisicaoId): array
    {
        $sql = "
          SELECT
            o.id,
            o.valor_unitario,
            o.created_at,
            o.valor_total,
            f.nome AS fornecedor_nome
          FROM ofertas o
          INNER JOIN fornecedores f ON f.id = o.fornecedor_id
          WHERE o.requisicao_id = :req_id
          ORDER BY o.valor_unitario ASC, o.created_at ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['req_id' => $requisicaoId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function pertenceARequisicao(string $ofertaId, string $requisicaoId): bool
{
    $sql = "SELECT 1 FROM ofertas WHERE id = :id AND requisicao_id = :req LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['id' => $ofertaId, 'req' => $requisicaoId]);
    return (bool)$stmt->fetchColumn();
}

public function fornecedorJaOfertou(string $requisicaoId, string $fornecedorId): bool
{
    $sql = "SELECT 1 FROM ofertas WHERE requisicao_id = :rid AND fornecedor_id = :fid LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['rid' => $requisicaoId, 'fid' => $fornecedorId]);
    return (bool)$stmt->fetchColumn();
}

public function criar(string $requisicaoId, string $fornecedorId, float $valorUnitario, float $valorTotal, string $marca): string
{
    $sql = "
      INSERT INTO ofertas (requisicao_id, fornecedor_id, valor_unitario, valor_total, marca)
      VALUES (:rid, :fid, :valor_unitario, :valor_total, :marca)
      RETURNING id
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        'rid' => $requisicaoId,
        'fid' => $fornecedorId,
        'valor_unitario' => $valorUnitario,
        'valor_total' => $valorTotal,
        'marca' => $marca
    ]);
    return (string)$stmt->fetchColumn();
}

public function ofertasPorFornecedor(string $fornecedorId): array
{
    $sql = "
      SELECT
        o.id,
        o.valor_unitario,
        o.created_at,
        r.produto,
        r.quantidade,
        c.nome AS categoria
      FROM ofertas o
      INNER JOIN requisicoes r ON r.id = o.requisicao_id
      LEFT JOIN fornecedor_categorias fc ON fc.fornecedor_id = o.fornecedor_id
      LEFT JOIN categorias c ON c.id = fc.categoria_id
      WHERE o.fornecedor_id = :fid 
      ORDER BY o.created_at DESC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['fid' => $fornecedorId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function buscarSelecionadaPorRequisicao(string $requisicaoId): ?array
  {
      $sql = "
        SELECT
          o.id,
          o.valor_unitario,
          o.valor_total,
          f.nome AS fornecedor_nome
        FROM ofertas o
        INNER JOIN requisicoes r ON r.oferta_selecionada_id = o.id
        INNER JOIN fornecedores f ON f.id = o.fornecedor_id
        WHERE r.id = :rid
        LIMIT 1
      ";

      $stmt = $this->pdo->prepare($sql);
      $stmt->execute(['rid' => $requisicaoId]);
      $row = $stmt->fetch(\PDO::FETCH_ASSOC);

      return $row ?: null;

  }
}