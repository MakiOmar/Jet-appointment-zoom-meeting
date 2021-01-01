<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

add_action( 'wp_ajax_anozom_appointment_checkout', function(){
    
    extract($_POST);
    
    $current_doctor_id = get_current_doctor_profile_id();
    
    $updated_state =false;
    
    if(current_user_can('administrator') ||intval($current_doctor_id) === intval($doctors_id)){
        
        $updated = update_post_meta(intval($order_id), 'appointment-checkin', 'no');
        
        if($updated) $updated_state = true;
    }
    
    $return = [
                'updated' => $updated_state,
            ];
	wp_send_json($return);

	die();
    
} );