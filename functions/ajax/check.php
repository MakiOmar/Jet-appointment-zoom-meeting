<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

add_action( 'wp_ajax_anozom_appointment_checkout', function(){
    
    extract($_POST);
    
    if(current_user_can('administrator') || get_current_user_id() === intval($doctors_id)){
        
        $updated = update_post_meta(intval($order_id), 'appointment-checkin', 'no');
        
        if($updated){
            $return = [
                'updated' => true,
            ];
        }else{
            $return = [
                'updated' => false,
            ];
        }

    }
	wp_send_json($return);

	die();
    
} );