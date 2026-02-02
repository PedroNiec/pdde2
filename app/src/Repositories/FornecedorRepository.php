<?php
declare(strict_types=1);

class FornecedorRepository
{
    public function __construct(private \PDO $pdo) {}

    /** @return string[] lista de UUIDs categoria_id */
    public function listarCategoriaIds(string $fornecedorId): array
    {
        $sql = "SELECT categoria_id FROM fornecedor_categorias WHERE fornecedor_id = :fid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['fid' => $fornecedorId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($r) => (string)$r['categoria_id'], $rows);
    }
}
