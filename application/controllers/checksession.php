<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Check_Session extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		check_session();
	}
	public function index()
	{
	}
}
/* End of file login.php */
/* Location: ./application/controllers/login.php */