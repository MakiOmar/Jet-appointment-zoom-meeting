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
 * Options page name
 */
define('ZOOM_JWT', true);

/**
 * Options page name
 */
define('ZOOM_OPTS_KEY', 'clinic-options');

$zoom_opts = get_option(ZOOM_OPTS_KEY);



add_action( 'admin_notices', function() {
    $zoom_opts = get_option(ZOOM_OPTS_KEY);

	if(
        !$zoom_opts || 
        empty($zoom_opts) ||
        !isset($zoom_opts['client-id'] ) ||
        empty($zoom_opts['client-id']) ||
        !isset($zoom_opts['client-secret'] ) ||
        empty($zoom_opts['client-secret']) ||
        !isset($zoom_opts['oauth-redirect-uri'] ) ||
        empty($zoom_opts['oauth-redirect-uri'])
    ){
        $url = admin_url();
        $url = add_query_arg('page', ZOOM_OPTS_KEY, $url);
	    ?>
	    <div class="notice notice-error is-dismissible">
	        <p><?php printf(__( 'Zoom oAuth data is missing or incomplete, so please make sure all data are correct from <a href="%s">Here</a>' ), $url); ?></p>
	    </div>
	<?php }
});

/**
 * If set to false, Zoom's OAuth will use general cridentials
 */
define('ZOOM_OAUTH_PER_USER', false);

/**
 * General zoom OAuth cridentials
 */
define('CLIENT_ID', $zoom_opts['client-id']);
define('CLIENT_SECRET', $zoom_opts['client-secret'] );
define('REDIRECT_URI', $zoom_opts['oauth-redirect-uri']);


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


require_once 'functions/scripts.php';

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

require_once ANOZOM_DIR . 'functions/helper.php';
require_once ANOZOM_DIR . 'callback.php';
require_once ANOZOM_DIR . 'functions/ajax/create-meeting.php';
require_once ANOZOM_DIR . 'functions/ajax/check.php';
require_once ANOZOM_DIR . 'functions/listings/provider.php';
require_once ANOZOM_DIR . 'functions/roles.php';
require_once ANOZOM_DIR . 'functions/woocommerce/endpint.php';


function _do_after_activate() {
    flush_rewrite_rules( true );
 
}
register_activation_hook( __FILE__, '_do_after_activate' );


add_action( 'jet-appointment/wc-integration/process-order',  'get_order_data' , 100, 3 );

function get_order_data($order_id, $order, $cart_item){
	$arr = [$order_id, $order, $cart_item];
	
	add_option('test-hook', $arr);
	
	
}

function zoom_protocol(){
    	$protocol = 'anozom_create_meeting';
    	if(ZOOM_JWT){
    	    $protocol = 'anozom_create_meeting_jwt';
    	} ?>
	    <input type="hidden" id="zoom-protocol" value="<?php echo $protocol ?>"/>
<?php }

add_action('admin_footer', 'zoom_protocol');
add_action('wp_footer', 'zoom_protocol');

/**
 * Load plugin textdomain.
 */
add_action( 'init',  function () {
  load_plugin_textdomain( ANOZOM_TEXTDOM, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
});
  

