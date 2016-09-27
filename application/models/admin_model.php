<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_model extends CI_Model {


	public function __construct()
	{
		parent::__construct();
	}

	public function username_exists($username)
	{
		$this->db->where('username', $username);
		$query = $this->db->get('student');
		if ($query->num_rows() > 0)
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function email_exists($email)
	{
		$this->db->where('email', $email);
		$this->db->get('student');
		if ($query->num_rows() > 0)
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function insert($table, $data=array())
	{
		$this->db->insert($table, $data);
		if ($this->db->affected_rows() > 0)
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function admin_login($username, $password)
	{
		$this->db->select('*', FALSE);
		$this->db->where('username', $username);
		$this->db->where('password', $password);
		$this->db->from('admin');
		$result=$this->db->get();
		if ($result->num_rows() > 0)
		{
			$admindetails = $result->row();
			$admindata['admindetails'] = array('admin_username'=>$admindetails->username,
											   'admin_id'=>$admindetails->admin_id,
											   'admin_name'=>$admindetails->name,
											   'admin_phone'=>$admindetails->phone);
			$this->session->set_userdata($admindata);
			return TRUE;
		}
		else return FALSE;
	}

	public function superadmin_login($username, $password)
	{
		$this->db->select('*', FALSE);
		$this->db->where('username', $username);
		$this->db->where('password', $password);
		$this->db->from('superadmin');
		$result=$this->db->get();
		if ($result->num_rows() > 0)
		{
			$admindetails = $result->row();
			$admindata['admindetails'] = array('admin_username'=>$admindetails->username,
											   'admin_id'=>$admindetails->superadmin_id,
											   'admin_name'=>$admindetails->name,
											   'admin_phone'=>$admindetails->phone);
			$this->session->set_userdata($admindata);
			return TRUE;
		}
		else return FALSE;
	}

	public function dbselect($tablename)
	{
		$this->db->select('*',FALSE);
		$this->db->from($tablename);
		$result = $this->db->get()->result_array();
		return $result;

	}

	public function editstudentprofile($studentdetails, $student_id)
	{
		$this->db->where('student_id', $student_id);
		if ($this->db->update('student', $studentdetails))
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function get_all_student()
	{
		$this->db->select('student.*, level.lvl_name as kelas', FALSE);
		$this->db->from('student');
		$this->db->join('level', 'level.lvl_id = student.lvl_id', 'inner');
		$all_student = $this->db->get()->result_array();
		return $all_student;
	}

	public function studentprofile($student_id)
	{
		$this->db->select('*', FALSE);
		$this->db->from('student');
		$this->db->where('student_id', $student_id);
		$studentdetails = $this->db->get()->row();
		return $studentdetails;
	}

	public function get_student_level($student_id)
	{
		$this->db->select('student.*, level.lvl_name', FALSE);
		$this->db->from('student');
		$this->db->join('level', 'level.lvl_id = student.lvl_id', 'inner');
		$this->db->where('student_id', $student_id);
		$studentdetails = $this->db->get()->row();
		return $studentdetails;
	}

	public function adminprofile($admin_id)
	{
		$this->db->select('*', FALSE);
		$this->db->from('admin');
		$this->db->where('admin_id', $admin_id);
		$admindetails = $this->db->get()->row();
		return $admindetails;
	}

	public function superadminprofile($admin_id)
	{
		$this->db->select('*', FALSE);
		$this->db->from('superadmin');
		$this->db->where('superadmin_id', $admin_id);
		$admindetails = $this->db->get()->row();
		return $admindetails;
	}

	public function get_level($lvl_id)
	{
		$this->db->select('*', FALSE);
		$this->db->from('level');
		$this->db->where('lvl_id', $lvl_id);
		$lvldetails = $this->db->get()->row();
		return $lvldetails;
	}

	public function deleterecord($tablename, $fieldname, $index_id)
	{
		$this->db->where($fieldname, $index_id);
		$this->db->delete($tablename);
		$error = $this->db->_error_message();
		if ($error){
    		$result = 'Terjadi Error! =>>> ['.$error.']';
    	}
    	else{
    		$result = 'Data telah dihapus!!';
		}
		return $result;
	}

	public function get_all_subject()
	{
		$this->db->select('subject_category.*, level.lvl_name as kelas', FALSE);
		$this->db->from('subject_category');
		$this->db->join('level', 'subject_category.lvl_id = level.lvl_id', 'inner');
		$this->db->group_by('subject_category.cat_name, level.lvl_id');
		$all_subject = $this->db->get()->result_array();
		return $all_subject;
	}

	public function get_subject($cat_id)
	{
		$this->db->select('*', FALSE);
		$this->db->from('subject_category');
		$this->db->where('cat_id', $cat_id);
		$subjectdetails = $this->db->get()->row();
		return $subjectdetails;
	}

	public function get_level_result()
	{
		// $raw_query = "SELECT level.lvl_id, level.lvl_name, COUNT(student.lvl_id) as jumlah_siswa
		// 			  FROM level
		// 			  left join student on level.lvl_id = student.lvl_id
		// 			  where student.status = '1'
		// 			  group by lvl_id";
		// $query = $this->db->query($raw_query);
		// $all_level = $query->result_array();
		// return $all_level;

		$this->db->select('level.lvl_id, level.lvl_name, COUNT(student.lvl_id) as jumlah_siswa', FALSE);
		$this->db->from('level');
		$this->db->join('student', 'level.lvl_id = student.lvl_id', 'left');
		$this->db->where('student.status', '1');
		$this->db->group_by('student.lvl_id');
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function get_student_result($lvl_id)
	{
		$this->db->select('student.student_id, student.name, count(lks_record.lks_id) as attempted,
						sum(case when lks_record.pass_status = "lulus" then 1 else 0 end) as passed', FALSE);
		$this->db->from('student');
		$this->db->join('lks_record', 'lks_record.student_id = student.student_id', 'left');
		$this->db->where('student.lvl_id', $lvl_id);
		$this->db->where('student.status', '1');
		$this->db->group_by('student.student_id');
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function get_lks_result($student_id)
	{
		$this->db->select('lks_record.*, lks.lks_name', FALSE);
		$this->db->from('lks_record');
		$this->db->join('lks', 'lks.lks_id = lks_record.lks_id', 'left');
		$this->db->where('student_id', $student_id);
		$this->db->group_by('lks_record.lks_id, lks_record.start_time, lks_record.lr_id');
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function get_question_result($lr_id)
	{
		$this->db->select('question_record.qr_id, question_record.lr_id, LEFT(question.question, 50) as question, question.correct_answer, question_record.student_answer', FALSE);
		$this->db->from('question_record');
		$this->db->join('question', 'question.q_id = question_record.q_id', 'inner');
		$this->db->where('question_record.lr_id', $lr_id);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function lks_record($lr_id)
	{
		$this->db->select('lks_record.lr_id, lks_record.lks_id, lks_record.end_time, lks.lks_name, lks_record.student_id', FALSE);
		$this->db->from('lks_record');
		$this->db->join('lks', 'lks.lks_id = lks_record.lks_id', 'inner');
		$this->db->where('lr_id', $lr_id);
		$result = $this->db->get()->row();
		return $result;
	}

	public function get_lvl_by_cat_name($cat_name)
	{
		$this->db->select('lvl_name as kelas', FALSE);
		$this->db->from('level');
		$this->db->join('subject_category', 'subject_category.lvl_id = level.lvl_id', 'inner');
		$this->db->where('cat_name', $cat_name);
	}

	public function get_list_subject_level($cat_name)
	{
		$query = $this->db->get('subject_category');
		$select = '';
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$select.='<li>'.$row[$name].'</li>';
			}
		}
		return $select;
	}

	public function createsubject($subjectdetails)
	{
		if ($this->db->insert('subject_category', $subjectdetails))
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function createmapel($lvl_id, $cat_name)
	{
		if ($this->db->insert('subject_category', $lvl_id, $cat_name))
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function editsubject($subjectdetails, $cat_id)
	{
		$this->db->where('cat_id', $cat_id);
		if ($this->db->update('subject_category', $subjectdetails))
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function get_all_lks()
	{
		$this->db->select('lks.*, subject_category.cat_name as mata_pelajaran, level.lvl_name as kelas', FALSE);
		$this->db->from('lks');
		$this->db->join('subject_category', 'subject_category.cat_id = lks.cat_id', 'inner');
		$this->db->join('level', 'level.lvl_id = lks.lvl_id', 'inner');
		$this->db->group_by('lks.lvl_id, lks.cat_id, lks.lks_id');
		$all_lks = $this->db->get()->result_array();
		return $all_lks;
	}

	public function get_select_option($table, $id, $name, $selected=0)
	{
		$query = $this->db->get($table);
		$select = '<option value="" class=""> - - Pilih - - </option>';
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$selected_option = ($selected == $row[$id]) ? 'selected = "selected" ':' ';
				$select.='<option value="'.$row[$id].'" '.$selected_option.'>'.$row[$name].'</option>';
			}
		}
		return $select;
	}

	public function get_chained_option($table, $id, $name, $parentid, $selected=0)
	{
		$query = $this->db->get($table);
		$select = '<option value=""> - - Pilih - - </option>';
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$selected_option = ($selected == $row[$id]) ? 'selected = "selected" ':' ';
				$select.='<option value="'.$row[$id].'" '.$selected_option.' class="'.$row[$parentid].'">'.$row[$name].'</option>';
			}
		}
		return $select;
	}

	public function get_checkbox_option($table, $id, $name, $selected=0)
	{
		$query = $this->db->get($table);
		$select= ' ';
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$selected_option = ($selected == $row[$id]) ? 'selected = "selected" ':' ';
				$select .='<input type="checkbox" name="kelas[]" value="'.$row[$id].'" '.$selected_option.'>'.$row[$name].'<br>';
			}
		}
		return $select;
	}

	public function get_select_subject($cat_id, $cat_name, $selected=0)
	{
		$query = $this->db->get('subject_category');
		$select = '<option value=""> - - Pilih -  </option>';
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$selected_option = ($selected == $row[$cat_id]) ? 'selected = "selected" ':' ';
				$select.='<option value="'.$row[$cat_id].'" '.$selected_option.'>'.$row[$cat_name].'</option>';
			}
		}
		return $select;
	}

	public function add_lks($lksdetails)
	{
		if ($this->db->insert('lks', $lksdetails))
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function createquestion($questiondetails)
	{
		if ($this->db->insert('question', $questiondetails))
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function getquestion($q_id)
	{
		$this->db->select('*', FALSE);
		$this->db->from('question');
		$this->db->where('q_id', $q_id);
		$result = $this->db->get();
		return $result->row();
	}

	public function editlks($lksdata, $lks_id)
	{
		$this->db->where('lks_id', $lks_id);
		if ($this->db->update('lks', $lksdata))
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function editquestion($questiondetails, $q_id)
	{
		$this->db->where('q_id', $q_id);
		if ($this->db->update('question', $questiondetails))
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function get_lks($lks_id)
	{
		$this->db->select('*', FALSE);
		$this->db->from('lks');
		$this->db->where('lks_id', $lks_id);
		$lksdetails = $this->db->get()->row();
		return $lksdetails;
	}
	//LEFT(question, 150) as
	public function get_lks_question($lks_id)
	{
		$this->db->select('q_id, lks_id, question, option_a, option_b, option_c, option_d, correct_answer', FALSE);
		$this->db->from('question');
		$this->db->where('lks_id', $lks_id);
		$result = $this->db->get();
		return $result;
	}

	public function get_lks_name($lks_id)
	{
		$this->db->select('lks_name', FALSE);
		$this->db->from('lks');
		$this->db->where('lks_id', $lks_id);
		$result = $this->db->get()->row();
		return $result;
	}

	public function adminusername_exists($username)
	{
		$this->db->where('username', $username);
		$query = $this->db->get('admin');
		if ($query->num_rows() > 0)
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function adminemail_exists($email)
	{
		$this->db->where('email', $email);
		$query = $this->db->get('admin');
		if ($query->num_rows() > 0)
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function editadminprofile($admindetails, $admin_id)
	{
		$this->db->where('admin_id', $admin_id);
		if ($this->db->update('admin', $admindetails))
		{
			return TRUE;
		}
		else return FALSE;
	}

	public function editmyprofile($details, $admin_id, $currentpassword)
	{
		$this->db->select('*', FALSE);
		$this->db->from('admin');
		$this->db->where('admin_id', $admin_id);
		$this->db->where('password', $currentpassword); //sha1($password)
		$userdata = $this->db->get();

		if ($userdata->num_rows() > 0)
		{
			$this->db->where('admin_id', $admin_id);
			$this->db->update('admin', $details);
			return TRUE;
		}
		else return FALSE;
	}

	public function editsuperprofile($details, $admin_id, $currentpassword)
	{
		$this->db->select('*', FALSE);
		$this->db->from('superadmin');
		$this->db->where('superadmin_id', $admin_id);
		$this->db->where('password', $currentpassword); //sha1($password)
		$userdata = $this->db->get();

		if ($userdata->num_rows() > 0)
		{
			$this->db->where('superadmin_id', $admin_id);
			$this->db->update('superadmin', $details);
			return TRUE;
		}
		else return FALSE;
	}

	public function get_attempted_lks()
	{
		$this->db->select('lks.*, COUNT(student_lks.lks_id) AS attempted_students', FALSE);
		$this->db->from('lks');
		$this->db->join('student_lks', 'student_lks.lks_id = lks.lks_id', 'left');
		$this->db->group_by('lks.lks_id'); //THIS IS GREAT, WHY AM I SO CONFUSED????
		$lks = $this->db->get();
		return $lks;
	}

	public function get_exam_results($lks_id)
	{
		$this->db->select('lks.lks_name, lks.passmark', FALSE);
		$this->db->from('lks');
		$this->db->where('lks_id', $lks_id);
		$exam = $this->db->get();
		$result = array();

		if ($exam->num_rows() > 0)
		{
			$examdata = $exam->row();
			$results['lks_name'] = $examdata->lks_name;
			$results['exampassmark'] = $examdata->passmark;
			$this->db->select('student_lks.*, SUM(question.marks) AS maxmarks, student.name, student.email', FALSE);
			$this->db->from('student_lks');
			$this->db->join('question', 'question.lks_id = student_lks.lks_id', 'inner');
			$this->db->join('student', 'student.student_id = student_lks.student_id', 'inner');
			$this->db->where('student_lks.lks_id', $lks_id);
			$this->db->group_by('student_lks.student_id');
			$exam_records = $this->db->get();
			$user_results = array();

			foreach ($exam_records->result_array() as $key => $exam_result)
			{
				$student_id = $exam_result['student_id'];
				$maxmarks = $exam_result['maxmarks'];
				$results[''] = $exam_result['maxmarks'];
				$this->db->select('student_questions.q_id, student_questions.student_answer, question.correctanswer, question.marks', FALSE);
				$this->db->from('student_questions');
				$this->db->join('question', 'question.q_id = student_questions.q_id', 'inner');
				$this->db->where('student_questions.lks_id', $lks_id);
				$this->db->where('student_questions.student_id', $student_id);
				$allquestions = $this->db->get()->result_array();
				$marksobtained = 0;
				$failedquestions = array();

				foreach ($allquestions as $questiondata)
				{
					if ($questiondata['student_answer'] == $questiondata['correctanswer'])
					{
						$marksobtained += $questiondata['marks'];
					}
				}

				$user_results[$key]['student_names']  = $exam_result['name'];
				$user_results[$key]['student_emails'] = $exam_result['email'];
				$user_results[$key]['marksobtained']  = $marksobtained;
				$user_results[$key]['percentage'] 	  = floor(($marksobtained / $maxmarks) * 100);

				if ($user_results[$key]['percentage'] >= $results['exampassmark'])
				{
					$user_results[$key]['passed'] = TRUE;
				}
				else $user_results[$key]['passed'] = FALSE;
			}
			$results['user_results'] = $user_results;
			return $results;
		}
	}

	public function cekKuesioner($admin_id)
	{
		$this->db->select('k_status', FALSE);
		$this->db->from('admin');
		$this->db->where('admin_id', $admin_id);
		$status = $this->db->get();
		if ($status == '1'){
			return TRUE;
		}
		else return FALSE;
	}
}

/* End of file admin_model.php */
/* Location: ./application/models/admin_model.php */