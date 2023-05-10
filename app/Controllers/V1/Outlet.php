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

    public function get_outlet()
    {
        $userid     = $this->request->getGet('member_id', FILTER_SANITIZE_STRING);
        $result     = $this->outlet->get_outlet($userid);
        if (@$result) {
            $response = [
                "code"     => "200",
                "error"    => null,
                "messages"  =>  $result
            ];

            return $this->respond($response);
        }
    }

    public function add_outlet()
    {
        $validation = $this->validation;
        $validation->setRules([
            'member_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Member is required'
                ]
            ],
            'namaoutlet' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Nama is required'
                ]
            ],
            'alamat' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Alamat is required'
                ]
            ],
            'kota' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Kota is required'
                ]
            ],
            'telp' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'No. Telepon is required'
                ]
            ],
            'bisnis_category' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Kategori bisnis is required'
                ]
            ],
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        $data           = $this->request->getJSON();

        $filters = array(
            'member_id'     => FILTER_SANITIZE_STRING,
            'namaoutlet'     => FILTER_SANITIZE_STRING,
            'alamat'     => FILTER_SANITIZE_STRING,
            'kota'     => FILTER_SANITIZE_STRING,
            'telp'     => FILTER_SANITIZE_STRING,
            'bisnis_category'     => FILTER_SANITIZE_STRING,
        );

        $filtered = array();
        foreach ($data as $key => $value) {
            $filtered[$key] = filter_var($value, $filters[$key]);
        }

        $data = (object) $filtered;

        $mdata = array(
            "member_id"       => $data->member_id,
            "namaoutlet"       => $data->namaoutlet,
            "alamat"       => $data->alamat,
            "kota"       => $data->kota,
            "telp"       => $data->telp,
            "bisnis_category"       => $data->bisnis_category,
            "is_deleted"       => 'no',
            "created_at"       => date("Y-m-d H:i:s"),
        );

        $result = $this->outlet->add_outlet($mdata);
        if (@$result->code == 1060) {
            return $this->respond(@$result);
        }

        $response = [
            "code"     => "200",
            "error"    => null,
            "messages"  => $result
        ];

        return $this->respond($response);
    }
}
