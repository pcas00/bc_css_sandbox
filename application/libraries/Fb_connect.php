<?php
class Fb_connect{
	
	public $CI;
	public $config;
	
	public function __construct(){
		$this->CI = &get_instance();
		
		$app_id = get_cfg_var('aws.param5');
		$secret = get_cfg_var('aws.param6');
		
		$this->config = array(
  			'appId'  => $app_id,
  			'secret' => $secret,
		);
		$this->CI->load->library('facebook-php-sdk/src/facebook', $this->config, 'facebook');
	}

	public function get_user_id(){
		return $this->CI->facebook->getUser();
	}
	
	public function get_login_url(){
	  	return $this->CI->facebook->getLoginUrl(array("scope" => "email"));
	}
	
	public function get_user_info($user_id){
		if ($user_id):
  			try{
    			// Proceed knowing you have a logged in user who's authenticated.
			    $user_profile = $this->CI->facebook->api('/me');
				return $user_profile;
  			} catch (FacebookApiException $e) {
   			 	error_log($e);
    			$user_id = null;
				return False;
  			}
		endif;
	}
}



?>