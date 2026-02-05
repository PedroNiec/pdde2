<?php

declare(strict_types=1);

class AutorizacoesRepository
{
    public function __construct(private \PDO $pdo) {}

    /** @return array<int, array{id:string,nome:string}> */
    public function listarAutorizacoesPorOferta(): array
    {
        $sql = "SELECT id, nome FROM categorias ORDER BY nome ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function criarAutorizacao(string $requisicaoId, string $ofertaId, string $publicUrl): void
    {
        $sql = "INSERT INTO autorizacoes_compra (requisicao_id, oferta_id, data_criacao, public_url)
                VALUES (:requisicao_id, :oferta_id, NOW(), :public_url)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':requisicao_id' => $requisicaoId,
            ':oferta_id' => $ofertaId,
            ':public_url' => $publicUrl
        ]);
    }
}