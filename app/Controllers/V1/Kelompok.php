<?php

namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Kelompok extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        $this->kelompok  = model('App\Models\V1\Mdl_kelompok');
    }

    public function get_data_kelompok()
    {
        $userid     = $this->request->getGet('member_id', FILTER_SANITIZE_STRING);
        $result     = $this->kelompok->get_kelompok($userid);

        $response = [
            "code"     => "200",
            "error"    => null,
            "messages"  =>  $result
        ];

        return $this->respond($response);
    }

    public function add_data_kelompok()
    {
        $validation = $this->validation;
        $validation->setRules([
            'member_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Member is required'
                ]
            ],
            'kelompok' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Kelompok is required'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        $data           = $this->request->getJSON();

        $filters = array(
            'member_id'     => FILTER_SANITIZE_STRING,
            'kelompok'     => FILTER_SANITIZE_STRING,
        );

        $filtered = array();
        foreach ($data as $key => $value) {
            $filtered[$key] = filter_var($value, $filters[$key]);
        }

        $data = (object) $filtered;

        $mdata = array(
            "member_id"     => $data->member_id,
            "kelompok"      => $data->kelompok,
            "created_at"    => date("Y-m-d H:i:s")
        );

        $result = $this->kelompok->add_kelompok($mdata);
        if (@$result->code == 5055) {
            return $this->respond($result);
        }

        $response = [
            "code"     => "200",
            "error"    => null,
            "messages"  => $mdata
        ];
        return $this->respond($response);
    }

    public function update_kelompok()
    {
        $validation = $this->validation;
        $validation->setRules([
            'kelompok_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Kelompok ID is required'
                ]
            ],
            'kelompok' => [
                'rules'  => 'required',
                'errors' => [
                    'required'      => 'Kategori is required'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        $data           = $this->request->getJSON();

        $filters = array(
            'kelompok_id'  => FILTER_SANITIZE_STRING,
            'kelompok'     => FILTER_SANITIZE_STRING,
        );

        $filtered = array();
        foreach ($data as $key => $value) {
            $filtered[$key] = filter_var($value, $filters[$key]);
        }

        $data = (object) $filtered;

        $mdata = array(
            "kelompok"      => $data->kelompok,
            "update_at"     => date("Y-m-d H:i:s")
        );

        $result = $this->kelompok->update_kelompok($mdata, $data->kelompok_id);
        if (@$result->code == 5055) {
            return $this->respond($result);
        }

        $response = [
            "code"     => "200",
            "error"    => null,
            "messages"  => $mdata
        ];
        return $this->respond($response);
    }

    public function delete_kelompok()
    {
        $kelompok_id = $this->request->getGet('kelompok_id', FILTER_SANITIZE_STRING);
        $mdata = array(
            "is_deleted"      => "yes",
            "update_at"     => date("Y-m-d H:i:s")
        );

        $result = $this->kelompok->delete_kelompok($mdata, $kelompok_id);
        if (@$result->code == 5055) {
            return $this->respond($result);
        }

        $response = [
            "code"     => "200",
            "error"    => null,
            "messages"  => $mdata
        ];

        return $this->respond($response);
    }
}
