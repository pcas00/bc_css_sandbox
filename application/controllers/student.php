<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//Extend MY_Controller for authentication/current student logged in built in
class Student extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->helper("image_helper");
	}	

	public function index()
	{
		$data["student_logged_in"] = $this->current_student_info;
		//Retrieve all students information to send to view
		$data["students"] = $this->student_model->get_all_students();
		
		$this->load->view('student/home', $data);
	}

	public function search()
	{
				
		$query = $this->input->post('query', TRUE);

		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('query', 'search terms', 'trim|required|htmlspecialchars|xss_clean');
		
		//If form does not validate according to rules above, load form view with error messages
		if ($this->form_validation->run() == FALSE):
			
			$data["student_logged_in"] = $this->current_student_info;
			$this->load->view('student/home', $data);
		
		//Else, display students according to search term
		else:
			
			if (strpos(",", $query) != FALSE)
				$query_exploded = explode(',', $query);
			else
				$query_exploded = explode(' ', $query);
			
			$search_string = '';
			$i = 0;
			foreach ($query_exploded AS $term):
				$search_string = $search_string . $term;
				if ($i != sizeof($query_exploded) - 1)
					$search_string = $search_string . ' | ';
				$i++;
			endforeach;
			
			$data["students"] = $this->student_model->search_students($search_string);
			$data["student_logged_in"] = $this->current_student_info;
			$data["search_query"] = $query;
			
			$this->load->view('student/view_students', $data);
			
		endif;
		
	}
	

	
	public function edit_form(){
		
		$data["student_logged_in"] = $this->current_student_info;

		//Create list of majors for view
		$data["majors"] = $this->student_model->get_majors();
		
		//Create list of schools view
		$data["schools"] = $this->student_model->get_schools();
		$data["upload_errors"] = '';
		
		$this->load->view('student/edit_student_form', $data);
	}
	
	public function edit(){
		
		$student_id = $this->current_student_id;		
		
		//Currently, email address cannot be changed
		
		$first 	  		  = $this->input->post('first',    		   TRUE);
		$last  	  		  = $this->input->post('last',     		   TRUE);
		//$email 	  		  = $this->input->post('email',    		   TRUE);
		$password 		  = $this->input->post('password', 		   TRUE);
		$confirm_password = $this->input->post('confirm_password', TRUE);
		$year 	  		  = $this->input->post('year', 	  		   TRUE);
		$school 	  	  = $this->input->post('school',   		   TRUE);
		$major 	  		  = $this->input->post('major',    		   TRUE);
		$status			  = $this->input->post('status', 		   TRUE);
		$bio 	  		  = $this->input->post('bio', 	  		   TRUE);
		$skills 	  	  = $this->input->post('skills', 	  	   TRUE);
		$software 	  	  = $this->input->post('software', 	  	   TRUE);
		$twitter 	  	  = $this->input->post('twitter', 	  	   TRUE);
		$facebook 	  	  = $this->input->post('facebook', 	  	   TRUE);
		$linkedin	  	  = $this->input->post('linkedin', 	  	   TRUE);
		$dribbble 	  	  = $this->input->post('dribbble', 	  	   TRUE);
		$github 	  	  = $this->input->post('github', 	  	   TRUE);
		
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('first', 'first name', 					'trim|required|htmlspecialchars|xss_clean');
		$this->form_validation->set_rules('last', 'last name', 						'trim|required|htmlspecialchars|xss_clean');
		//Add custom validation for e-mail addresses so that only Boston College students can register
		
		$this->form_validation->set_rules('email', 'e-mail address', 			'trim|required|htmlspecialchars|xss_clean|valid_email|is_unique[students.email]');
		
		if (!empty($password)):
			$this->form_validation->set_rules('password', 'password', 					'trim|required|htmlspecialchars|xss_clean|matches[confirm_password]');
			$this->form_validation->set_rules('confirm_password', 'confirmed password', 'trim|required|htmlspecialchars|xss_clean');
		endif;
		
		$this->form_validation->set_rules('year', 'year of graduation', 			'trim|htmlspecialchars|xss_clean|numeric|max_length[4]|valid_graduation_date');
		$this->form_validation->set_rules('school', 'school', 						'trim|htmlspecialchars|xss_clean|numeric');
		$this->form_validation->set_rules('major', 'major', 						'trim|htmlspecialchars|xss_clean|numeric');
		$this->form_validation->set_rules('status', 'status', 						'trim|htmlspecialchars|xss_clean');
		$this->form_validation->set_rules('bio', 'bio', 							'trim|htmlspecialchars|xss_clean');
		$this->form_validation->set_rules('skills', 'skills',						'trim|htmlspecialchars|xss_clean');
		$this->form_validation->set_rules('software', 'software',					'trim|htmlspecialchars|xss_clean');
		$this->form_validation->set_rules('twitter', 'twitter',						'trim|htmlspecialchars|xss_clean');
		$this->form_validation->set_rules('facebook', 'facebook',					'trim|htmlspecialchars|xss_clean|valid_url');
		$this->form_validation->set_rules('linkedin', 'linkedin',					'trim|htmlspecialchars|xss_clean|valid_url');
		$this->form_validation->set_rules('dribbble', 'dribbble',					'trim|htmlspecialchars|xss_clean|valid_url');
		$this->form_validation->set_rules('github', 'github',						'trim|htmlspecialchars|xss_clean|valid_url');

		//If form does not validate according to rules above, load form view with error messages
		if ($this->form_validation->run() == FALSE):	
			
			$data["student_logged_in"] = $this->current_student_info;
			
			//Create list of majors for view
			$data["majors"] = $this->student_model->get_majors();
			//Create list of schools view
			$data["schools"] = $this->student_model->get_schools();
			$data["upload_errors"] = '';
			$this->load->view('student/edit_student_form', $data);
		
		//Else, add student to database
		else:
			
			$student_data = array(
								 'first' 	=> $first,
								 'last'	 	=> $last,
								 'email' 	=> $email,
								 'year'		=> $year,
								 'major_id' => $major,
								 'school_id'=> $school,
								 'status'   => $status,
								 'bio' 		=> $bio,
								 'skills'	=> $skills,
								 'software'	=> $software,
								 'twitter'	=> $twitter,
								 'facebook'	=> $facebook,
								 'linkedin'	=> $linkedin,
								 'dribbble'	=> $dribbble,
								 'github'	=> $github
								 );
			
			if (!empty($password)):
				$student_data["password"] = sha1($password);
			endif;
			
			//Load message library for setting success/error messages
			$this->load->library('message');
			
			$rows_affected = $this->student_model->edit_student($student_id, $student_data);
			
			if ($rows_affected):
				$this->message->set("You have successfully edited your account profile", "success", TRUE);
				redirect("student/edit_form");
				
			else:
				$this->message->set("Your profile could not be edited. Please try again.", "error", TRUE);
				redirect("student/edit_form");
				
				//echo 'Your profile could not be edited. Please try again.';
			endif;
			
		endif;
		
	}

	public function upload_profile_pic(){
		
		//set the path to root
		$config['upload_path'] = './uploads/students/pictures';
		$config['allowed_types'] = 'gif|jpg|png';
		//append unix time stamp for unique update
		$config['file_name'] = "studentpic_" . $this->current_student_id . "_" . time();
		
		//2000kb and max image width and height
		$config['max_size']	= '2000';
		$config['max_width']  = '1024';
		$config['max_height']  = '768';
		
		//prevent users from uploading multiple images.
		$config['overwrite']  = TRUE;
		
		//load the upload lib with settings
		$this->load->library('upload', $config);
		if (!$this->upload->do_upload()):
			$data["student_logged_in"] = $this->current_student_info;
			$data["majors"] = $this->student_model->get_majors();
			$data["schools"] = $this->student_model->get_schools();
			$data["upload_errors"] =  $this->upload->display_errors();
			
			$this->load->view('student/edit_student_form', $data);
		else:
			$uploaded_data = $this->upload->data();
			$status = $this->student_model->update_profile_picture($this->current_student_id, $uploaded_data['file_name']);
			
			$this->load->library('message');
			if ($status):
				$this->message->set("Picture updated successfully", "success", TRUE);
				redirect("student/edit_form");
			else:
				$this->message->set("Picture update failed", "error", TRUE);
				redirect("student/edit_form");
			endif;
		endif;
		
	}

	public function remove_profile_pic(){
			$status = $this->student_model->delete_profile_picture($this->current_student_id);
			$this->load->library('message');
			if ($status):
				$this->message->set("Picture deleted successfully", "success", TRUE);
				redirect("student/edit_form");
			else:
				$this->message->set("Picture delete failed", "error", TRUE);
				redirect("student/edit_form");
			endif;
	}
	
	//View all students
	public function view_all()
	{
		//Retrieve all students information to send to view
		$data["students"] = $this->student_model->get_all_students();
		$this->load->view('student/view_all_students', $data);
	}

}

/* End of file student.php */
/* Location: ./application/controllers/student.php */