<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if (!class_exists('ANONY_Zoom_Token')){
	
	class ANONY_Zoom_Token{
		
		public function __construct($doctors_id, $order_id, $customer_id){
			
			if(!is_user_logged_in()) return;
			
			$this->doctors_id  = $doctors_id;
			$this->order_id  = $order_id;
			$this->customer_id  = $customer_id;
			
			$this->token_data = [
			    
			    'doctors_id'   => $this->doctors_id,
			    'order_id'     => $this->order_id,
			    'customer_id'  => $this->customer_id,
			    
			];
			
			$this->meta_key = 'zatoken_' . $doctors_id.'_'.$order_id.'_'.$customer_id ;
		}
		
		
		public function isTransientSet(){
			
			$token = $this->getAccessToken();
			
			if (!$token || $token === '')  return false;
			
			return true;
		}
		
		public function updateAccessToken($token) {
		    
		    $this->token_data['token'] = json_encode($token);
		    
            update_post_meta($this->order_id, $this->meta_key, $this->token_data);
        		
	    }
	    
	    public function getTokenData(){
	        return get_post_meta($this->order_id, $this->meta_key, true);
	    }
	    
	    public function getToken(){
	        
	        $token_data = $this->getTokenData();
	        
	        return json_decode($token_data['token']);

	    }
	    
	    public function getAccessToken() {	
        	
        	return $this->getToken()->access_token;
			
	        
	    }
	    
	    public function getRefreshToken(){
	        
	    	return $this->getToken()->refresh_token;
	    	
	    }
	}
}