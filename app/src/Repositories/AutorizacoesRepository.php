<?php

declare(strict_types=1);

class AutorizacoesRepository
{
    public function __construct(private \PDO $pdo) {}

    /** @return array<int, array{id:string,nome:string}> */
    public function listarAutorizacoesPorOfertaVencedora(): array
    {
        $sql = "SELECT * 
                    FROM autorizacoes_compra a
                    LEFT JOIN ofertas o ON o.id = a.oferta_id
                    LEFT JOIN requisicoes r ON r.id = o.requisicao_id
                    JOIN escolas e ON e.id = r.escola_id
                    ";
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