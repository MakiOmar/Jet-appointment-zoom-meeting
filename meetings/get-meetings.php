<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

function anony_get_meetings(){
	$client = new GuzzleHttp\Client(['base_uri' => 'https://api.zoom.us']);
 
   	$zoom_token = new ANONY_Zoom_Token($doctors_id, $order_id, $customer_id);
     
    $accessToken = $zoom_token->getAccessToken();
    
 
    try {
        $response = $client->request('GET', '/v2/users/me/meetings', [
            "headers" => [
                "Authorization" => "Bearer $accessToken"
            ]
        ]);
 
        $data = json_encode(json_decode($response->getBody()),true);
       
 
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
            return $e->getMessage();
        }
    }
}
//add_shortcode( 'anony-get-meetings', 'anony_get_meetings' );