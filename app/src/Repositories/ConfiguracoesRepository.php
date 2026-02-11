<?php

declare(strict_types=1);    

class ConfiguracoesRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getUserData(int $userId): ?array
    {
        if($_SESSION['escola_id'] !== null){
            $stmt = $this->pdo->prepare('SELECT * FROM users JOIN escolas ON users.escola_id = escolas.id WHERE users.id = :id');
            $stmt->execute(['id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM users JOIN fornecedores ON users.fornecedor_id = fornecedores.id WHERE users.id = :id');
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateEscolaData(array $data): bool
    {

        if($_SESSION['escola_id'] !== null){
            $sql = "
            UPDATE public.escolas SET
                nome = :nome,
                endereco = :endereco,
                telefone = :telefone,
                responsavel = :responsavel
            WHERE id = :id
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $data['id'],
                ':nome' => $data['nome'],
                ':endereco' => $data['endereco'],
                ':telefone' => $data['telefone'],
                ':responsavel' => $data['responsavel'],
            ]);

            return true;
        };

            $sql = "
            UPDATE public.fornecedores SET
                nome = :nome,
                endereco = :endereco,
                telefone = :telefone,
                responsavel = :responsavel
            WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $data['id'],
                ':nome' => $data['nome'],
                ':endereco' => $data['endereco'],
                ':telefone' => $data['telefone'],
                ':responsavel' => $data['responsavel'],
            ]);

            return true;
    }

}