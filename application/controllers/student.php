<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Student extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		is_userlogged_in();
		$this->load->model('student_model');
	}

	public function index()
	{
		$data         = array();
		$data['page'] = 'dashboard';
		$this->load->view('student/content', $data);
	}

	public function subject_category()
	{
		$data = array();
		$session = get_session_details();

		if (isset($session->studentdetails) && !empty($session->studentdetails))
		{
			$loggeduser  = (object)$session->studentdetails;
			$student_id  = $loggeduser->student_id;
			$lvl_id      = $loggeduser->student_lvl;
			$subject_lvl = $this->student_model->subject_category($lvl_id);
			$level = $this->student_model->studentprofile($student_id);
			//var_dump($categories_lks);
		}
		// var_dump($subject_lvl);
		$data['level'] = $level;
		$data['subject_category'] = $subject_lvl;
		$data['page']             = 'subject_category';
		$this->load->view('student/content', $data);
	}

	public function all_lks($cat_id)
	{
		$data = array();
		$session = get_session_details();

		if (isset($session->studentdetails) && !empty($session->studentdetails))
		{
			$loggeduser = (object)$session->studentdetails;
			$student_id = $loggeduser->student_id;
			$lvl_id     = $loggeduser->student_lvl;
			$lkslist    = $this->student_model->all_lks($lvl_id, $cat_id);
			$subject    = $this->student_model->get_subject($cat_id);
		}
		$data['subjectdetails'] = $subject;
		$data['all_lks']        = $lkslist;
		$data['page']           = 'all_lks';
		$this->load->view('student/content', $data);
	}

	public function take_lks($lks_id=0)
	{
		$data               = array();
		$data['lksdetails'] = $this->student_model->lksdetails($lks_id);
		$data['page']       = 'startlks';
		$this->load->view('student/content', $data);
	}

	public function lks($lks_id=0)
	{
		$data             = array();
		$lksdata          = $this->student_model->lksdata($lks_id);
		$data['page']     = 'lks';
		$data['duration'] = $lksdata->duration * 60;
		$data['lks_id']   = $lks_id;
		$this->load->view('student/content', $data);
	}

	public function get_student_lks_data()
	{
		$lks_id  = $this->input->post('lksId');
		$lksdata = $this->student_model->get_lks_question($lks_id);//ambil soal + pilgan
		$session = get_session_details();

		if (isset($session->studentdetails) && !empty($session->studentdetails))
		{
			$loggeduser = (object)$session->studentdetails;
			$last_id = $this->student_model->recordlks_start($lks_id, $loggeduser->student_id);
		}
		echo json_encode(
			array(
				'lksdata' 		=> $lksdata,
				'lr_id' 		=> (!empty($last_id)?$last_id:0)
			)
		);
	}

	public function save_answer()
	{
		$lr_id         = $this->input->post('id');
		$q_id           = $this->input->post('q_id');
		$student_answer = $this->input->post('a');

		if ($this->student_model->save_answer($lr_id, $q_id, $student_answer))
		{
			$response   = 'success';
		}
		else $response = 'error';
		echo $response;
	}

	public function finish_lks()
	{
		$lr_id = $this->input->post('lr_id');
		$lks_id  = $this->input->post('lks_id');
		$session = get_session_details();

		if (isset($session->studentdetails) && !empty($session->studentdetails))
		{
			$loggeduser = (object)$session->studentdetails;
			$student_id = $loggeduser->student_id;
			$this->student_model->recordlks_finish($lr_id, $lks_id, $student_id);
			$response   = 'success';
		}
		else $response = 'error';
		echo $response; //$response is echoed and not saved in any variable
	}

	public function submit_lks($lks_id, $lr_id)
	{
		// submit_lks/$lks_id/$lr_id
		// $_GET['indeksLrId']
		$data = array();
		$session = get_session_details();

		if (isset($session->studentdetails) && !empty($session->studentdetails))
		{
			$loggeduser = (object)$session->studentdetails;
			$student_id = $loggeduser->student_id;
			$result = $this->student_model->lks_result($lr_id, $lks_id, $student_id);
		}
		else redirect(base_url());

		$data['result'] = $result;
		$data['page'] = 'lks_result';
		$this->load->view('student/content', $data);
	}

	public function history()
	{
		$data = array();
		$session = get_session_details();

		if (isset($session->studentdetails) && !empty($session->studentdetails))
		{
			$loggeduser = (object)$session->studentdetails;
			$student_id = $loggeduser->student_id;
			$result = $this->student_model->history($student_id);
		}
		else redirect(base_url());

		$data['result'] = $result;
		$data['page']    = 'history';
		$this->load->view('student/content', $data);
	}

	public function profile()
	{
		$data = array();
		$session = get_session_details();

		if (isset($session->studentdetails) && !empty($session->studentdetails))
		{
			$loggeduser = (object)$session->studentdetails;
			$student_id = $loggeduser->student_id;
		}
		else redirect(base_url());

		if ($this->input->post('updateprofilebttn'))
		{
			$this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
			$this->form_validation->set_rules('kelas', 'Kelas', 'required|is_natural');
			$this->form_validation->set_rules('newpassword', 'New password', 'trim|xss_clean|min_length[6]|max_length[20]');
			$this->form_validation->set_rules('confirmnewpassword', 'Confirm new password', 'trim|xss_clean|matches[newpassword]');
			$this->form_validation->set_rules('currentpassword', 'Password', 'trim|xss_clean');
			$this->form_validation->set_error_delimiters('<span class="fielderror">', '</span>');

			if ($this->form_validation->run() ==  FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$studentdetails = array('name'  => $this->input->post('name'),
									    'email' => $this->input->post('email'),
									    'lvl_id'=> $this->input->post('kelas'));
				$newpassword = md5($this->input->post('newpassword'));

				if (isset($newpassword) && $newpassword != '')
				{
					$studentdetails['password'] = $this->input->post('newpassword'); //sha1
				}

				$index_id = $this->input->post('student_id');

				$curpassword = md5($this->input->post('currentpassword'));

				if ($this->student_model->updateprofile($studentdetails, $index_id, $curpassword))
				{
					$data['success'] = 'Akun telah berhasil diperbarui!';
				}
				else
				{
					$data['error'] = 'Terjadi error saat memperbarui akun. Silahkan coba lagi!';
				}
			}
		}
		$data['studentdetails'] = $this->student_model->studentprofile($student_id);
		$data['kelas'] = $this->student_model->get_select_option('level', 'lvl_id', 'lvl_name');
		$data['page'] = 'profile';
		$this->load->view('student/content', $data);
	}

	public function email_exists($email)
	{
		if ($this->student_model->email_exists($email))
		{
			$this->form_validation->set_rules('email_exists', 'Email sudah pernah digunakan, pilih  email yang lain!');
			return FALSE;
		}
		else return TRUE;
	}

	public function questionnaire($student_id)
	{
		$data = array();
		if ($this->input->post('savequestionnaire'))
		{
			$this->form_validation->set_rules('student_id', 'student_id', 'trim|xss_clean');
			$this->form_validation->set_rules('q1', 'No.1', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q2', 'No.2', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q3', 'No.3', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q4', 'No.4', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q5', 'No.5', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q6', 'No.6', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q7', 'No.7', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q8', 'No.8', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q9', 'No.9', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q10', 'No.10', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q11', 'No.11', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q12', 'No.12', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q13', 'No.13', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q14', 'No.14', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q15', 'No.15', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q16', 'No.16', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q17', 'No.17', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q18', 'No.18', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q19', 'No.19', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q20', 'No.20', 'trim|xss_clean|required');
			$this->form_validation->set_rules('q21', 'No.21', 'trim|xss_clean|required');
			$this->form_validation->set_error_delimiters("<span class='fielderror'>", "</span>");

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$qdetails = array('student_id'		=> $this->input->post('student_id'),
										'q1'		=> $this->input->post('q1'),
										'q2'		=> $this->input->post('q2'),
										'q3'		=> $this->input->post('q3'),
										'q4'		=> $this->input->post('q4'),
										'q5'		=> $this->input->post('q5'),
										'q6'		=> $this->input->post('q6'),
										'q7'		=> $this->input->post('q7'),
										'q8'		=> $this->input->post('q8'),
										'q9'		=> $this->input->post('q9'),
										'q10'		=> $this->input->post('q10'),
										'q11'		=> $this->input->post('q11'),
										'q12'		=> $this->input->post('q12'),
										'q13'		=> $this->input->post('q13'),
										'q14'		=> $this->input->post('q14'),
										'q15'		=> $this->input->post('q15'),
										'q16'		=> $this->input->post('q16'),
										'q17'		=> $this->input->post('q17'),
										'q18'		=> $this->input->post('q18'),
										'q19'		=> $this->input->post('q19'),
										'q20'		=> $this->input->post('q20'),
										'q21'		=> $this->input->post('q21'));
				if ($this->student_model->insert('kuesioner', $qdetails))
				{
					redirect(base_url().'main_login/logout');
				}
				else
				{
					$data['error'] = 'Belum lengkap';
					redirect(base_url().'student/questionnaire'.$student_id);
				}
			}
		}
		$data['student_id'] = $student_id;
		$this->load->view('questionnaire');
	}
}
/* End of file users.php */
/* Location: ./application/controllers/users.php */
