<?php

namespace App\Models;

use PDO;
use App\Core\Database;

require_once __DIR__ . '/../Core/Database.php';

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}