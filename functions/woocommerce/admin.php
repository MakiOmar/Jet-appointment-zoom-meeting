<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

add_filter( 'manage_edit-shop_order_columns', 'anony_add_order_column_to_admin_table' );

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



