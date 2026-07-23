<?php

declare(strict_types=1);

namespace MaxServ\Core\Database;

use PDO;

class Connection
{
    private ?PDO $pdoBacking = null;

    public function __construct(
        private readonly string $host,
        private readonly string $user,
        private readonly string $password,
        private readonly string $database,
    ) {
    }

    public PDO $pdo {
        get => $this->pdoBacking ??= $this->makeConnection();
    }

    private function makeConnection(): PDO
    {
        return new PDO(
            "mysql:host=$this->host;dbname=$this->database",
            $this->user,
            $this->password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
    }
}
