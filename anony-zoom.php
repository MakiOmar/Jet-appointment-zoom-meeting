<?php
/**
 * Plugin Name: AnonyEngine Zoom meeting
 * Plugin URI: https://makiomar.com
 * Description: Connect with users with zoom, even per single service provider
 * Version: 1.0.0
 * Author: Mohammad Omar
 * Author URI: https://makiomar.com
 * Text Domain: anonyengine-zoom-meeting
 * License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

require_once 'vendor/autoload.php';

add_action('init', function(){
	if(!is_user_logged_in()) return;
});

/**
 * If set to false, Zoom's OAuth will use general cridentials
 */
define('ZOOM_OAUTH_PER_USER', false);

/**
 * General zoom OAuth cridentials
 */
define('CLIENT_ID', 'ORm5wHN1RLuVqHyl99VwyA');
define('CLIENT_SECRET', 'reQvdpJwulTCenVsoWgKBt0I58Fxsf3X');
define('REDIRECT_URI', 'https://manara.makiomar.com/zoom-auth/');

require_once 'zoom-link.php';

/**
 * Holds plugin's PATH
 * @const
 */ 
define('ANOZOM_DIR', wp_normalize_path(plugin_dir_path( __FILE__ )));

/**
 * Holds plugin's URI
 * @const
 */ 
define('ANOZOM_URI', plugin_dir_url( __FILE__ ));

/**
 * Holds plugin's text domain
 * @const
 */
define('ANOZOM_TEXTDOM', 'anonyengine-zoom-meeting');


require_once 'scripts.php';

/**
 * Holds plugin's classes
 * @const
 */
define('ANOZOM_ClASSES', ANOZOM_DIR . 'classes');

define('ANOZOM_AUTOLOADS' ,serialize(array(
	ANOZOM_ClASSES
)));


/*
*Classes Auto loader
*/
spl_autoload_register( function ( $class_name ) {

	if ( false !== strpos( $class_name, 'ANONY_Zoom_' )) {

		$class_name = preg_replace('/ANONY_Zoom_/', '', $class_name);

		$class_name  = strtolower(str_replace('_', '-', $class_name));
		
		$class_file = $class_name .'.php';

		if(file_exists($class_file)){

			require_once($class_file);
		}else{
			foreach(unserialize( ANOZOM_AUTOLOADS ) as $path){

				$class_file = wp_normalize_path($path).'/' .$class_name . '.php';				

				if(file_exists($class_file)){

					require_once($class_file);
				}else{

					$class_file = wp_normalize_path($path) .$class_name .'/' .$class_name . '.php';

					if(file_exists($class_file)){

						require_once($class_file);
					}
				}
			}
		}
		
	}
} );


require_once ANOZOM_DIR . 'callback.php';
require_once ANOZOM_DIR . 'functions/ajax/create-meeting.php';


add_action( 'jet-appointment/wc-integration/process-order',  'get_order_data' , 100, 3 );

function get_order_data($order_id, $order, $cart_item){
	$arr = [$order_id, $order, $cart_item];
	
	add_option('test-hook', $arr);
	
	
}

add_action('wp_footer', function(){
	
	$data = get_option('test-hook');
	echo '<pre>';
	//var_dump($data[1]);
	echo '</pre>';
});