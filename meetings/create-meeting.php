<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

function anony_create_meeting($doctors_id, $order_id, $customer_id){
    $html = '';

    if(isset($_GET['doctor-id']) && isset($_GET['order-id']) && !empty($_GET['doctor-id']) && !empty($_GET['order-id'])  ){
        
        
        $order = wc_get_order(intval($_GET['order-id']));
    
        //$user_id = $order->get_user_id(); // or $order->get_customer_id();
         
        $customer_id = $order->get_customer_id();
        
        $zoom_token = new ANONY_Zoom_Token(intval($_GET['doctor-id']), intval($_GET['order-id']), $customer_id);
        
        extract($zoom_token->getTokenData());
        
        $html .= "<a href='".$join_url."'>".esc_html__('Join Now', ANOZOM_TEXTDOM)."</a>";
        $html .= "<p>".sprintf(esc_html__('Meeting password: %s', ANOZOM_TEXTDOM), $join_pass)."</p>";
        
        return $html;
    }
	
	if(!isset($_GET['code']) || !isset($_GET['state']) || empty($_GET['code']) || empty($_GET['state'])  ) return;
	
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
	$checked = get_post_meta(intval($order_id), 'appointment-checkin', true);
 
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
    
    
    
    
    try {
        $response = $client->request('POST', '/v2/users/me/meetings', $request );
        update_post_meta(intval($order_id), 'appointment-checkin', 'yes');
        
        $data = json_decode($response->getBody());
        
        $zoom_token->addJoinUrl($data->join_url, $data->password);
        
        $html .= "<a href='".$data->join_url."'>".esc_html__('Join Now', ANOZOM_TEXTDOM)."</a>";
        $html .= "<p>".sprintf(esc_html__('Meeting password: %s', ANOZOM_TEXTDOM), $data->password)."</p>";
        
        $doctor_email = get_post_meta(intval($doctors_id), 'g-mail', true);
        $patient_email = $order->get_billing_email();
        $protocols = array('http://', 'http://www.','https://', 'https://www.', 'www.');
        $noreply = str_replace($protocols, '', get_bloginfo('url'));
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: '.get_bloginfo().' <noreplay@'.$noreply.'>',
            'Bcc: '.$doctor_email,
            );
         
        wp_mail($patient_email , esc_html__('You have an appointment', ANOZOM_TEXTDOM), $html, $headers );
 
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