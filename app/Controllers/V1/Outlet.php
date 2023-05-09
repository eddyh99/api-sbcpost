<?php

namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Outlet extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        $this->outlet  = model('App\Models\V1\Mdl_outlet');
    }

    public function get_outlet(){
        $userid     = $this->request->getGet('member_id', FILTER_SANITIZE_STRING);
        $result     = $this->outlet->get_outlet($userid);
        $response = [
            "code"     => "200",
            "error"    => null,
            "message"  =>  $result
        ];

        return $this->respond($response);
    }
}
