<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth extends RestController
{
	private $_KEY;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Auth_model', 'auth');
		$this->_KEY = 'aoijia98rhr2h98h';
		// "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImdpYnJhbiIsImVtYWlsIjoicmdpYnJhbjIzNEBnbWFpbC5jb20ifQ.ajgKagdbqeuo7dWrqmDL-sIjOJSuusUoWKpHqNLtyq0"
	}

	public function login_post()
	{
		$username = $this->post('username');
		$password = $this->post('password');

		$user = $this->auth->get_by_username($username);

		// mengecek user ada atau tidak
		if ($user != null) {
			// memverifikasi password
			if (password_verify($password, $user['password'])) {
				// jika password benar

				// membuat payload
				$payload = [
					'username' => $user['username'],
					'email' => $user['email']
				];

				// membuat token dengan jwt
				$token = JWT::encode($payload, $this->_KEY, 'HS256');

				// response
				$response = [
					'status' => true,
					'token' => $token
				];
				$this->response($response['token'], 200);
			} else {
				// jika password error
				$response = [
					'status' => false,
					'message' => 'Error Password Is Wrong'
				];
				$this->response($response, 400);
			}
		} else {
			// jika username tidak di temukan
			$response = [
				'status' => false,
				'message' => 'Error User Not Found'
			];
			$this->response($response, 404);
		}
	}

	public function register_post()
	{
		$data['name']     = $this->post('name');
		$data['username'] = $this->post('username');
		$data['email']    = $this->post('email');
		$data['password'] = password_hash($this->post('password'), PASSWORD_BCRYPT);

		if ($this->auth->register_user($data) > 0) {
			$this->response([
				'status' => true,
				'message' => "New User Has been created"
			], 201);
		} else {
			$this->response([
				'status' => false,
				'message' => 'You\'ve sent bad data!'
			], 400);
		}
	}

	public function validate_post()
	{
		$headers = $this->input->request_headers();

		try {
			// Men-decode token. Dalam library ini juga sudah sekaligus memverfikasinya
			$decodedToken = JWT::decode($headers['Authorization'] ?? $headers['authorization'], new Key($this->_KEY, 'HS256'));
			$decoded_array = (array) $decodedToken;

			// Data yang akan dikirim jika token valid
			$this->response($decoded_array, 200);
		} catch (Exception $e) {
			// Bagian ini akan jalan jika terdapat error saat JWT diverifikasi atau di-decode
			$this->response($e->getMessage(), 401);

			exit();
		}
	}
}
