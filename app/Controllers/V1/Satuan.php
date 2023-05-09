<?php

namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul Satuan
    Desc        : Modul ini digunakan untuk operasi satuan
    Sub fungsi  : 
        - get_satuan        : berfungsi daftar satuan yang ada (member)
        - add_satuan        : berfungsi menyimpan satuan (admin sbc)
        - update_satuan     : berfungsi mengupdate nama satuan (admin sbc)
------------------------------------------------------------*/

class Satuan extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        $this->satuan  = model('App\Models\V1\Mdl_satuan');
    }

    public function get_satuan()
    {
        $result     = $this->satuan->get_satuan();

        $response = [
            "code"     => "200",
            "error"    => null,
            "message"  =>  $result
        ];

        return $this->respond($response);
    }
}
