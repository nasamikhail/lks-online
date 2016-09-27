<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main_login extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('student_model');
	}

	public function index()
	{
		$data = array();
		$data['active'] = 'login';
   		$this->load->view('index', $data);
	}

	public function login()
	{
		$data = array();

		if($this->input->post('loginbttn'))
		{
			$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$username = $this->input->post('username');
				$password = md5($this->input->post('password'));
				//$password = sha1($this->input->post('password'));

				if ($this->student_model->login($username, $password))
				{
					redirect(base_url().'student');
				}
				else
				{
					$data['error'] = 'Kesalahan kombinasi username/password! Coba kembali.';
				}
			}
		}
		$data['active'] = 'login';
		$this->load->view('index', $data);
	}

	public function logout()
	{
		$this->session->unset_userdata('studentdetails');//$this->studentdetails or $studentdetails
		$this->index();
	}

}

/* End of file login.php */
/* Location: ./application/controllers/login.php */