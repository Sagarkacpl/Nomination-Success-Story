<?php

namespace App\Core;

use App\Config\Database;
use PDO;

/**
 * Base Model
 * Gives every model a shared PDO connection via App\Config\Database.
 */
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
