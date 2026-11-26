<?php

namespace App\Models;

use App\Core\Database;
use PDO;

require_once __DIR__ . '/../Core/Database.php';

abstract class BaseModel
{
    protected PDO $db;

    public function __construct(?Database $database = null)
    {
        $this->db = ($database ?? Database::getInstance())->getConnection();
    }
}
