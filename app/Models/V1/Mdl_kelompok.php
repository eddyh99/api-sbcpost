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
        SELECT `id`,`member_id`,`kelompok`,`is_deleted`,`created_at`,`update_at` , IFNULL(x.jml,0) as jml FROM kelompok a 
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
                "messages"    => $this->db->error()
            ];
            return (object) $error;
        }
    }


    public function update_kelompok($data, $id)
    {
        $tblkategori    = $this->db->table("kelompok");

        if (!$tblkategori->update($data, "id=" . $id)) {
            $error = [
                "code"       => "5055",
                "error"      => "10",
                "messages"    => $this->db->error()
            ];
            return (object) $error;
        }
    }

    public function delete_kelompok($data, $id)
    {
        $tblkelompok      = $this->db->table("kelompok");
        $tblkategori   = $this->db->table("kategori");

        $this->db->transStart();
        if (!$tblkelompok->update($data, "id=" . $id)) {
            $psn = "Periksa data Kelompok";
        }

        $sql = " SELECT * FROM kategori a WHERE a.id_kelompok=?;";
        $query = $this->db->query($sql, $id)->getResult();
        if ($query) { // Jika ada data
            if (!$tblkategori->update($data, "id_kelompok=" . $id)) {
                $psn = "Periksa data Kategori";
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            $error = [
                "code"      => "5055",
                "error"     => "1060",
                "message"   => $psn
            ];
            return (object)$error;
        } else {
            $this->db->transCommit();
        }
    }
}
