<?php
class MY_Controller extends CI_Controller 
{
    
	protected $current_student_id;
	protected $current_student_info;
	
    function __construct()
    {
        parent::__construct();
		$this->load->model('student_model');
		$this->load->helper('authentication_helper');
        
		if(!student_is_logged_in()) {
            $this->load->library('message');
            $this->message->set('You must be logged in to access that page.', 'error', TRUE);
			redirect('/');
        }
		
		$this->current_student_id = $this->session->userdata('student_id');
		$this->current_student_info = $this->student_model->get_student($this->current_student_id);
		$student_skills = $this->student_model->get_student_skills($this->current_student_id);
        $this->current_student_info->skills = '';
        
        foreach($student_skills as $skill) {
        	$this->current_student_info->skills = $this->current_student_info->skills . $skill->skill . ', ';
        }
        
    }
    
    function set_notification($data, $current_student_id)
    {
        $data["notifications"] = $this->student_model->get_notifications($current_student_id);
        return $data;
    }
    
    function set_current_page($page, $data = array())
    {
        $data["current_page"] = $page;
        return $data;
    }
	
}
?>