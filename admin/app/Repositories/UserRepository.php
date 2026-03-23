<?php

declare(strict_types=1);

class UserRepository
{
    public function __construct(private Database $db)
    {
    }

    public function findByLogin(string $login): ?array
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM zugriff1 WHERE login = :login OR name = :login LIMIT 1');
        $stmt->execute(['login' => $login]);
        return $stmt->fetch() ?: null;
    }

    public function findByRememberToken(string $token): ?array
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM zugriff1 WHERE remember_token = :token AND token_expiry >= :now LIMIT 1');
        $stmt->execute(['token' => $token, 'now' => time()]);
        return $stmt->fetch() ?: null;
    }

    public function updateRememberToken(int $userId, ?string $token, ?int $expiry): void
    {
        $stmt = $this->db->pdo()->prepare('UPDATE zugriff1 SET remember_token = :token, token_expiry = :expiry WHERE userid = :id');
        $stmt->execute(['token' => $token, 'expiry' => $expiry, 'id' => $userId]);
    }
}
