<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * 1. Register new endpoint slug to use for My Account page
 * @important-note	Resave Permalinks or it will give 404 error
 */
add_action( 'init', function() {
    add_rewrite_endpoint( 'appiontments', EP_ROOT | EP_PAGES );
} );

/**
 * 2. Add new query var
 */
add_filter( 'woocommerce_get_query_vars',function ( $vars ) {
    $vars[] = 'appiontments';
    return $vars;
} , 0 );

/**
 * 3. Insert the new endpoint into the My Account menu
 */
 
add_filter( 'woocommerce_account_menu_items', function( $items ) {
    $new_items = insert_before_key( $items, 'orders','appiontments', 'appiontments' );
    return $new_items;
} );

/**
 * 4. Add content to the new endpoint
 * @important-note	"add_action" must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format
 */
add_action( 'woocommerce_account_appiontments_endpoint', function () {
    $user_id = get_current_user_id();
    $user_meta=get_userdata($user_id);

    $user_roles = $user_meta->roles; //array of roles the user is part of.
    
    $legible_roles = array_intersect(['anony_doctor', 'administrator'], (array) $user_roles);
	
	if( !empty($legible_roles) ){
	    	$doctor_id = get_current_doctor_profile_id();

    	if(!$doctor_id) {
    	    if(current_user_can('administrator')){
    	        esc_html_e('You didn\'t set the profile ID for clinic\'s director' , ANOZOM_TEXTDOM);
    	    }else{
    	        esc_html_e('Sorry! but this user has more than one profile. Should be one', ANOZOM_TEXTDOM);
    	    }
    	    
    	    return;
    	}
       anony_provider_appointments($doctor_id);
       
	}elseif(in_array( 'anony_patient', $user_roles, true )){
	    anony_provider_appointments($user_id, 'user_id');
	}

} );

