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
    
    $order = wc_get_order($order_id);
    
    //$user_id = $order->get_user_id(); // or $order->get_customer_id();
     
    $user_id = $order->get_customer_id();
    
    $url = "https://zoom.us/oauth/authorize?response_type=code&client_id=".$client_id."&redirect_uri=".esc_url( REDIRECT_URI ).'&state='.$doctors_id.'-'.$order_id.'-'.$user_id;
    
    return $url;
    
    //return '<a href="'. $url .'">'.esc_html__('start consultation', ANOZOM_TEXTDOM).'</a>';
}

/**
 * Generate Zoom's meeting creation link by general cridentials
 * @param int $doctors_id
 * @return string
 */
function anony_get_general_oauth_zoom_link($doctors_id, $order_id){
    
    $order = wc_get_order($order_id);
    
    //$user_id = $order->get_user_id(); // or $order->get_customer_id();
     
    $user_id = $order->get_customer_id();
    
    $url = "https://zoom.us/oauth/authorize?response_type=code&client_id=".CLIENT_ID."&redirect_uri=".esc_url( REDIRECT_URI ).'&state='.$doctors_id.'-'.$order_id.'-'.$user_id;
    return $url;
    
   // return '<a href="'. $url .'">'.esc_html__('start consultation', ANOZOM_TEXTDOM).'</a>';
}

/**
 * get appointment data
 * @param obj $order
 * @return array
 */
function anony_get_appointment_data($order){
	foreach($order->get_meta_data() as $index => $item){
        
        foreach( $item->get_data() as $item_data){
            
            if(is_array($item_data) && isset($item_data['form_data'])){
                return $item_data['form_data'] ;
            }
    
        }
    }
}

/**
 * get appointment time zone 
 * @return string
 */
function wp_zoom_timezone_string() {
    $timezone_string = get_option( 'timezone_string' );
 
    if ( $timezone_string ) {
        return $timezone_string;
    }
 
    $offset  = (float) get_option( 'gmt_offset' );
    $hours   = (int) $offset;
    $minutes = ( $offset - $hours );
 
    $sign      = ( $offset < 0 ) ? '-' : '+';
    $abs_hour  = abs( $hours );
    $abs_mins  = abs( $minutes * 60 );
    $tz_offset = sprintf( 'UTC%s%02d:%02d', $sign, $abs_hour, $abs_mins );
 
    return $tz_offset;
}
/**
 * get appointment date
 * @param obj $order
 * @return array
 */
function anony_get_appointment_date($order){
    $months = [
        
            'يناير' => 'January',
            'فبراير' => 'February',
            'مارس' => 'March',
            'إبريل' => 'April',
            'مايو' => 'May',
            'يونيو' => 'June',
            'يوليو' => 'July',
            'أغسطس' => 'August',
            'سبتمبر' => 'September',
            'أكتوبر' => 'October',
            'نوفمبر' => 'November',
            'ديسمبر' => 'December',
        ];
    $data = anony_get_appointment_data($order);
    
    $date = $data['date'];

    foreach($months as $ar => $en){
        
        if(mb_strpos($date, $ar) !== false){
            $date = str_replace($ar, $en, $date);
            break;
        }
    }
    
    if(mb_strpos($date, 'ص') !== false){
        $date = str_replace('ص', 'am', $date);
    }else{
        $date = str_replace('م', 'pm', $date);
    }
    $date_format = get_option('date_format');
    $time_format = get_option('time_format');
    $format = $date_format.', '.$time_format;
	
	
	$date_obj = DateTime::createFromFormat($format, $date);
	
	$zoom_date = $date_obj->format('Y-m-d');
	$zoom_time = $date_obj->format('H:i:s');
	
	//$time_stamp = date("M d, Y, H:i",strtotime($date_time));
	
	return $zoom_date.'T'.$zoom_time;
}
function anony_checkin_markup($order){
    
    $order_status  = $order->get_status();
        
        
        if($order_status !== 'completed') {
            echo $order_status;
            
            return;
        }
        
        $doctors_id = anony_get_doctors_id($order);
        
        $checked_in = get_post_meta(intval($order->get_id()), 'appointment-checkin', true);
        
        $user = wp_get_current_user();
        
        $legible_roles = array_intersect(['anony_doctor', 'administrator'], (array) $user->roles);
        
        $token_data = anony_get_zoom_token_data($order);
        
        
 
        if(!$checked_in || $checked_in !== 'yes'){?>
        
            <div class="checkin-container">
           
                <?php if ( !empty($legible_roles) ) {?>
    
            		    <a href="#" class="check-state check-in" data-id="<?= $doctors_id ?>" data-order="<?= $order->get_id() ?>"><span class="zoom-loading-bg"></span><span class="zoom-loading"></span><?= esc_html__('Check-in', ANOZOM_TEXTDOM) ?></a>
    
                <?php }elseif(is_array($token_data) && !empty($token_data)){ 
                    
                    extract($token_data);
                
                ?>
    
            		    <a href="#" class="check-state is-out" data-id="<?= $doctors_id ?>" data-order="<?= $order->get_id() ?>"><?= esc_html__('Doctor out', ANOZOM_TEXTDOM) ?></a><span class="is-out-tip"><i class="fa fa-info-circle"></i></span>
            		    <div class="appointment-tip">
            		        
            		        <p><?= esc_html__('You will be able to start consulting, once your doctor checks in', ANOZOM_TEXTDOM) ?></p>
            		        
            		    </div>
                
                <?php }?> 
            </div>
            
       <?php }else{
       
        extract($token_data);
       
       ?>
           
            <div class="checkout-container">
                <?php if ( !empty($legible_roles) ) { ?>
                
    		        <a href="#" class="check-state check-out" data-id="<?= $doctors_id ?>" data-order="<?= $order->get_id() ?>"><span class="zoom-loading-bg"></span><span class="zoom-loading"></span><?= esc_html__('Check out', ANOZOM_TEXTDOM) ?></a>
    		        
    		        <?php if(!is_admin() || (is_admin() && defined('DOING_AJAX') && DOING_AJAX) ) : ?>
    		        
    		            <a href="<?= $join_url ?>" class="check-state start-consulting" data-id="<?= $doctors_id ?>" data-order="<?= $order->get_id() ?>"><i class="fa fa-video-camera"></i></a>
    		        
    		        <?php else: ?>
    		            
    		            <a href="<?= $join_url ?>" class="check-state" data-id="<?= $doctors_id ?>" data-order="<?= $order->get_id() ?>"><?= esc_html__('Start meeting', ANOZOM_TEXTDOM) ?></a>
    		            
    		        <?php endif; ?>
    		        
    		   <?php }else{?>
    		   
    		    <a href="<?= $join_url ?>" class="check-state start-consulting" data-id="<?= $doctors_id ?>" data-order="<?= $order->get_id() ?>"><i class="fa fa-video-camera"></i></a>
    		   <?php }?>
    		</div>

       <?php }
}
/**
 * Generats Zoom's checkin markup to admins' orders table
 * @param string $column
 * @return void
 */
function anony_add_order_zoom_link_admin_table_content( $column ) {
   
    global $post;
 
    if ( 'anony_zoom_link' === $column ) {
 
        $order = wc_get_order( $post->ID );?>
        
        <div id="zoom-controls-<?= $post->ID ?>">
		    <?php anony_checkin_markup($order);?>
		</div>
		
<?php	}
	
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