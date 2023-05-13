<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class Mdl_outlet extends Model
{

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_outlet($member_id)
    {
        $sql = "
        SELECT * FROM outlet WHERE member_id=? AND is_deleted='no'
        ";
        $query = $this->db->query($sql, $member_id)->getResult();

        return $query;
    }

    public function get_kategori_outlet()
    {
        $sql = "
        SELECT * FROM `kategori_outlet`
        ";
        $query = $this->db->query($sql)->getResult();

        return $query;
    }

    public function add_outlet($mdata = array())
    {
        $outlet = $this->db->table("outlet");
        if (!$outlet->insert($mdata)) {
            $error = [
                "code"       => "5055",
                "error"      => "10",
                "messages"    => $this->db->error()
            ];
            return (object) $error;
        }
    }

    public function delete_list_ot_kategori($data)
    {
        $tbl_kt_ot = $this->db->table("kategori_outlet");

        if (!$tbl_kt_ot->delete($data)) {
            $error = [
                "code"      => "5055",
                "error"     => "1060",
                "message"   => $this->db->error()
            ];
            return (object)$error;
        }
    }
}
