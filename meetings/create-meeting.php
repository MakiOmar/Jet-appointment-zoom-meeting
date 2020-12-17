<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

function anony_create_meeting($doctors_id, $order_id, $customer_id){
	
	
	
	extract($_GET);
	
	$appointment_data = explode('-', $state);
	
	$doctors_id = $appointment_data[0];
	$order_id   = $appointment_data[1];
	$customer_id   = $appointment_data[2];
	
	$order= wc_get_order( intval($order_id) );
	
	$order_status  = $order->get_status();
        
        
    if($order_status !== 'completed') {
        printf(esc_html__('Sorry but this appointment payment is %s', ANOZOM_TEXTDOM), $order_status);
        
        return;
    }
	$current_user_id = get_current_user_id();
	
	if(intval($current_user_id) !== intval($doctors_id) && !current_user_can('administrator')) return esc_html__('You have no permission to access here');
 
    $zoom_token = new ANONY_Zoom_Token($doctors_id, $order_id, $customer_id);
    
    $accessToken = $zoom_token->getAccessToken();
    
    $client = new GuzzleHttp\Client(['base_uri' => 'https://api.zoom.us']);
    
    $request = 
    	[
            "headers" => [
                "Authorization" => "Bearer $accessToken"
            ],
            'json' => [
                "topic" => "Doctor appointment",
                "type" => 2,
                "start_time" => anony_get_appointment_date($order),
                //"timezone" => wp_zoom_timezone_string,
                "duration" => "30", // 30 mins
                "password" => wp_generate_password('6', false),
            ],
        ];
    
    
    $html = '';
    
    try {
        $response = $client->request('POST', '/v2/users/me/meetings', $request );
        update_post_meta(intval($order_id), 'appointment-checkin', true);
        
        $data = json_decode($response->getBody());
        $html .= "Join URL: ". $data->join_url;
        $html .= "<br>";
        $html .= "Meeting Password: ". $data->password;
 
    } catch(Exception $e) {
        if( 401 == $e->getCode() ) {
            $refresh_token = $zoom_token->getRefreshToken();
 
            $client = new GuzzleHttp\Client(['base_uri' => 'https://zoom.us']);
            $response = $client->request('POST', '/oauth/token', [
                "headers" => [
                    "Authorization" => "Basic ". base64_encode(CLIENT_ID.':'.CLIENT_SECRET)
                ],
                'form_params' => [
                    "grant_type" => "refresh_token",
                    "refresh_token" => $refresh_token
                ],
            ]);
            
            $zoom_token->updateAccessToken($response->getBody());
 
            anony_create_meeting($doctors_id, $order_id, $customer_id);
        } else {
            $html .= $e->getMessage();
        }
    }
    
    return $html;
}

add_shortcode( 'anony-create-meeting', 'anony_create_meeting' );