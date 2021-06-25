<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

use \Firebase\JWT\JWT;

function anony_create_meeting_aouth(){
    extract($_POST);
    
    $current_doctor_id = get_current_doctor_profile_id();
    
    
    if(current_user_can('administrator') || intval($current_doctor_id) === intval($doctors_id)){
        
        $order = wc_get_order($order_id);
        $checked_in = get_post_meta(intval($order_id), 'appointment-checkin', true);
       
        if($checked_in){
            
            if($checked_in !== 'yes') update_post_meta(intval($order_id), 'appointment-checkin', 'yes');
            
            ob_start();
                anony_checkin_markup($order);
            $checkout_html = ob_get_clean();
            
            //update_post_meta(intval($order_id), 'appointment-checkin', 'no');
            
            $return = [
                'access' => 'allow',
                'html' => $checkout_html,
                'msg' => esc_html__('You have already checked in', ANOZOM_TEXTDOM),
                'link' => add_query_arg( array(
                                'doctor-id' => $doctors_id,
                                'order-id' => $order_id,
                            ), REDIRECT_URI ),
            ];
        }else{
            if(ZOOM_OAUTH_PER_USER){
                $create_meating = anony_get_zoom_link(intval($doctors_id), intval($order_id));
            }else{
                $create_meating = anony_get_general_oauth_zoom_link(intval($doctors_id), intval($order_id));
            }
            
            
            
            $customer_id = $order->get_customer_id();
            
            set_transient('zoom_temp_'.$doctors_id.'_'.$order_id, $doctors_id, 360);
            
            $return = [
                'post' => $_POST,
                'access' => 'allow',
                //'html' => $checkout_html,
                'msg' => '',
                'link' => $create_meating,
            ];
        }
        
        
        
    }else{
        $return = [
            'access' => 'deny',
            'msg' => esc_html__('Your not allowed to do this', ANOZOM_TEXTDOM),
        ];
        
        
    }
    
    

	wp_send_json($return);

	die();
}

function anony_create_meeting_jwt(){
    extract($_POST);
    
    $current_doctor_id = get_current_doctor_profile_id();
    
    $order = wc_get_order($order_id);
    
    $user_id = $order->get_customer_id();
    
    $doctors_id = $doctors_id;
	$customer_id   = $user_id;
    
    $current_user_id = get_current_user_id();
    
    $meta_key = 'zatoken_jwt_' . $doctors_id.'_'.$order_id.'_'.$customer_id ;
    
    $checked_in = get_post_meta(intval($order_id), 'appointment-checkin', true);
    
    $html = '';
    
    if($checked_in && $checked_in !== ''){
         if($checked_in !== 'yes') update_post_meta(intval($order_id), 'appointment-checkin', 'yes');
         
         $mail = anony_meeting_notify($order_id, $doctors_id);
            
        ob_start();
            anony_checkin_markup($order);
        $checkout_html = ob_get_clean();
        $return = [
                'access' => 'allow',
                'html' => $checkout_html,
                'msg' => esc_html__('You have already checked in', ANOZOM_TEXTDOM),
                'x' => $mail
            ];
    }else{
            
            if(intval($current_doctor_id) === intval($doctors_id) || current_user_can('administrator')){
                
                $response = createJwtZoomMeeting([
                    "topic" => "Doctor appointment",
                    "type" => 2,
                    "start_time" => anony_get_appointment_date($order),
                    //"timezone" => wp_zoom_timezone_string,
                    "duration" => "30", // 30 mins
                    "password" => wp_generate_password('6', false),
                ]);
                
                update_post_meta(intval($order_id), $meta_key, $response);
                
                update_post_meta(intval($order_id), 'appointment-checkin', 'yes');
                
                anony_meeting_notify($order_id, $doctors_id);
                
                ob_start();
                    anony_checkin_markup($order);
                $checkout_html = ob_get_clean();
                $return = [
                    'access' => 'allow',
                    'html' => $checkout_html,
                    'x' => get_post_meta(intval($order_id), $meta_key, true),
                ];
                
                
            }
        
    }
    
   
    wp_send_json($return);

	die();
}
add_action( 'wp_ajax_anozom_create_meeting', 'anony_create_meeting_aouth' );
add_action( 'wp_ajax_anozom_create_meeting_jwt', 'anony_create_meeting_jwt' );

