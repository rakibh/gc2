<?php

declare(strict_types=1);

namespace Core;

use PDO;

abstract class Repository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
