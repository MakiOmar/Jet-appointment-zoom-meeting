<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

function anony_outh_callback(){
	if(!isset($_GET['code']) || !isset($_GET['state']) || empty($_GET['code']) || empty($_GET['state'])  ) return esc_html__('Some data are missing');
	
	extract($_GET);
	
	$appointment_IDs = $state;
	
	return $appointment_IDs;
	
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

add_shortcode( 'anony-zoom-meeting', 'anony_outh_callback' );