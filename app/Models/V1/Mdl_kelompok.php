<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class Mdl_kelompok extends Model
{

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_kelompok($member_id)
    {
        $sql = "
        SELECT kelompok, IFNULL(x.jml,0) as jml FROM kelompok a 
        LEFT JOIN (SELECT id_kelompok, count(1) as jml FROM kategori 
                WHERE is_deleted='no'
                GROUP BY id_kelompok) x ON a.id=x.id_kelompok
        WHERE a.is_deleted='no'  AND a.member_id=?;
        ";
        $query = $this->db->query($sql, $member_id)->getResult();

        return $query;
    }

    public function add_kelompok($mdata = array())
    {
        $kelompok = $this->db->table("kelompok");

        if (!$kelompok->insert($mdata)) {
            $error = [
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
}
