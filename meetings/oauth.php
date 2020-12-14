<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

function anony_outh_callback(){
	if(!isset($_GET['code']) || !isset($_GET['state']) || empty($_GET['code']) || empty($_GET['state'])  ) return esc_html__('Some data are missing');
	
	extract($_GET);
	
	$appointment_data = explode('-', $state);
	
	$current_user_id = get_current_user_id();
	
	if(get_transient('zoom_temp_'.$current_user_id.'_'.$appointment_data[1]) == $current_user_id || current_user_can('administrator')){
	    
	    if(intval($current_user_id) === intval($appointment_data[2]) || current_user_can('administrator')){
	        
    	    print_r($appointment_data);
    	    
        	$request = 
        		[
        	        "headers" => [
        	            "Authorization" => "Basic ". base64_encode(CLIENT_ID.':'.CLIENT_SECRET)
        	        ],
        	        'form_params' => [
        	            "grant_type" => "authorization_code",
        	            "code" => @$_GET['code'],
        	            "redirect_uri" => REDIRECT_URI
        	        ],
        	    ];
        	
        	try {
        	    $client = new GuzzleHttp\Client(['base_uri' => 'https://zoom.us']);
        	 
        	    $response = $client->request('POST', '/oauth/token', $request);
        	 
        	    $token = json_decode($response->getBody()->getContents(), true);
        	 
        	    $zoom_token = new ANONY_Zoom_Token();
        	 	
        	 	$zoom_token->updateAccessToken($token);
        	    
        	} catch(Exception $e) {
        		
        	   if( 401 == $e->getCode() ) {
                    $refresh_token = $accessToken;
         
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
         
                    anony_create_meeting();
                } else {
                    return $e->getMessage();
                }
        	    
        	}	        
	    }
	}
	
	delete_transient('zoom_temp_'.$current_user_id.'_'.$appointment_data[1]);
	

}

add_shortcode( 'anony-zoom-meeting', 'anony_outh_callback' );