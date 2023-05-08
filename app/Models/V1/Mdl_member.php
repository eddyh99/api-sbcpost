<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class Mdl_member extends Model
{

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function add($data=array()) {
        $tblmember = $this->db->table("member");
        $this->db->transStart();
            if (!$tblmember->insert($data)){
                $psn=array("1",$this->db->error());
                throw new Exception("Email already used");
            }
            
            $id = $this->db->insertID();
            $mdata = array(
                "status" => "new",
                "token" => $this->generate_token($id),
                "date_created" => date("Y-m-d H:i:s"),
                );
        
            $tblmember->where("id", $id);
            if (!$tblmember->update($mdata)){
                $psn=array("2",$this->db->error());
            };
        $this->db->transComplete();
        
        if ($this->db->transStatus() === false) {
             $this->db->transRollback();
             $error=[
                 "code"      => "1060",
	             "error"     => "1060",
	             "message"   => $psn
	        ];
             return (object)$error;
        }else {
            $this->db->transCommit();
            return (object)$mdata;
        }
    }

    
    private function generate_token($id) {
        require_once APPPATH . "ThirdParty/Hashids/HashidsInterface.php";
        require_once APPPATH . "ThirdParty/Hashids/Hashids.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/MathInterface.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/Gmp.php";

        $hashids =  new \Hashids\Hashids('', 48, 'abcdefghijklmnopqrstuvwxyz1234567890'); 
        return $hashids->encode($id, time(), rand()); 
    }
    
}