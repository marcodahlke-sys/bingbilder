<?php

declare(strict_types=1);

class CounterRepository
{
    public function __construct(private Database $db)
    {
    }

    public function get(): int
    {
        return (int) ($this->db->pdo()->query('SELECT `count` FROM counter LIMIT 1')->fetchColumn() ?: 0);
    }
}
