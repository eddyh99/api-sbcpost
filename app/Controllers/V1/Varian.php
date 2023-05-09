<?php

namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul Varian
    Desc        : Modul ini digunakan untuk setup varian dan sub varian
    Sub fungsi  : 
        - get_varian        : berfungsi daftar varian
        - add_varian        : berfungsi menyimpan varian
        - update_varian     : berfungsi mengupdate nama varian
------------------------------------------------------------*/

class Varian extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        $this->varian  = model('App\Models\V1\Mdl_varian');
    }

    public function get_varian()
    {
        $userid     = $this->request->getGet('member_id', FILTER_SANITIZE_STRING);
        $result     = $this->varian->get_varian($userid);

        $response = [
            "code"     => "200",
            "error"    => null,
            "message"  =>  $result
        ];

        return $this->respond($response);
    }

    public function add_varian()
    {
        $validation = $this->validation;
        $validation->setRules([
            'member_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Member is required'
                ]
            ],
            'namavarian' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Nama Varian is required'
                ]
            ],
            'subvarian' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Sub Varian is required'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        $data           = $this->request->getJSON();

        $filters = array(
            'member_id'    => FILTER_SANITIZE_STRING,
            'namavarian'   => FILTER_SANITIZE_STRING,
        );

        $filtered = array();
        foreach($data as $key=>$value) {
            if ($key!="subvarian"){
                $filtered[$key] = filter_var($value, $filters[$key]);
            }else{
                $filtered[$key] = $value;
            }
        }
        $data = (object) $filtered;

        
        $mdata = array(
            "member_id"     => $data->member_id,
            "namavarian"    => ucfirst($data->namavarian),
            "created_at"    => date("Y-m-d H:i:s")
        );

        $result = $this->varian->add_varian($mdata,$data->subvarian);
        if (@$result->code == 5055) {
            return $this->respond($result);
        }

        $response = [
            "code"     => "200",
            "error"    => null,
            "message"  => $mdata
        ];
        return $this->respond($response);
    }

    public function update_varian()
    {
        $validation = $this->validation;
        $validation->setRules([
            'varian_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Varian ID is required'
                ]
            ],
            'namavarian' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Nama Varian is required'
                ]
            ],
            'subvarian' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Sub Varian is required'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        $data           = $this->request->getJSON();

        $filters = array(
            'varian_id'     => FILTER_SANITIZE_STRING,
            'namavarian'    => FILTER_SANITIZE_STRING,
            'subvarian'     => FILTER_DEFAULT
        );

        $filtered = array();
        foreach($data as $key=>$value) {
            if ($key!="subvarian"){
                $filtered[$key] = filter_var($value, $filters[$key]);
            }else{
                $filtered[$key] = $value;
            }
        }
        $data = (object) $filtered;

        
        $mdata = array(
            "id"            => $data->varian_id,
            "namavarian"    => ucfirst($data->namavarian),
            "update_at"     => date("Y-m-d H:i:s")
        );

        $result = $this->varian->update_varian($mdata,$data->subvarian);
        if (@$result->code == 5055) {
            return $this->respond($result);
        }

        $response = [
            "code"     => "200",
            "error"    => null,
            "message"  => $mdata
        ];
        return $this->respond($response);
    }
}
