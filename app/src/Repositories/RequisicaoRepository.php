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

    public function criar(array $data): string
    {
        $sql = "
            INSERT INTO requisicoes (escola_id, pdde_id, categoria_id, produto, quantidade, status, observacoes)
            VALUES (:escola_id, :pdde_id, :categoria_id, :produto, :quantidade, 'aberta', :observacoes)
            RETURNING id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'escola_id'   => $data['escola_id'],
            'pdde_id'     => $data['pdde_id'],
            'categoria_id'=> $data['categoria_id'],
            'produto'     => $data['produto'],
            'quantidade'  => $data['quantidade'],
            'observacoes' => $data['obs']
        ]);

        return (string)$stmt->fetchColumn();
    }

    public function buscarDetalhe(string $requisicaoId): ?array
{
    $sql = "
      SELECT
        r.id,
        r.produto,
        r.quantidade,
        r.status,
        r.created_at,
        r.escola_id,
        r.pdde_id,
        r.observacoes,
        p.nome AS pdde_nome,
        r.oferta_selecionada_id,
        c.nome AS categoria_nome
      FROM requisicoes r
      INNER JOIN pddes p ON p.id = r.pdde_id
      LEFT JOIN categorias c ON c.id = r.categoria_id
      WHERE r.id = :id
      LIMIT 1
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['id' => $requisicaoId]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    return $row ?: null;
}

public function selecionarOferta(string $requisicaoId, string $ofertaId): void
{
    $sql = "
      UPDATE requisicoes
      SET oferta_selecionada_id = :oferta_id
      WHERE id = :req_id
        AND status = 'aberta'
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        'oferta_id' => $ofertaId,
        'req_id' => $requisicaoId
    ]);

    // Se não atualizou nenhuma linha, ou a req não existe, ou não estava em 'aberta'
    if ($stmt->rowCount() === 0) {
        throw new \RuntimeException('Não foi possível selecionar a oferta. Verifique o status da requisição.');
    }
}

public function iniciarCompra(array $requisicao, array $oferta): void
{
    $sql = "
      UPDATE requisicoes
      SET status = 'em_compra'
      WHERE id = :id
        AND status = 'aberta'
        AND oferta_selecionada_id IS NOT NULL
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['id' => $requisicao['id']]);

    if ($stmt->rowCount() === 0) {
        throw new \RuntimeException('Não foi possível iniciar a compra. Verifique se a requisição está aberta e possui oferta selecionada.');
    }

    $saldo_bloqueado = $oferta['valor_total'];

     $sql = "
      UPDATE pddes
      SET saldo_bloqueado = :saldo_bloqueado,
      saldo_disponivel = saldo_disponivel - :saldo_bloqueado
      WHERE id = :pdde_id
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        'saldo_bloqueado' => $saldo_bloqueado,
        'pdde_id' => $requisicao['pdde_id']
    ]);

      
}

public function listarAbertasParaFornecedor(string $fornecedorId, array $categoriaIds): array
{
    if (empty($categoriaIds)) {
        return [];
    }

    // monta placeholders :c0,:c1,...
    $placeholders = [];
    $params = ['fornecedor_id' => $fornecedorId];

    foreach ($categoriaIds as $i => $cid) {
        $key = "c{$i}";
        $placeholders[] = ":{$key}";
        $params[$key] = $cid;
    }

    $in = implode(',', $placeholders);

    $sql = "
      SELECT
        r.id,
        r.produto,
        r.quantidade,
        r.created_at,
        r.status,
        c.nome AS categoria_nome
      FROM requisicoes r
      LEFT JOIN categorias c ON c.id = r.categoria_id
      WHERE r.status = 'aberta'
        AND r.categoria_id IN ($in)
        AND NOT EXISTS (
          SELECT 1 FROM ofertas o
          WHERE o.requisicao_id = r.id
            AND o.fornecedor_id = :fornecedor_id
        )
      ORDER BY r.created_at DESC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function buscarParaConclusao(string $requisicaoId, string $escolaId): ?array
{
    $sql = "
      SELECT
        r.id,
        r.status,
        r.quantidade,
        r.pdde_id,
        r.oferta_selecionada_id,
        r.produto,
        e.nome AS escola_nome,
        f.nome AS fornecedor_nome,
        o.valor_unitario
      FROM requisicoes r
      JOIN ofertas o ON o.id = r.oferta_selecionada_id
      JOIN escolas e ON e.id = r.escola_id
      JOIN fornecedores f ON f.id = o.fornecedor_id
      WHERE r.id = :rid
        AND r.escola_id = :eid
      LIMIT 1
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['rid' => $requisicaoId, 'eid' => $escolaId]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    return $row ?: null;
}

public function marcarComoConcluida(string $requisicaoId): void
{
    $sql = "UPDATE requisicoes SET status = 'concluida' WHERE id = :id AND status = 'em_compra'";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['id' => $requisicaoId]);

    if ($stmt->rowCount() === 0) {
        throw new \RuntimeException('Não foi possível concluir a requisição.');
    }
}

}
