<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

add_filter( 'manage_edit-shop_order_columns', 'anony_add_order_column_to_admin_table' );
add_filter( 'woocommerce_my_account_my_orders_columns', 'anony_add_order_column_to_admin_table' );
/**
 * Add Zoom's link column to orders table
 * @param array $column
 * @return array
 */
function anony_add_order_column_to_admin_table( $columns ) {
    $columns['anony_zoom_link'] = 'Zoom link';
    return $columns;
}

/**
 * Get Zoom OAuth credintals
 * @param int $doctors_id
 * @return array
 */
function anony_get_zoom_oauth($doctors_id){
    
    $zoom_data['client_id'] = get_post_meta($doctors_id, 'zoom-oauth-client-id', true);
    
    $zoom_data['client_secret'] = get_post_meta($doctors_id, 'zoom-oauth-client-secret', true);
    
    return $zoom_data;
}

/**
 * Get doctor's id from order
 * @param obj $order
 * @return int
 */
function anony_get_doctors_id($order){
    
    foreach($order->get_meta_data() as $index => $item){
            
        foreach( $item->get_data() as $item_data){
            
            if(is_array($item_data) && isset($item_data['form_data'])){
                return $item_data['form_data']['doctors_id'];
            }
  
        }
    }
}


/**
 * Generate Zoom's meeting creation link by doctors id
 * @param int $doctors_id
 * @return string
 */
function anony_get_zoom_link($doctors_id, $order_id){
    $zoom_data = anony_get_zoom_oauth($doctors_id);
		
    extract($zoom_data);
    
    $url = "https://zoom.us/oauth/authorize?response_type=code&client_id=".$client_id."&redirect_uri=".esc_url( REDIRECT_URI ).'&state='.$doctors_id.'-'.$order_id;
    
    return $url;
    
    //return '<a href="'. $url .'">'.esc_html__('start consultation', ANOZOM_TEXTDOM).'</a>';
}

/**
 * Generate Zoom's meeting creation link by general cridentials
 * @param int $doctors_id
 * @return string
 */
function anony_get_general_oauth_zoom_link($doctors_id, $order_id){

    
    $url = "https://zoom.us/oauth/authorize?response_type=code&client_id=".CLIENT_ID."&redirect_uri=".esc_url( REDIRECT_URI ).'&state='.$doctors_id.'-'.$order_id;
    return $url;
    
   // return '<a href="'. $url .'">'.esc_html__('start consultation', ANOZOM_TEXTDOM).'</a>';
}

/**
 * Generats Zoom's checkin markup to admins' orders table
 * @param string $column
 * @return void
 */
function anony_add_order_zoom_link_admin_table_content( $column ) {
   
    global $post;
 
    if ( 'anony_zoom_link' === $column ) {
 
        $order = wc_get_order( $post->ID );
        
        
		$doctors_id = anony_get_doctors_id($order);?>
		
		
		<div class="checkin container">
		    
		    <a href="#" class="button-primary check-in" data-id="<?= $doctors_id ?>" data-order="<?= $post->ID ?>"><?= esc_html__('Check-in', ANOZOM_TEXTDOM) ?></a>
		    <div class="check-in-links"></div>
		    
		</div>
    <?php }
	
}

add_action( 'manage_shop_order_posts_custom_column', 'anony_add_order_zoom_link_admin_table_content' );

/**
 * Shows join room markup
 *
 * @param \WC_Order $order the order object for the row
 */
function anony_orders_zoom_link( $order ) {

    $doctors_id = anony_get_doctors_id($order);
		
	
}
add_action( 'woocommerce_my_account_my_orders_column_anony_zoom_link', 'anony_orders_zoom_link' );