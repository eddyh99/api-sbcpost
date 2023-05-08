<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class ValidateToken extends Model
{

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    function checkAPIkey($token)
    {
        $sql="SELECT * FROM settings WHERE sha1(CONCAT(appid,secret))=?";
        $data=$this->db->query($sql,array($token))->getRow();
        if (!$data) {
            throw new Exception("invalid API Key, please check your API Key");
        }
        return $data;
    }
}