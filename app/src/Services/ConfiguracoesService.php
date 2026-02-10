<?php

declare(strict_types=1);    

require_once __DIR__ . '/../core/bootstrap.php';    

class ConfiguracoesService
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

    public function updateUserData(int $userId, string $name, string $email): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        return $stmt->execute([
            'id' => $userId,
            'name' => $name,
            'email' => $email
        ]);
    }
}