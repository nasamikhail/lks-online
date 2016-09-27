<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function username_exists($username)
	{
		$this->db->where('username', $username);
		$query = $this->db->get('student');

		if ($query->num_rows() > 0) return TRUE;
		else return FALSE;
	}

	public function email_exists($email)
	{
		$this->db->where('email',$email);
		$query = $this->db->get('student');

		if ($query->num_rows() > 0) return TRUE;
		else return FALSE;
	}

	public function insert($table, $data = array())
	{
		$this->db->insert($table, $data);

		if ($this->db->affected_rows() > 0) return TRUE;
		else return FALSE;
	}

	public function login($username, $password)
	{
		$this->db->select('*');
		$this->db->where('username', $username);
		$this->db->where('password', $password);
		$this->db->where('status', "1"); //jika status siswa aktif
		$this->db->from('student');
		$result = $this->db->get();

		if ($result->num_rows() > 0)
		{
			$studentdetails = $result->row();
			$studentdata['studentdetails'] = array('student_username' => $studentdetails->username,
												   'student_id' 	  => $studentdetails->student_id,
												   'student_name'	  => $studentdetails->name,
												   'student_lvl'	  => $studentdetails->lvl_id);
			$this->session->set_userdata($studentdata); //set session pake data student
			return TRUE;
		}
		else return FALSE;
	}

	public function subject_category($lvl_id)
	{
		$raw_query = "SELECT subject_category.cat_id, subject_category.cat_name, COUNT(lks.lks_id) as jumlah_lks
					  FROM subject_category
					  LEFT JOIN lks ON subject_category.cat_id = lks.cat_id
					  WHERE subject_category.lvl_id = ".$lvl_id."
					  GROUP BY subject_category.cat_id";
		$query = $this->db->query($raw_query);
		return $query->result_array();
	}

	public function all_lks($lvl_id, $cat_id)
	{
		$this->db->select("lks.lks_id, lks.lks_name, lks_record.student_id, lks_record.lks_id as lks_r_id,
						   COUNT(lks_record.lks_id) AS attempted,
						   COUNT( CASE WHEN lks_record.pass_status = 'lulus' THEN 1 END) AS passed");
		$this->db->from('lks');
		$this->db->join('lks_record', 'lks.lks_id = lks_record.lks_id', 'left');
		$this->db->where('cat_id', $cat_id);
		$this->db->group_by('lks.lks_id');
		$all_lks = $this->db->get()->result_array();
		return $all_lks;
	}

	public function studentprofile($student_id) //nyari data student berdasarkan student_id, resultnya berupa array
	{
		$this->db->select('student.*, level.lvl_name');
		$this->db->from('student');
		$this->db->join('level', 'student.lvl_id = level.lvl_id', 'left');
		$this->db->where('student_id', $student_id);
		$studentdetails = $this->db->get()->row();
		return $studentdetails;
	}

	public function updateprofile($details, $student_id, $password)
	{
		$this->db->select('*');
		$this->db->from('student');
		$this->db->where('student_id', $student_id);
		$this->db->where('password', $password); //$password sha1
		$studentdata = $this->db->get();

		if ($studentdata->num_rows() > 0)
		{
			$this->db->where('student_id', $student_id);
			$this->db->update('student', $details);
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function lksdetails($lks_id) //result in array
	//lks join table question qount questin number
    {
        $this->db->select('lks.*, COUNT(question.q_id) AS totalquestion');
        $this->db->from('lks');
        $this->db->join('question', 'question.lks_id = lks.lks_id', 'left');
        $this->db->where('lks.lks_id', $lks_id);
        $lksdetails = $this->db->get()->result_array();
        return $lksdetails;
    }

    public function lksdata($lks_id) //result in object
    {
        $this->db->select('lks_id, lks_name, duration');
        $this->db->from('lks');
        $this->db->where('lks_id', $lks_id);
        $lksdata = $this->db->get()->row();
        return $lksdata;
    }

    public function get_subject($cat_id)
    {
    	$this->db->select('*');
        $this->db->from('subject_category');
        $this->db->where('cat_id', $cat_id);
        $subject = $this->db->get()->row();
        return $subject;
    }

    public function catdetails($cat_id)
    {
        $this->db->select('*');
        $this->db->from('subject_category');
        $this->db->where('cat_id', $cat_id);
        $result = $this->db->get();

        if ($result->num_rows() > 0)
		{
			$catdetails = $result->row();
			$catdata['catdetails'] = array('cat_name' => $catdetails->cat_name,
										   'cat_id'	  => $catdetails->cat_id,
										   'lvl_id'	  => $catdetails->lvl_id);
		}
		return $catdata;
    }

	public function update($tablename, $details, $fieldname, $fieldvalue)
	{
		$this->db->where($fieldname, $fieldvalue);
		$this->db->update($tablename, $details);
	}

	public function get_lks_question($lks_id) //loading lks question, options, and each answer //HACK IT TO DO LOAD 15-20 random question
	{
		$this->db->select('lks_id, lks_name, duration');
		$this->db->from('lks');
		$this->db->where('lks_id', $lks_id);
		$result = $this->db->get();

		$lks = array();
		$lksrow = $result->row();
		$lks['id'] = $lksrow->lks_id; //index id di array exam[] punya value sama kayak kolom lks_id di table lks //iki mlebu exam.js
		$lks['name'] = $lksrow->lks_name; //row dari object ke?? //iki mlebu exam.js

		$this->db->select('*');
		$this->db->from('question');
		$this->db->where('lks_id', $lks_id);
		$this->db->order_by('', 'random'); //give random question
		$this->db->limit(40);
		$result_question = $this->db->get();

		$lks['question'] = array(); //array di dalam array

		foreach ($result_question->result_array() as $x => $question)
		{
			$lks['question'][$x]['q_id']  = $question['q_id'];
			$lks['question'][$x]['text']  = $question['question'];

			$answer 	= array();
			$ansoption1 = array('id' => 'A', 'text' => $question['option_a']);
			$ansoption2 = array('id' => 'B', 'text' => $question['option_b']);
			$ansoption3 = array('id' => 'C', 'text' => $question['option_c']);
			$ansoption4 = array('id' => 'D', 'text' => $question['option_d']);

			array_push($answer, $ansoption1);
			array_push($answer, $ansoption2);
			array_push($answer, $ansoption3);
			array_push($answer, $ansoption4);
			$lks['question'][$x]['answer'] = $answer;
		}
		return $lks;
	}

	public function recordlks_start($lks_id, $student_id) //u dont need to delete old data nash!
	{
		$this->db->select('*');
		$this->db->from('lks_record');
		$lks_newstatus = array(
			'student_id' => $student_id,
			'lks_id' => $lks_id);
		$this->db->set('start_time', 'NOW()', FALSE);
		$this->db->insert('lks_record', $lks_newstatus);
		$last_id = $this->db->insert_id();
		return $last_id;
	}

	public function recordlks_finish($lr_id, $lks_id, $student_id)
	{
		$this->db->select('*');
		$this->db->from('lks_record');
		$lks_newstatus = array(
			'student_id' => $student_id,
			'lks_id' => $lks_id,);
		$this->db->where('lr_id', $lr_id);
		$this->db->set('end_time', 'NOW()', FALSE);
		$this->db->update('lks_record', $lks_newstatus);
	}

	public function save_answer($lr_id, $q_id, $student_answer)
	{
		$this->db->select('*');
		$this->db->from('question_record');
		$qr_details = array ('lr_id'			=> $lr_id,
							 'q_id'				=> $q_id,
							 'student_answer' 	=> $student_answer);
		$this->db->insert('question_record', $qr_details);
		$last_id = $this->db->insert_id();
		return $last_id;
	}

	function lks_result($lr_id, $lks_id, $student_id)
	{
		$this->db->select('lks_record.*, lks.lks_name');
	    $this->db->from('lks_record');
		$this->db->join('lks', 'lks.lks_id = lks_record.lks_id', 'inner');
	    $this->db->where('lks_record.student_id', $student_id);
        $this->db->where('lks_record.lks_id', $lks_id);
        /*SELECT lks_record.*, lks.lks_name
		from lks_record
		inner join lks on lks.lks_id = lks_record.lks_id*/
	    $lksrecord = $this->db->get();
        $results = array();
		if($lksrecord->num_rows() > 0)
        {
			$lksdata = $lksrecord->row();
			$results['duration'] = timeDiff($lksdata->start_time, $lksdata->end_time);
			$results['lks_name'] = $lksdata->lks_name;

			$this->db->select('question_record.q_id, question_record.qr_id, question.correct_answer, question_record.student_answer');
			$this->db->from('question_record');
			$this->db->join('question', 'question.q_id = question_record.q_id', 'inner');
			$this->db->where('question_record.lr_id', $lr_id);
			$questionrecord = $this->db->get();
			$question = array();
			$this->db->select('*');
	        $this->db->from('question');
	        $this->db->where('lks_id', $lks_id);
	        $totalquestion = $this->db->get()->num_rows();
			$pointobtained = 0;
			$failedquestion = array();
			foreach ($questionrecord -> result_array() as $questiondata)
			{
				if($questiondata['student_answer'] == $questiondata['correct_answer'])
				{
					$pointobtained += 1;
				}
				else
				{
					$this->db->select('*');
					$this->db->from('question');
					$this->db->where('q_id', $questiondata['q_id']);
					$failed = $this->db->get()->row();

					$correct_answer = 'option_'.strtolower($failed->correct_answer);
					$student_answer = 'option_'.strtolower($questiondata['student_answer']);
					$question = array('question'=>$failed->question,
										'correct_answer'=>$failed->$correct_answer,
										'student_answer' => $failed->$student_answer
								);
					array_push($failedquestion, $question);
				}
			}
			$results['failedquestion'] = $failedquestion;
			$results['pointobtained'] = $pointobtained;
			$results['totalquestion'] = $totalquestion;
			$results['grade'] = floor(($pointobtained / 40) * 100);
			if($results['grade'] > 65)
			{
				$results['passed'] = true;
				$updatedetails = array('pass_status' => 'lulus', 'grade' => $results['grade']);
				$this->db->where('lr_id', $lr_id);
				$this->db->update('lks_record', $updatedetails);
			}
			else
			{
				$results['passed'] = false;
				$updatedetails = array('pass_status' => 'gagal', 'grade' => $results['grade']);
				$this->db->where('lr_id', $lr_id);
				$this->db->update('lks_record', $updatedetails);
			}
		}
		return $results;
	}

	public function history($student_id)
	{
		$this->db->select("lks_record.lr_id, lks_record.lks_id, lks.lks_name, subject_category.cat_name,
							DATE_FORMAT(lks_record.start_time,'%d/%m/%Y') as start_date,
							concat(
							MOD(TIMESTAMPDIFF(hour, lks_record.start_time, lks_record.end_time), 24), ':',
							MOD(TIMESTAMPDIFF(minute, lks_record.start_time, lks_record.end_time), 60), ':',
							MOD(TIMESTAMPDIFF(second, lks_record.start_time, lks_record.end_time), 60)
							) AS duration, lks_record.grade, lks_record.pass_status", FALSE);
		$this->db->from('lks_record');
		$this->db->join('lks', 'lks.lks_id = lks_record.lks_id', 'inner');
		$this->db->join('subject_category', 'lks.cat_id = subject_category.cat_id', 'inner');
		$this->db->where('lks_record.student_id', $student_id);
		$this->db->order_by('lks_record.lr_id', 'desc');
		$query = $this->db->get()->result_array();
		return $query ;
	}

	public function get_select_option($table, $id, $name, $selected=0)
	{
		$query = $this->db->get($table);
		$select = '';
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
}
/* End of file student_model.php */
/* Location: ./application/models/student_model.php */
