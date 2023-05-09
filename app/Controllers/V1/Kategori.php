<?php

namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul Kategori
    Desc        : Modul ini digunakan untuk operasi pada kategori
    Sub fungsi  : 
        - get_data_kategori : berfungsi daftar kategori dan produk yang ada di dalamnya
        - add_kategori      : berfungsi menyimpan kategori
        - update_kategori   : berfungsi mengupdate nama kategori
------------------------------------------------------------*/

class Kategori extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        $this->kategori  = model('App\Models\V1\Mdl_kategori');
    }

    // public function get_data_kategori()
    // {
    //     $userid     = $this->request->getGet('userid', FILTER_SANITIZE_STRING);
    //     $result     = $this->kategori->get_kategori($userid);

    //     $response = [
    //         "code"     => "200",
    //         "error"    => null,
    //         "message"  =>  $result
    //     ];

    //     return $this->respond($response);
    // }

    public function add_kategori()
    {
        $validation = $this->validation;
        $validation->setRules([
            'member_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Member is required'
                ]
            ],
            'kategori' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Kategori is required'
                ]
            ],
            'kelompok_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Kelompok is required'
                ]
            ],
            'outlet' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Outlet is required'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        $data           = $this->request->getJSON();

        $filters = array(
            'member_id'    => FILTER_SANITIZE_STRING,
            'kelompok_id'  => FILTER_SANITIZE_STRING,
            'kategori'     => FILTER_SANITIZE_STRING,
            'outlet'       => FILTER_DEFAULT
        );

        $filtered = array();
        foreach($data as $key=>$value) {
            if ($key!="outlet"){
                $filtered[$key] = filter_var($value, $filters[$key]);
            }else{
                $filtered[$key] = $value;
            }
        }
        $data = (object) $filtered;

        
        $mdata = array(
            "member_id"     => $data->member_id,
            "id_kelompok"   => $data->kelompok_id,
            "kategori"      => $data->kategori,
            "created_at"    => date("Y-m-d H:i:s")
        );

        $result = $this->kategori->add_kategori($mdata,$data->outlet);
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

    public function update_kategori()
    {
        $validation = $this->validation;
        $validation->setRules([
            'kategori_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Kategori ID is required'
                ]
            ],
            'kategori' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Kategori is required'
                ]
            ],
            'kelompok_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Kelompok is required'
                ]
            ],
            'outlet' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Outlet is required'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        $data           = $this->request->getJSON();

        $filters = array(
            'kategori_id'  => FILTER_SANITIZE_STRING,
            'kelompok_id'  => FILTER_SANITIZE_STRING,
            'kategori'     => FILTER_SANITIZE_STRING,
            'outlet'       => FILTER_DEFAULT
        );

        $filtered = array();
        foreach($data as $key=>$value) {
            if ($key!="outlet"){
                $filtered[$key] = filter_var($value, $filters[$key]);
            }else{
                $filtered[$key] = $value;
            }
        }
        $data = (object) $filtered;

        
        $mdata = array(
            "id"            => $data->kategori_id,
            "id_kelompok"   => $data->kelompok_id,
            "kategori"      => $data->kategori,
            "update_at"     => date("Y-m-d H:i:s")
        );

        $result = $this->kategori->update_kategori($mdata,$data->outlet);
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
