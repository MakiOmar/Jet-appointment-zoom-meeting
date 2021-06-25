<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if (!class_exists('ANONY_Zoom_Token_Jwt')){
	
	class ANONY_Zoom_Token_Jwt{
		
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
			
			$this->meta_key = 'zatoken_jwt_' . $doctors_id.'_'.$order_id.'_'.$customer_id ;
		}
	}
}