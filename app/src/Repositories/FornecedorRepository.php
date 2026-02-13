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

    public function criarFornecedorBD($data)
    {
        $this->pdo->beginTransaction();

        $st = $this->pdo->prepare("
            INSERT INTO fornecedores (nome, cnpj_cpf, endereco, telefone, responsavel)
            VALUES (:nome, :cnpj_cpf, :endereco, :telefone, :responsavel)
            RETURNING id
          ");

        $st->execute([
            ':nome' => $data['nome'],
            ':cnpj_cpf' => ($data['cnpjCpf'] !== '' ? $data['cnpjCpf'] : null),
            ':endereco' => ($data['endereco'] !== '' ? $data['endereco'] : null),
            ':telefone' => ($data['telefone'] !== '' ? $data['telefone'] : null),
            ':responsavel' => ($data['responsavel'] !== '' ? $data['responsavel'] : null),
        ]);

        $result = $st->fetch(PDO::FETCH_ASSOC);

        $this->pdo->commit();

        return $result['id'];
    }

    public function criarVinculoCategoria($data, $idFornecedor)
    {
        $this->pdo->beginTransaction();

        $categorias = $data['categorias'];

        $stLink = $this->pdo->prepare("
            INSERT INTO fornecedor_categorias (fornecedor_id, categoria_id)
            VALUES (:fornecedor_id, :categoria_id)
          ");
        foreach ($categorias as $catId) {
            $stLink->execute([
                ':fornecedor_id' => $idFornecedor,
                ':categoria_id' => (string)$catId,
            ]);
        }

        $this->pdo->commit();
    }

    public function criarUserFornecedor($data, $idFornecedor)
    {
        try {


            $stUser = $this->pdo->prepare("
              INSERT INTO users (name, email, password_hash, fornecedor_id, role, active)
              VALUES (:nome, :email, :password_hash, :fornecedor_id, 'fornecedor', TRUE)
            ");

            $stUser->execute([
                ':nome' => $data['nome'],
                ':email' => $data['email'],
                ':password_hash' => $data['senha'],
                ':fornecedor_id' => $idFornecedor,
            ]);


        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e; // ou error_log($e->getMessage());
        }
    }

}
