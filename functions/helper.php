<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
use \Firebase\JWT\JWT;
use GuzzleHttp\Client;
function getJwtZoomAccessToken() {
    $key = CLIENT_SECRET;
    $payload = array(
        "iss" => CLIENT_ID,
        'exp' => time() + 3600,
    );
    return JWT::encode($payload, $key);    
}

function createJwtZoomMeeting($json) {
    $client = new Client([
        // Base URI is used with relative requests
        'base_uri' => 'https://api.zoom.us',
    ]);
    $token = getJwtZoomAccessToken();
    $response = $client->request('POST', '/v2/users/me/meetings', [
        "headers" => [
            "Authorization" => "Bearer " . $token
        ],
        'json' => $json,
    ]);
 /*
    $data = json_decode($response->getBody());
    $html = "Join URL: ". $data->join_url;
    $html .= "<br>";
    $html .= "Meeting Password: ". $data->password;
    
    return $html;
    */
    $body = json_decode($response->getBody());
    
    $result['token'] = $token;
    $result['join_url'] = $body->join_url;
    $result['password'] = $body->password;
    
    return $result;
}
add_shortcode('jwt_zoom_meeting','createJwtZoomMeeting');
function anony_get_meeting_crids($doctors_id, $order_id, $customer_id){
    $zoom_token = new ANONY_Zoom_Token(intval($doctors_id), intval($order_id), $customer_id);
    $token_data = $zoom_token->getTokenData();
        if(!is_array($token_data)){
            return ['join_url' => '', 'join_pass' => ''];
        }
        extract( $token_data );
        
        return ['join_url' => $join_url, 'join_pass' => $join_pass];
}
/**
 * Get zoom's oAuth token data
 * @param object $order 
 * @return array
 */
function anony_get_zoom_token_data($order){
    
    $customer_id = $order->get_customer_id();
    $doctors_id = anony_get_doctors_id($order);
    $order_id = $order->get_id();
    if(!ZOOM_JWT){
        $zoom_token = new ANONY_Zoom_Token(intval($doctors_id), $order_id, $customer_id);
        return $zoom_token->getTokenData();
    }
    $meta_key = 'zatoken_jwt_' . $doctors_id.'_'.$order_id.'_'.$customer_id ;
    
    $zoom_token = get_post_meta(intval($order_id), $meta_key, true);
    return $zoom_token;
     
}
/**
 * Insert a key/value before another in an associative array
 * @param array $originalArray 
 * @param strin $originalKey 
 * @param array $insertKey 
 * @param string $insertValue 
 * @return array
 */
function insert_before_key( $originalArray, $originalKey, $insertKey, $insertValue ) {

    $newArray = array();
    $inserted = false;

    foreach( $originalArray as $key => $value ) {

        if( !$inserted && $key === $originalKey ) {
            $newArray[ $insertKey ] = $insertValue;
            $inserted = true;
        }

        $newArray[ $key ] = $value;

    }

    return $newArray;

}

function get_current_doctor_profile_id(){
    
    
    
    if(current_user_can('administrator')) {
        
        $clinic_options = get_option('clinic-options');
    
        if(!$clinic_options || !isset($clinic_options['clinic-director-profile']) || empty($clinic_options['clinic-director-profile'])) return false;
        
        return intval($clinic_options['clinic-director-profile']);
    }
    
    $doctors_query = new WP_Query([
	        
        'post_type' => 'doctors',
        'author' => get_current_user_id(),
	            
   ]);
	   
   if($doctors_query->have_posts()){
       while($doctors_query->have_posts()){
           $doctors_query->the_post();
           $IDs[] = get_the_ID();
           
       }
       
       wp_reset_postdata();
   }
   
   if(count($IDs) > 1) return false;
   
   return $IDs[0];
}