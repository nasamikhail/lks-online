<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin_model');
	}

	public function index()
	{
		$data = array();
		$this->load->view('admin/login', $data);
	}

	public function login()
	{
		$data = array();

		if ($this->input->post('loginbttn'))
		{
			$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
			$this->form_validation->set_error_delimiters('<span class="fielderror">', '</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$username = $this->input->post('username');
				$password = md5($this->input->post('password'));

				if ($this->admin_model->admin_login($username, $password))
				{
					redirect(base_url().'administrator');
				}
				elseif ($this->admin_model->superadmin_login($username, $password)) {
					redirect(base_url().'superadmin');
				}
				else
				{
					$data['error'] = 'Kombinasi username/password salah! Silahkan coba lagi!';
				}
			}
		}
		$data['active'] = 'login';
		$this->load->view('admin/login', $data);
	}

	public function logout()
	{
		$this->session->unset_userdata('admindetails');
		$this->index();
	}

}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */