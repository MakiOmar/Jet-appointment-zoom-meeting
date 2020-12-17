<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

//Hooked function is defined in admin.php
add_filter( 'woocommerce_my_account_my_orders_columns', 'anony_add_order_column_to_admin_table' );

/**
 * Shows join room markup
 *
 * @param \WC_Order $order the order object for the row
 */
function anony_orders_zoom_link( $order ) {

    $doctors_id = anony_get_doctors_id($order);
		
	
}
add_action( 'woocommerce_my_account_my_orders_column_anony_zoom_link', 'anony_orders_zoom_link' );




