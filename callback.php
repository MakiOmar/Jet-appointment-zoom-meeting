<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.




function anony_zoom_link(){
	
	$url = "https://zoom.us/oauth/authorize?response_type=code&client_id=".CLIENT_ID."&redirect_uri=".esc_url( REDIRECT_URI );

 
	return '<a href="'. $url .'">Login with Zoom</a>';
}

add_shortcode( 'anony-zoom-link', 'anony_zoom_link' );

require_once ANOZOM_DIR . 'meetings/create-meeting.php';
require_once ANOZOM_DIR . 'meetings/get-meetings.php';
require_once ANOZOM_DIR . 'meetings/oauth.php';