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

    public function add_kategori($data, $outletid)
    {
        $tblkategori    = $this->db->table("kategori");
        $tbloutlet      = $this->db->table("kategori_outlet");

        $this->db->transStart();
        if (!$tblkategori->insert($data)) {
            $psn = array("error" => "Periksa data input");
            // throw new Exception("Email already used");
        }

        $id = $this->db->insertID();
        $mdata=array();
        foreach ($outletid as $dt){
            $temp["id_kategori"]=$id;
            $temp["id_outlet"]=$dt->id;
            $temp["show_menu"]=$dt->showmenu;
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
        if (!$tblkategori->update($data,"id=".$data["id"])) {
            $psn = array("error" => "Periksa data input");
            // throw new Exception("Email already used");
        }

        $tbloutlet->delete(["id_kategori"=>$data["id"]]);
        $mdata=array();
        foreach ($outletid as $dt){
            $temp["id_kategori"]=$data["id"];
            $temp["id_outlet"]=$dt->id;
            $temp["show_menu"]=$dt->showmenu;
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
}
