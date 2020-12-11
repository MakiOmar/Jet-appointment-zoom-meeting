<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if (!class_exists('ANONY_Zoom_Token')){
	
	class ANONY_Zoom_Token{
		
		public function __construct(){
			
			if(!is_user_logged_in()) return;
			
			$this->user_id = get_current_user_id();
			
			$this->token_transient = 'zoom-access-token_' . $this->user_id ;
		}
		
		
		public function isTransientSet(){
			
			$token = $this->getAccessToken();
			
			if (!$token || $token === '')  return false;
			
			return true;
		}
		
		public function updateAccessToken($token) {
			
        	if(!$this->isTransientSet()) {
        		
	            set_transient( $this->token_transient , $token );
	        }
	    }
	    
	    public function getAccessToken() {	
        		
			get_transient( $this->token_transient );
	        
	    }
	}
}