<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class Mdl_kategori extends Model
{

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_kategori($member_id)
    {
        // LEFT JOIN (SELECT id_kelompok, count(1) as jml FROM kategori 
        //         WHERE is_deleted='no'
        //         GROUP BY id_kelompok) x ON a.id=x.id_kelompok
        // $sql = "
        // SELECT * FROM kategori a 
        // WHERE a.is_deleted='no' AND a.member_id=?;
        // ";

        $sql = "
            SELECT 
            a.id,
            a.member_id,
            a.id_kelompok,
            a.kategori,
            a.is_deleted,
            a.created_at,
            a.update_at,
            GROUP_CONCAT(b.id_outlet SEPARATOR ',') as outlet,
            IFNULL(c.jml,0) as jml_produk
            FROM kategori a 
            LEFT JOIN kategori_outlet b ON a.id = b.id_kategori
            LEFT JOIN (
                SELECT id_kategori, count(1) as jml FROM produk_outlet 
                GROUP BY id_produk
            ) c ON a.id=c.id_kategori
            WHERE a.is_deleted='no' AND a.member_id=?
            GROUP BY a.id;
        ";
        $query = $this->db->query($sql, $member_id)->getResult();

        return $query;
    }

    public function get_kategori_byid($id)
    {
        // $sql = "
        // SELECT a.id, a.member_id, a.id_kelompok, a.kategori, GROUP_CONCAT(b.id_outlet SEPARATOR ',') AS outlet 
        // FROM kategori a INNER JOIN kategori_outlet b ON a.id=b.id_kategori
        // WHERE a.id = ?;
        // ";
        $sql = "
        SELECT 
        a.id,
        a.member_id,
        a.id_kelompok,
        a.kategori,
        a.is_deleted,
        a.created_at,
        a.update_at,
        GROUP_CONCAT(b.id_outlet SEPARATOR ',') as outlet,
        IFNULL(c.jml,0) as jml_produk
        FROM kategori a 
        LEFT JOIN kategori_outlet b ON a.id = b.id_kategori
        LEFT JOIN (
            SELECT id_kategori, count(1) as jml FROM produk_outlet 
            GROUP BY id_produk
        ) c ON a.id=c.id_kategori
        WHERE a.is_deleted='no' AND a.id = ?
        GROUP BY a.id;
        ";
        $query = $this->db->query($sql, $id)->getRow();

        return $query;
    }

    public function add_kategori($data, $outletid)
    {
        $tblkategori    = $this->db->table("kategori");
        $tbloutlet      = $this->db->table("kategori_outlet");

        $this->db->transStart();
        if (!$tblkategori->insert($data)) {
            $psn = array("error" => "Periksa data input");
        }

        $id = $this->db->insertID();
        $mdata = array();
        foreach ($outletid as $dt) {
            $temp["id_kategori"] = $id;
            $temp["id_outlet"] = $dt->id_outlet;
            $temp["show_menu"] = $dt->show;
            array_push($mdata, $temp);
        }
        if (!$tbloutlet->insertBatch($mdata)) {
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

    public function update_kategori($data, $outletid)
    {
        $tblkategori    = $this->db->table("kategori");
        $tbloutlet      = $this->db->table("kategori_outlet");

        $this->db->transStart();
        if (!$tblkategori->update($data, "id=" . $data["id"])) {
            $psn = array("error" => "Periksa data input");
            // throw new Exception("Email already used");
        }

        $tbloutlet->delete(["id_kategori" => $data["id"]]);
        $mdata = array();
        foreach ($outletid as $dt) {
            $temp["id_kategori"] = $data["id"];
            $temp["id_outlet"] = $dt->id;
            $temp["show_menu"] = $dt->showmenu;
            array_push($mdata, $temp);
        }
        if (!$tbloutlet->insertBatch($mdata)) {
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

    public function delete_kategori($data, $kategori_id)
    {
        $tblkategori    = $this->db->table("kategori");
        $tbloutlet      = $this->db->table("kategori_outlet");

        $this->db->transStart();
        if (!$tblkategori->update($data, "id=" . $kategori_id)) {
            $psn = array("error" => "Periksa data input");
            // throw new Exception("Email already used");
        }
        $tbloutlet->delete(["id_kategori" => $kategori_id]);
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
            return;
        }
    }
}
