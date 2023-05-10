<?php

namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Auth extends BaseController
{
	use ResponseTrait;

	public function __construct()
	{
		$this->member  = model('App\Models\V1\Mdl_member');
	}

	public function register()
	{
		$appid   = getAppId(apache_request_headers()["Authorization"]);
		$validation = $this->validation;
		$validation->setRules([
			'nama' => [
				'rules'  => 'required',
				'errors' => [
					'required'      => 'Nama is required'
				]
			],
			'email' => [
				'rules'  => 'required|valid_email',
				'errors' => [
					'required'      => 'Email is required',
					'valid_email'   => 'Invalid Email format'
				]
			],
			'password' => [
				'rules'  => 'required|min_length[8]',
				'errors' =>  [
					'required'      => 'Password is required',
					'min_length'    => 'Min length password is 8 character'
				]
			]
		]);

		if (!$validation->withRequest($this->request)->run()) {
			return $this->fail($validation->getErrors());
		}

		$data           = $this->request->getJSON();

		$filters = array(
			'nama'     => FILTER_SANITIZE_STRING,
			'email'     => FILTER_VALIDATE_EMAIL,
			'password'  => FILTER_UNSAFE_RAW,
		);

		$filtered = array();
		foreach ($data as $key => $value) {
			$filtered[$key] = filter_var($value, $filters[$key]);
		}

		$data = (object) $filtered;

		$mdata = array(
			"appid"   	=> $appid,
			"nama"   	=> $data->nama,
			"email"     => $data->email,
			"passwd"    => sha1($data->password)
		);

		$result = $this->member->add($mdata);
		if (@$result->code == 1060) {
			return $this->respond(@$result);
		}

		$response = [
			"code"     => "200",
			"error"    => null,
			"messages"  => [
				"token"   => $result->token
			]
		];

		return $this->respond($response);
	}


	public function activate()
	{
		$token = $this->request->getGet('token', FILTER_SANITIZE_STRING);
		$member = $this->member->getby_token($token);

		// Token salah
		if (@$member->code == 5051) {
			return $this->respond(@$member);
		}

		// Akun sudah aktif
		if (@$member->status == 'active') {
			$response = [
				"code"       => "5051",
				"error"      => "05",
				"messages"    => "Member already active"
			];
			return $this->respond($response);

			// Member tersuspend atau tidak akitf
		} else if (@$member->status == 'disabled') {
			$response = [
				"code"      => "5051",
				"error"     => "06",
				"messages"   => "Your account is suspended. Please contact administrator"
			];
			return $this->respond($response);
		}

		$result = $this->member->activate($member->id);
		if (@$result->code == 5051) {
			return $this->respond(@$result);
		}

		$response = [
			"code"      => "200",
			"error"      => null,
			"messages"    => "Member is successfully activated"
		];
		return $this->respond($response);
	}

	public function signin()
	{
		$appid   = getAppId(apache_request_headers()["Authorization"]);
		$validation = $this->validation;
		$validation->setRules([
			'email' => [
				'rules'  => 'required|valid_email',
				'errors' => [
					'required'      => 'Email is required',
					'valid_email'   => 'Invalid Email format'
				]
			],
			'password' => [
				'rules'  => 'required|min_length[8]',
				'errors' =>  [
					'required'      => 'Password is required',
					'min_length'    => 'Min length password is 8 character'
				]
			],
		]);

		if (!$validation->withRequest($this->request)->run()) {
			return $this->fail($validation->getErrors());
		}

		$data           = $this->request->getJSON();

		$user = $this->member->getby_email($data->email, $appid);
		if (@$user->code == 5051) {
			return $this->respond(@$user);
		}

		// Jika ada User
		if ($user != NULL) {
			// Cek Password
			if (sha1($data->password) == $user->passwd) {
				if ($user->status == 'new') {
					$response = [
						"code"      => "5051",
						"error"     => "22",
						"messages"   => "Please activate your account"
					];
				} elseif ($user->status == 'disabled') {
					$response = [
						"code"      => "5051",
						"error"     => "06",
						"messages"   => "Your account is suspended. Please contact administrator"
					];
				} elseif ($user->status == 'active') {
					$user_type = $this->member->getUserType($user->id);
					$session_data = array(
						'id'        => $user->id,
						'appid'        => $user->appid,
						'email'        => $user->email,
						'passwd'        => $user->passwd,
						'nama'        => $user->nama,
						'status'        => $user->status,
						'created_at'        => $user->created_at,
					);

					$response = [
						"code"      => "200",
						"error"      => null,
						"messages"    => $session_data
					];
				} else {
					$response = [
						"code"      => "5051",
						"error"     => "04",
						"messages"   => "Invalid username or password"
					];
				}
				return $this->respond($response);
			} else {
				$response = [
					"code"      => "5051",
					"error"     => "04",
					"messages"   => "Invalid username or password"
				];
				return $this->respond($response);
			}
		} else {
			$response = [
				"code"      => "5051",
				"error"     => "04",
				"messages"   => "Invalid username or password"
			];
			return $this->respond($response);
		}
	}

	public function resetpassword()
	{
		$email = $this->request->getGet('email', FILTER_SANITIZE_STRING);
		$token = $this->member->resetToken($email);

		if (@$token->code == 5051) {
			return $this->respond(@$token);
		}

		$response = [
			"code"     => "200",
			"error"    => null,
			"messages"  => [
				"token"   => $token
			]
		];
		return $this->respond($response);
	}

	public function recoverytoken()
	{
		$token = $this->request->getGet('token', FILTER_SANITIZE_STRING);
		$member = $this->member->getby_token($token);

		// Token salah
		if (@$member->code == 5051) {
			return $this->respond(@$member);
		}

		// Member tersuspend atau tidak akitf
		if (@$member->status == 'disabled') {
			$response = [
				"code"      => "5051",
				"error"     => "06",
				"messages"   => "Your account is suspended. Please contact administrator"
			];
			return $this->respond($response);
		}

		$response = [
			"code"       => "200",
			"error"      => null,
			"messages"    => $member
		];
		return $this->respond($response);
	}

	public function updatepassword()
	{
		$validation = $this->validation;
		$validation->setRules([
			'token' => [
				'rules'  => 'required',
				'errors' => [
					'required'      => 'Reset token is required',
				]
			],
			'password' => [
				'rules'  => 'required|min_length[8]',
				'errors' => [
					'required'      => 'Password is required',
					'min_length'    => 'Min length password is 8 characters'
				]
			]
		]);

		if (!$validation->withRequest($this->request)->run()) {
			return $this->fail($validation->getErrors());
		}

		$data       = $this->request->getJSON();
		$filters = array(
			'token'     => FILTER_SANITIZE_STRING,
			'password'  => FILTER_UNSAFE_RAW,
		);

		$filtered = array();
		foreach ($data as $key => $value) {
			$filtered[$key] = filter_var($value, $filters[$key]);
		}

		$data = (object) $filtered;

		$member     = $this->member->getby_token($data->token);
		if (@$member->code == 5051) {
			return $this->respond($member);
		}

		$where = array(
			"email"     => $member->email,
			"token"     => $data->token
		);

		$mdata = array(
			"passwd"    => $data->password,
			"token"     => NULL
		);

		$result = $this->member->change_password($mdata, $where);
		if (@$result->code == 5051) {
			return $this->respond(@$result);
		}
		$response = [
			"code"      => "200",
			"error"      => null,
			"messages"    => "Password successfully changed"
		];
		return $this->respond($response);
	}

	public function addoutlet()
	{
		$appid   = getAppId(apache_request_headers()["Authorization"]);
		$validation = $this->validation;
		$validation->setRules([
			'id' => [
				'rules'  => 'required',
				'errors' => [
					'required'      => 'Id is required'
				]
			],
			'nama' => [
				'rules'  => 'required',
				'errors' => [
					'required'      => 'Nama is required'
				]
			],
			'bisnis_category' => [
				'rules'  => 'required',
				'errors' => [
					'required'      => 'Categori Bisnis is required'
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
					'required'      => 'No. Telephon is required'
				]
			]
		]);

		if (!$validation->withRequest($this->request)->run()) {
			return $this->fail($validation->getErrors());
		}

		$data           = $this->request->getJSON();

		$filters = array(
			'id'     => FILTER_SANITIZE_NUMBER_INT,
			'nama'     => FILTER_SANITIZE_STRING,
			'bisnis_category' => FILTER_SANITIZE_STRING,
			'alamat' => FILTER_SANITIZE_STRING,
			'kota' => FILTER_SANITIZE_STRING,
			'telp' => FILTER_SANITIZE_NUMBER_INT,
		);

		$filtered = array();
		foreach ($data as $key => $value) {
			$filtered[$key] = filter_var($value, $filters[$key]);
		}

		$data = (object) $filtered;

		$mdata = array(
			"member_id" => $data->id,
			"namaoutlet" => $data->nama,
			"bisnis_category" => $data->bisnis_category,
			"alamat"    => $data->alamat,
			"kota"    => $data->kota,
			"telp"    => $data->telp,
			"created_at"    => date("Y-m-d H:i:s"),
			"update_at"    => date("Y-m-d H:i:s"),
		);

		$result = $this->member->createOutlet($mdata);
		if (@$result->code == 5055) {
			return $this->respond(@$result);
		}

		$response = [
			"code"     => "200",
			"error"    => null,
			"messages"  => "Outlet successfully created"
		];

		return $this->respond($response);
	}
}
