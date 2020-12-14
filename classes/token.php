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
        		
	           set_transient( $this->token_transient , json_encode($token) );
	    }
	    
	    public function getToken(){
	    	return json_decode( get_transient( $this->token_transient ) ) ;
	    }
	    
	    public function getAccessToken() {	
        	
        	$token = $this->getToken();
        	
        	return $token->access_token;
			
	        
	    }
	    
	    public function getRefreshToken(){
	    	
	    	$token = $this->getToken();
	    	
	    	return $token->refresh_token;
	    }
	}
}