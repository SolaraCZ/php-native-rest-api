<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

class Migrator
{
    public function __construct(private PDO $db)
    {
    }

    public function migrate(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                completed INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ";

        $this->db->exec($sql);
    }
}