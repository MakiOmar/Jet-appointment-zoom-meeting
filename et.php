	<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

	add_shortcode( 'anony-provider-appointments', function(){
	    global $post, $wpdb;
	    if(!is_single(  ) || $post->post_type !== 'doctors') return;
	    
	    $query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}jet_appointments WHERE provider = %d ORDER BY ID DESC", $post->ID);
	    $results = $wpdb->get_results($query, ARRAY_A );
	    
	    if(!empty($results) && !is_null($results)){
	    	
			foreach($results as $result){
				
				extract($result);
				$order = wc_get_order( intval($order_id) );
				
				if(!$order) continue;
				
				$order_status  = $order->get_status();
				
				if($order_status !== 'completed') continue;
				 		
				ob_start();
				
					anony_checkin_markup($order);
				
				$checkin_link = ob_get_clean();
				
				
				$service  = esc_html( get_the_title(intval($service)) );
				$provider = esc_html( get_the_title(intval($provider)) );
				
				$customer = get_userdata( $user_id );
				
				$customer_name = esc_html( $customer->data->user_nicename );
				
				$date = wp_date('F j, Y', $date);
				$time = wp_date('g:i a', $slot);
				
				//var_dump($result);
				
				?>
				
				<div class="appointment-item">
					
					<h2><?= $customer_name ?></h2>
					<h2><?= $provider ?></h2>
					<p><?= $service ?></p>
					<p><?= $date ?></p>
					<p><?= $time ?></p>
					
					<?= $checkin_link ?>
					
				</div>
				
			<?php }
		}
	    
	    
	} );