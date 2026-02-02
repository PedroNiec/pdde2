<?php
declare(strict_types=1);

class CategoriaRepository
{
    public function __construct(private \PDO $pdo) {}

    /** @return array<int, array{id:string,nome:string}> */
    public function listarTodas(): array
    {
        $sql = "SELECT id, nome FROM categorias ORDER BY nome ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function existe(string $categoriaId): bool
    {
        $sql = "SELECT 1 FROM categorias WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $categoriaId]);
        return (bool)$stmt->fetchColumn();
    }
}
