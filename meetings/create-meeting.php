<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

function anony_create_meeting(){
	
	$client = new GuzzleHttp\Client(['base_uri' => 'https://api.zoom.us']);
 
    $zoom_token = new ANONY_Zoom_Token();
    
    $accessToken = $zoom_token->getAccessToken();
    
    $request = 
    	[
            "headers" => [
                "Authorization" => "Bearer $accessToken"
            ],
            'json' => [
                "topic" => "Let's learn Laravel",
                "type" => 2,
                "start_time" => "2020-12-12T20:30:00",
                "duration" => "30", // 30 mins
                "password" => "123456"
            ],
        ];
    
    
    $html = '';
    
    try {
        $response = $client->request('POST', '/v2/users/me/meetings', $request );
 
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
 
            anony_create_meeting();
        } else {
            $html .= $e->getMessage();
        }
    }
    
    return $html;
}

add_shortcode( 'anony-create-meeting', 'anony_create_meeting' );