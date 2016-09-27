<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Superadmin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		is_adminlogged_in();
		$this->load->model('admin_model');
		$this->load->model('student_model');
	}

	public function index()
	{
		$data['page'] = 'dashboard';
		$this->load->view('superadmin/main', $data);
	}

	public function student()
	{
		$data = array();
		$data['all_student'] = $this->admin_model->get_all_student();
		$data['page'] = 'managestudent';
		$this->load->view('superadmin/main', $data);
	}

	public function add_student()
	{
		$data = array();

		if ($this->input->post('registerbttn'))
		{
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('email', 'Email', 'trim|valid_email|required|callback_email_exists|xss_clean');
			$this->form_validation->set_rules('kelas', 'Kelas', '|required|is_natural');
			//$this->form_validation->set_rules('phone', 'Telephone', 'trim|xss_clean|min_length[6]|max_length[20]');
			$this->form_validation->set_rules('username', 'Username', 'trim|min_length[6]|max_length[20]|required|xss_clean|callback_username_exists');
			$this->form_validation->set_rules('password', 'Password', 'trim|min_length[6]|max_length[20]|xss_clean|required');
			$this->form_validation->set_rules('confirmpassword', 'Confirm Password', 'trim|xss_clean|matches[password]|required');
			$this->form_validation->set_error_delimiters("<span class='fielderror'>", "</span>");

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$studentdetails = array('name' 		=> $this->input->post('name'),
										'email'		=> $this->input->post('email'),
										'lvl_id'	=> $this->input->post('kelas'),
										//'phone'		=> $this->input->post('phone'),
										'username'	=> $this->input->post('username'),
										'password'	=> $this->input->post('password'));//sha1();
				if ($this->student_model->insert('student', $studentdetails))
				{
					$data['success'] = 'Akun sukses dibuat.';
				}
				else
				{
					$data['error'] = 'Terjadi error saat membuat akun, silahkan coba lagi.';
				}
			}
		}
		$data['kelas'] = $this->admin_model->get_select_option('level', 'lvl_id', 'lvl_name');
		$data['page'] = 'createstudent';
		$this->load->view('superadmin/main', $data);
	}

	public function edit_student($student_id)
	{
		$data = array();

		if ($this->input->post('updateprofilebttn'))
		{
			$this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|xss_clean');
			$this->form_validation->set_rules('kelas', 'Kelas', 'is_natural|required');
			$this->form_validation->set_rules('status', 'Status', 'is_natural|required');
			$this->form_validation->set_error_delimiters('<span class="fielderror">', '</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$studentdetails = array('name' => $this->input->post('name'),
										'email'=> $this->input->post('email'),
										'lvl_id'=> $this->input->post('kelas'),
										'status'=> $this->input->post('status'));
				$index_id = $this->input->post('student_id');

				if ($this->admin_model->editstudentprofile($studentdetails, $index_id))
				{
					$data['success'] = 'Detil akun telah diperbarui';
				}
				else
				{
					$data['error'] = 'Terjadi error saat memperbarui akun, pastikan anda memasukan password yang benar!';
				}
			}
		}

		if ($this->input->post('changepasswdbttn'))
		{
			$this->form_validation->set_rules('newpassword', 'New Password', 'trim|required|min_length[6]|max_length[20]|xss_clean');
			$this->form_validation->set_rules('confirmnewpassword', 'Confirm New Password', 'trim|required|xss_clean|matches[newpassword]');
			$this->form_validation->set_error_delimiters('<span class="fielderror">','</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$studentdetails = array('password' => $this->input->post('newpassword')); ///sha1
				$index_id = $this->input->post('student_id');

				if ($this->admin_model->editstudentprofile($studentdetails, $index_id))
				{
					$data['success'] = 'Password behasil diubah.';
				}
				else
				{
					$data['error'] = 'Terjadi error saat memperbarui password. Pastikan anda telah memasukkan password yang benar!';
				}
			}
		}
		$data['studentdetails'] = $this->admin_model->studentprofile($student_id);
		$data['kelas'] = $this->admin_model->get_select_option('level', 'lvl_id', 'lvl_name', $data['studentdetails']->lvl_id);
		$data['page'] = 'editstudent';
		$this->load->view('superadmin/main', $data);
	}

	public function editsubject($cat_id)
	{
		$data = array();

		if ($this->input->post('editsubjectbttn'))
		{
			$this->form_validation->set_rules('cat_name', 'Category Name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('lvl_id', 'Kelas', 'required|is_natural');
			$this->form_validation->set_error_delimiters('<span class="fielderror">','</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$categorydetails = array('cat_name'=>$this->input->post('cat_name'),
										 'lvl_id'=> $this->input->post('lvl_id'));
				$index_id = $this->input->post('cat_id');

				if ($this->admin_model->editcategory($categorydetails, $index_id))
				{
					$data['success'] = 'Data mata pelajaran behasil diubah!';
				}
				else
				{
					$data['error'] = 'Terjadi error saat memperbarui data. Silahkan ulangi!';
				}
			}
		}
		$data['subjectdetails'] = $this->admin_model->get_subject($cat_id);
		$data['page'] = 'editsubject';
		$this->load->view('superadmin/main', $data);
	}

	public function deletestudent()
	{
		$student_id = $this->input->post('student_id');
		$this->admin_model->deleterecord('student', 'student_id', $student_id);
	}

	public function deleteadmin()
	{
		$admin_id = $this->input->post('admin_id');
		$this->admin_model->deleterecord('admin', 'admin_id', $admin_id);
	}

	public function subject()
	{
		$data = array();
		$data['all_subject'] = $this->admin_model->get_all_subject_name();
		$data['page'] = 'managesubject';
		$this->load->view('superadmin/main', $data);
	}

	public function subjectname()
	{
		$data = array();
		$subjectname = $this->admin_model->get_all_subject_name();
		$data['all_subject_name'] = $subjectname;
		$nameArray = array();

		foreach ($subjectname as $subject)
		{
			$cat_name = $subject['cat_name'];
		}
		$data['kelas'] = $this->admin_model->get_lvl_by_cat_name($cat_name);
		$data['page'] = 'managesubjectname';
		$this->load->view('superadmin/main', $data);
	}

	public function add_subject()
	{
		$data = array();

		if ($this->input->post('savecatbttn'))
		{
			$this->form_validation->set_rules('cat_name', 'Category Name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('kelas[]', 'Kelas', '|required|is_natural');
			$this->form_validation->set_error_delimiters('<span class="fielderror">','</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$insertData = array();
				if(!empty($this->input->post('kelas')))
				{
					foreach ($this->input->post('kelas') as $kelas)
					{
						$tempArray = array(
							'cat_name' => $this->input->post('cat_name'),
							'lvl_id'	=> $kelas
							);

						array_push($insertData, $tempArray);
					}
					$query = $this->db->insert_batch('subject_category', $insertData);
				}

				if ($query)
				{
					$data['success'] = 'Berhasil menambahkan mata pelajaran!';
				}
				else
				{
					$data['error'] = 'Terjadi error saat menambahkan mata pelajaran, silahkan coba lagi!';
				}
			}
		}
		$data['kelas'] = $this->admin_model->get_checkbox_option('level', 'lvl_id', 'lvl_name');
		$data['page'] = 'createsubject';
		$this->load->view('superadmin/main', $data);
	}

	public function all_lks()
	{
		$data = array();
		//$data['all_lks'] = $this->admin_model->dbselect('lks');
		$data['all_lks'] = $this->admin_model->get_all_lks();
		$data['page'] = 'managelks';
		$this->load->view('superadmin/main', $data);
	}

	public function editlks($lks_id)
	{
		//$data = array();
		if ($this->input->post('savelksbttn'))
		{
			$this->form_validation->set_rules('kelas', 'Kelas', 'required|is_natural');
			$this->form_validation->set_rules('subject', 'Mata Pelajaran', 'trim|required|xss_clean');
			$this->form_validation->set_rules('lks_name', 'Nama LKS', 'trim|required|xss_clean');
			$this->form_validation->set_rules('durasi', 'Durasi', 'trim|required|xss_clean');
			$this->form_validation->set_error_delimiters('<span class="fielderror">','</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$lksdata = array('lks_name' => $this->input->post('lks_name'),
								 'cat_id' 	=> $this->input->post('subject'),
								 'lvl_id' 	=> $this->input->post('kelas'),
								 'duration'	=> $this->input->post('durasi'));
				$index_id = $this->input->post('lks_id');

				if ($this->admin_model->editlks($lksdata, $index_id))
				{
					$data['success'] = 'LKS telah berhasil diperbarui!';
				}
				else
				{
					$data['error'] = 'Terjadi error saat memperbarui LKS! Silahkan coba lagi';
				}
			}
		}
		$data['lksdetails'] = $this->admin_model->get_lks($lks_id);
		$data['kelas'] = $this->admin_model->get_select_option('level', 'lvl_id', 'lvl_name', $data['lksdetails']->lvl_id);
		$data['subject'] = $this->admin_model->get_select_option('subject_category', 'cat_id', 'cat_name', $data['lksdetails']->cat_id);
		$data['page'] = 'editlks';
		$this->load->view('superadmin/main', $data);
	}

	public function add_lks()
	{
		$data = array();

		if ($this->input->post('savelksbttn'))
		{
			$this->form_validation->set_rules('kelas', 'Kelas', 'required|is_natural');
			$this->form_validation->set_rules('subject', 'Mata Pelajaran', 'trim|required|xss_clean');
			$this->form_validation->set_rules('lks_name', 'Nama LKS', 'trim|required|xss_clean');
			$this->form_validation->set_rules('durasi', 'Durasi', 'trim|required|xss_clean');
			$this->form_validation->set_error_delimiters("<span class='fielderror'>", "</span>");

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$lksdetails = array('lvl_id' 	=> $this->input->post('kelas'),
									'cat_id'	=> $this->input->post('subject'),
									'lks_name'	=> $this->input->post('lks_name'),
									'duration'	=> $this->input->post('durasi'));

				if ($this->admin_model->add_lks($lksdetails))
				{
					$data['success'] = 'LKS telah berhasil ditambahkan!';
				}
				else
				{
					$data['error'] = 'Tejadi error saat menambahkan LKS! Silahkan coba lagi';
				}
			}
		}
		$data['kelas'] = $this->admin_model->get_select_option('level', 'lvl_id', 'lvl_name');
		$data['subject'] = $this->admin_model->get_select_subject('cat_id', 'cat_name');
		$data['page'] = 'createlks';
		$this->load->view('superadmin/main', $data);
	}

	public function deletelks()
	{
		$lks_id = $this->input->post('lks_id');
		$this->admin_model->deleterecord('lks', 'lks_id', $lks_id);
	}

	public function deletequestion()
	{
		$q_id = $this->input->post('q_id');
		$this->admin_model->deleterecord('question', 'q_id', $q_id);
	}

	public function deletesubject()
	{
		$cat_id = $this->input->post('cat_id');
		$this->admin_model->deleterecord('subject_category', 'cat_id', $cat_id);
	}

	public function mngquestion($lks_id)
	{
		$data = array();
		$question = $this->admin_model->get_lks_question($lks_id);
		$data['question'] = $question;
		$data['totalrecords'] = $question->num_rows;
		$data['lksdetails'] = $this->admin_model->get_lks($lks_id);
		$data['page'] = 'managequestion';
		$this->load->view('superadmin/main', $data);
	}

	public function createquestion($lks_id)
	{
		$data = array();

		if ($this->input->post('savequestionbttn'))
		{
			$this->form_validation->set_rules('question', 'Question', 'trim|required|xss_clean');
			$this->form_validation->set_rules('optiona', 'Option A', 'trim|required|xss_clean');
			$this->form_validation->set_rules('optionb', 'Option B', 'trim|required|xss_clean');
			$this->form_validation->set_rules('optionc', 'Option C', 'trim|required|xss_clean');
			$this->form_validation->set_rules('optiond', 'Option D', 'trim|required|xss_clean');
			$this->form_validation->set_rules('image', 'Image', 'trim|xss_clean');
			$this->form_validation->set_rules('correctanswer', 'Correct Answer', 'trim|required|xss_clean');
			$this->form_validation->set_error_delimiters('<span class="fielderror">','</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$questiondetails = array('lks_id' 		  => $this->input->post('lks_id'),
										 'question'       => $this->input->post('question'),
									 	 'option_a'       => $this->input->post('optiona'),
										 'option_b'       => $this->input->post('optionb'),
										 'option_c'       => $this->input->post('optionc'),
										 'option_d'       => $this->input->post('optiond'),
										 'image'          => $this->input->post('image'),
										 'correct_answer' => $this->input->post('correctanswer'));

				if ($this->admin_model->createquestion($questiondetails))
				{
					$data['success'] = 'Pertanyaan telah berhasil ditambahkan!';
				}
				else
				{
					$data['error'] = 'Tejadi error saat menambahkan pertanyaan! Silahkan coba lagi.';
				}
			}
		}
		$data['qdetails'] = $this->input->post('question');
		$data['lks_id'] = $lks_id;
		$data['page'] = 'createquestion';
		$this->load->view('superadmin/main', $data);
	}

	public function editquestion($q_id = 0)
	{
		$data = array();
		if ($this->input->post('editquestionbttn'))
		{
			$this->form_validation->set_rules('question', 'Question', 'trim|required|xss_clean');
			$this->form_validation->set_rules('optiona', 'Option A', 'trim|required|xss_clean');
			$this->form_validation->set_rules('optionb', 'Option B', 'trim|required|xss_clean');
			$this->form_validation->set_rules('optionc', 'Option C', 'trim|required|xss_clean');
			$this->form_validation->set_rules('optiond', 'Option D', 'trim|required|xss_clean');
			$this->form_validation->set_rules('image', 'Image', 'trim|xss_clean');
			$this->form_validation->set_rules('correctanswer', 'Correct Answer', 'trim|required|xss_clean');
			$this->form_validation->set_error_delimiters('<span class="fielderror">','</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$questiondetails = array('question'		 => $this->input->post('question'),
										 'option_a' 	 => $this->input->post('optiona'),
										 'option_b' 	 => $this->input->post('optionb'),
										 'option_c' 	 => $this->input->post('optionc'),
										 'option_d' 	 => $this->input->post('optiond'),
										 'image' 		 => $this->input->post('image'),
										 'correct_answer' => $this->input->post('correctanswer'));

				if ($this->admin_model->editquestion($questiondetails, $q_id))
				{
					$data['success'] = 'Pertanyaan telah berhasil diperbarui!';
				}
				else
				{
					$data['error'] = 'Tejadi error saat memperbarui pertanyaan! Silahkan coba lagi.';
				}
			}
		}
		$data['question'] = $this->admin_model->getquestion($q_id);
		$data['page'] = 'editquestion';
		$this->load->view('superadmin/main', $data);
	}

	public function add_admin()
	{
		$data = array();

		if ($this->input->post('createadminbttn'))
		{
			$this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|callback_email_exists|xss_clean|valid_email');
			$this->form_validation->set_rules('phone', 'Phone', 'trim|xss_clean|required');
			$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean|min_length[6]|max_length[20]|callback_adminusername_exists');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|min_length[6]|max_length[20]');
			$this->form_validation->set_rules('confirmpassword', 'Confirm Password', 'trim|required|xss_clean|matches[password]');
			$this->form_validation->set_error_delimiters('<span class="fielderror">','</span>');
			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$admindetails = array('name'	 => $this->input->post('name'),
									  'email' 	 => $this->input->post('email'),
									  'phone' 	 => $this->input->post('phone'),
									  'username' => $this->input->post('username'),
									  'password' => $this->input->post('password'));

				if ($this->admin_model->insert('admin', $admindetails))
				{
					$data['success'] = 'Akun untuk admin telah berhasil dibuat!';
				}
				else
				{
					$data['error'] = 'Terjadi error saat membuat akun admin! Silahkan coba lagi.';
				}
			}
		}
		$data['page'] = 'createadmin';
		$this->load->view('superadmin/main', $data);
	}

	public function all_admin()
	{
		$data = array();
		$data['all_admin'] = $this->admin_model->dbselect('admin');
		$data['page'] = 'manageadmin';
		$this->load->view('superadmin/main', $data);
	}

	public function editadmin($admin_id)
	{
		if ($this->input->post('updateprofilebttn'))
		{
			$this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email|callback_adminemail_exists');
			$this->form_validation->set_rules('phone', 'Phone', 'trim|xss_clean');
			$this->form_validation->set_error_delimiters('<span class="fielderror">','</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$admindetails = array('name'	=> $this->input->post('name'),
									  'email'	=> $this->input->post('email'),
									  'phone'	=> $this->input->post('phone'));
				$index_id = $this->input->post('admin_id');

				if ($this->admin_model->editadminprofile($admindetails, $index_id))
				{
					$data['success'] = 'Akun untuk admin telah berhasil diperbarui!';
				}
				else
				{
					$data['error'] = 'Tejadi error saat membuat akun admin! Silahkan coba lagi.';
				}
			}
		}

		if ($this->input->post('changepasswrdbttn'))
		{
			$this->form_validation->set_rules('newpassword', 'New Password', 'trim|required|xss_clean|min_length[6]|max_length[20]');
			$this->form_validation->set_rules('confirmnewpassword', 'Confirm New Password', 'trim|required|xss_clean|matches[newpassword]');
			$this->form_validation->set_error_delimiters('<span class="fielderror">','</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$admindetails = array('password'=> $this->input->post('newpassword')); //XXXXXXXXXXXXxx sha1(
				$index_id = $this->input->post('admin_id');

				if ($this->admin_model->editadminprofile($admindetails, $index_id))
				{
					$data['success'] = 'Password telah berhasil diperbarui!';
				}
				else
				{
					$data['error'] = 'Tejadi error saat memperbarui password! Silahkan coba lagi.';
				}
			}
		}
		$data['admindetails'] = $this->admin_model->adminprofile($admin_id);
		$data['page'] = 'editadmin';
		$this->load->view('superadmin/main', $data);
	}

	public function myprofile()
	{
		$data = array();
		$session = get_session_details();

		if (isset($session->admindetails) && !empty($session->admindetails))
		{
			$loggedadmin = (object)$session->admindetails;
			$admin_id = $loggedadmin->admin_id;
		}
		else redirect(base_url().'admin/login');

		if ($this->input->post('updateprofilebttn'))
		{
			$this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
			$this->form_validation->set_rules('phone', 'Phone', 'trim|required|xss_clean');
			$this->form_validation->set_rules('currentpassword', 'Current Password', 'trim|xss_clean|required');
			$this->form_validation->set_error_delimiters('<span class ="fielderror">','</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$admindetails = array('name' => $this->input->post('name'),
									  'email'=> $this->input->post('email'),
									  'phone'=> $this->input->post('phone'));

				$index_id = $this->input->post('admin_id');
				$currentpassword = $this->input->post('currentpassword');

				if ($this->admin_model->editsuperprofile($admindetails, $index_id, $currentpassword))
				{
					$data['success'] = 'Detil akun telah berhasil diperbarui!';
				}
				else
				{
					$data['error'] = 'Terjadi error saat memperbarui akun! Pastikan memang anda yang memiliki akun ini dan memasukkan password yang benar.';
				}
			}
		}
		$data['admindetails'] = $this->admin_model->superadminprofile($admin_id);
		$data['page'] = 'myprofile';
		$this->load->view('superadmin/main', $data);
	}

	public function changepassword()
	{
		$data = array();
		$session = get_session_details();

		if (isset($session->admindetails) && !empty($session->admindetails))
		{
			$loggedadmin = (object)$session->admindetails;
			$admin_id = $loggedadmin->admin_id;
		}
		else redirect(base_url().'admin/login');

		if ($this->input->post('changepasswordbttn'))
		{
			$this->form_validation->set_rules('currentpassword', 'Current Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('newpassword', 'New Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('confirmnewpassword', 'Confirm Password', 'trim|required|matches[newpassword]|xss_clean');
			$this->form_validation->set_error_delimiters('<span class="fielderror">','</span>');

			if ($this->form_validation->run() == FALSE)
			{
				$data['reset'] = FALSE;
			}
			else
			{
				$passworddetails = array('password' => $this->input->post('newpassword')); //sha1();
				$index_id = $this->input->post('admin_id');
				$currentpassword = $this->input->post('currentpassword');

				if ($this->admin_model->editsuperprofile($passworddetails, $index_id, $currentpassword))
				{
					$data['success'] = 'Password telah berhasil diperbarui!';
				}
				else
				{
					$data['error'] = 'Terjadi error saat memperbarui password. Pastikan memang anda yang memiliki akun ini dan memasukkan password yang benar.';
				}
			}
		}
		$data['admindetails'] = $this->admin_model->superadminprofile($admin_id);
		$data['page'] = 'changepassword';
		$this->load->view('superadmin/main', $data);
	}

	public function result()
	{
		$data = array();
		$data['page'] = 'resultlevel';
		$data['all_level'] = $this->admin_model->get_level_result();
		$this->load->view('superadmin/main', $data);
	}

	public function resultstudent($lvl_id)
	{
		$data = array();
		$data['page'] = 'resultstudent';
		$data['all_student'] = $this->admin_model->get_student_result($lvl_id);
		$data['lvldetails'] = $this->admin_model->get_level($lvl_id);
		$this->load->view('superadmin/main', $data);
	}

	public function resultlks($student_id)
	{
		$data = array();
		$data['page'] = 'resultlks';
		$data['all_lks'] = $this->admin_model->get_lks_result($student_id);
		$data['studentdetails'] = $this->admin_model->get_student_level($student_id);
		$this->load->view('superadmin/main', $data);
	}

	public function resultquestion($lr_id)
	{
		$data = array();
		$data['page'] = 'resultquestion';
		$data['questionresult'] = $this->admin_model->get_question_result($lr_id);
		$data['lksrecorddetails'] = $this->admin_model->lks_record($lr_id);
		$this->load->view('superadmin/main', $data);
	}

	public function is_allowed()
	{
		return TRUE;
	}

	//////////////////////// THIS IS VALIDATION SECTION /////////////////////////////////////////
	public function email_exists($email)
	{
		if ($this->student_model->email_exists($email))
		{
			$this->form_validation->set_message('email_exists', 'Email sudah pernah digunakan!');
			return FALSE;
		}
		else return TRUE;
	}

	public function username_exists($username)
	{
		if ($this->student_model->username_exists($username))
		{
			$this->form_validation->set_message('username_exists','Username sudah pernah digunakan!');
			return FALSE;
		}
		else return TRUE;
	}

	public function adminemail_exists($email)
	{
		if ($this->admin_model->adminemail_exists($email))
		{
			$this->form_validation->set_message('adminemail_exists', 'Email sudah pernah digunakan! Silahkan gunakan email yang lain.');
			return FALSE;
		}
		else return TRUE;
	}

	public function adminusername_exists($username)
	{
		if ($this->admin_model->adminusername_exists($username))
		{
			$this->form_validation->set_message('adminusername_exists','Username sudah pernah digunakan! Silahkan gunakan username yang lain.');
			return FALSE;
		}
		return TRUE;
	}
	////////////////////END OF VALIDATION SECTION/////////////////////
}

/* End of file administrator.php */
/* Location: ./application/controllers/administrator.php */