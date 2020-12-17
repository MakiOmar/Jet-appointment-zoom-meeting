<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

add_action( 'wp_ajax_anozom_create_meeting', function(){
    
    extract($_POST);
    
    if(current_user_can('administrator') || get_current_user_id() === intval($doctors_id)){
        
        $checked_in = get_post_meta(intval($order_id), 'appointment-checkin', true);
        
        if($checked_in || $checked_in === true){
            
            $return = [
                'access' => 'deny',
                'msg' => esc_html__('You have already checked in', ANOZOM_TEXTDOM),
            ];
        }else{
            if(ZOOM_OAUTH_PER_USER){
                $create_meating = anony_get_zoom_link(intval($doctors_id), intval($order_id));
            }else{
                $create_meating = anony_get_general_oauth_zoom_link(intval($doctors_id), intval($order_id));
            }
            
            set_transient('zoom_temp_'.$doctors_id.'_'.$order_id, $doctors_id, 60);
            
            $return = [
                'post' => $_POST,
                'access' => 'allow',
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
    
} );