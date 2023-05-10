<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class Mdl_varian extends Model
{

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_varian($member_id)
    {
        $sql = "
        SELECT a.id, a.namavarian, GROUP_CONCAT(b.subvarian SEPARATOR ',') AS subvarian 
        FROM varian a INNER JOIN subvarian b ON a.id=b.varian_id
        WHERE member_id=? AND a.is_deleted='no';
        ";
        $query = $this->db->query($sql, $member_id)->getResult();
        return $query;
    }

    public function add_varian($data, $subvarian)
    {
        $tblvarian      = $this->db->table("varian");
        $tblsubvarian   = $this->db->table("subvarian");

        $this->db->transStart();
        if (!$tblvarian->insert($data)) {
            $psn = array("error" => "Periksa data input");
            // throw new Exception("Email already used");
        }

        $id = $this->db->insertID();
        $mdata = array();
        foreach ($subvarian as $dt) {
            $temp["varian_id"] = $id;
            $temp["subvarian"] = ucfirst($dt);
            array_push($mdata, $temp);
        }
        if (!$tblsubvarian->insertBatch($mdata)) {
            $psn = array("error" => "periksa input subvarian");
            // throw new Exception("Email already used");
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
            return (object)$mdata;
        }
    }

    public function update_varian($data, $subvarian)
    {
        $tblvarian      = $this->db->table("varian");
        $tblsubvarian   = $this->db->table("subvarian");

        $this->db->transStart();
        if (!$tblvarian->update($data, "id=" . $data["id"])) {
            $psn = array("error" => "Periksa data input");
            // throw new Exception("Email already used");
        }

        $tblsubvarian->delete(["varian_id" => $data["id"]]);
        $mdata = array();
        foreach ($subvarian as $dt) {
            $temp["varian_id"] = $data["id"];
            $temp["subvarian"] = ucfirst($dt);
            array_push($mdata, $temp);
        }
        if (!$tblsubvarian->insertBatch($mdata)) {
            $psn = array("error" => "periksa input outlet");
            // throw new Exception("Email already used");
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
            return (object)$mdata;
        }
    }
}
