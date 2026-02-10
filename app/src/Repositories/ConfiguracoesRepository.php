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
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateUserData(int $userId, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        return $stmt->execute([
            'id' => $userId,
            'name' => $data['name'],
            'email' => $data['email']
        ]);
    }
}