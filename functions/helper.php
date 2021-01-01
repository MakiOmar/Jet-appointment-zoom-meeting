<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * Get zoom's oAuth token data
 * @param object $order 
 * @return array
 */
function anony_get_zoom_token_data($order){
    
    $customer_id = $order->get_customer_id();
    $doctors_id = anony_get_doctors_id($order);
    $order_id = $order->get_id();
    
     $zoom_token = new ANONY_Zoom_Token(intval($doctors_id), $order_id, $customer_id);
     return $zoom_token->getTokenData();
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