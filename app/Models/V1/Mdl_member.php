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

    public function add($data = array())
    {
        $tblmember = $this->db->table("member");
        $this->db->transStart();
        if (!$tblmember->insert($data)) {
            $psn = array("error" => "Email already used");
            // throw new Exception("Email already used");
        }

        $id = $this->db->insertID();
        $mdata = array(
            "status" => "new",
            "token" => $this->generate_token($id),
            "created_at" => date("Y-m-d H:i:s"),
        );

        $tblmember->where("id", $id);
        if (!$tblmember->update($mdata)) {
            $psn = $this->db->error();
        };
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            $error = [
                "code"      => "1060",
                "error"     => "1060",
                "message"   => $psn
            ];
            return (object)$error;
        } else {
            $this->db->transCommit();
            return (object)$mdata;
        }
    }


    private function generate_token($id)
    {
        require_once APPPATH . "ThirdParty/Hashids/HashidsInterface.php";
        require_once APPPATH . "ThirdParty/Hashids/Hashids.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/MathInterface.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/Gmp.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/Bc.php";

        $hashids =  new \Hashids\Hashids('', 48, 'abcdefghijklmnopqrstuvwxyz1234567890');
        return $hashids->encode($id, time(), rand());
    }


    public function getby_token($token)
    {
        $sql = "SELECT `id`,`appid`,`email`,`passwd`,`nama`,`token`,`status`,`created_at`,`update_at` FROM `member` WHERE `token`= ?";

        $query = $this->db->query($sql, $token)->getRow();

        if (!$query) {
            $error = [
                "code"       => "5051",
                "error"      => "02",
                "message"    => "Invalid Token/expired token"
            ];
            return (object) $error;
        }

        return $query;
    }

    public function activate($id)
    {
        $tblmember = $this->db->table("member");
        $mdata = array(
            "token" => NULL,
            "status" => "active",
        );
        $tblmember->where("id", $id);
        $tblmember->where("status", "new");
        $tblmember->update($mdata);
        if ($this->db->affectedRows() == 0) {
            $error = [
                "code"       => "5051",
                "error"      => "03",
                "message"    => "Activation failed, Invalid token"
            ];
            return (object) $error;
        }
    }

    public function getby_email($email, $appid)
    {
        $sql = "SELECT `id`,`appid`,`email`,`passwd`,`nama`,`token`,`status`,`created_at`,`update_at` FROM `member` WHERE `email`=? AND `appid`=?  AND `status`='active'";
        $query = $this->db->query($sql, [$email, $appid])->getRow();
        if (!$query) {
            $error = [
                "code"       => "5051",
                "error"      => "04",
                "message"    => "Invalid email"
            ];
            return (object) $error;
        }

        return $query;
    }


    public function resetToken($email)
    {
        $sql = "SELECT id FROM member WHERE email=?";
        if (!$this->db->query($sql, $email)->getRow()) {
            $error = [
                "code"       => "5051",
                "error"      => "07",
                "message"    => "Member not found"
            ];
            return (object) $error;
        }
        $id = $this->db->query($sql, $email)->getRow()->id;

        $member = $this->db->table("member");
        $token = $this->generate_token($id);

        $member->where('email', $email);
        $member->set("token", $token);
        $member->update();
        return $token;
    }

    public function change_password($mdata, $where)
    {
        $member = $this->db->table("member");
        $member->where($where);
        $member->update($mdata);
        if ($this->db->affectedRows() == 0) {
            $error = [
                "code"       => "5051",
                "error"      => "08",
                "message"    => "Failed to change password, please try again later"
            ];
            return (object) $error;
        }
    }

    public function createOutlet($mdata = array())
    {
        $outlet = $this->db->table("outlet");
        if (!$outlet->insert($mdata)) {
            $error = [
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
}
