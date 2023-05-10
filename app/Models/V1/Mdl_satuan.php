<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class Mdl_satuan extends Model
{

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_satuan()
    {
        $sql = "
        SELECT * FROM settings_satuan WHERE is_deleted='no'
        ";
        $query = $this->db->query($sql)->getResult();

        return $query;
    }
}
