<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

function anony_provider_appointments($provider_id, $query_by = 'provider'){
	    global $wpdb;
	    
	    $query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}jet_appointments WHERE {$query_by} = %d ORDER BY ID DESC", $provider_id);
	    $results = $wpdb->get_results($query, ARRAY_A );
	    
	    if(!empty($results) && !is_null($results)){
	    	
			foreach($results as $result){
				
				extract($result);
				
				
				$order = wc_get_order( intval($order_id) );
				
				
				
				if(!$order) continue;
				
				$order_status  = $order->get_status();
				
				$service  = sprintf(esc_html__('%s clinic', ANOZOM_TEXTDOM), esc_html( get_the_title(intval($service)) ));
				$provider = esc_html( get_the_title(intval($provider)) );
				
				$customer_name = esc_html( $order->get_billing_first_name() ) .' '.esc_html( $order->get_billing_last_name() );
				
				$date = date_i18n('F j, Y', $date);
				$time = date_i18n('g:i a', $slot);
				
				if($order_status !== 'completed') {
					echo '<div class="appointment-item-wrapper">';
					printf(esc_html__('Your %1s appointment booked at %2s %3s   will appear once your payments are completed. If you think there is a problem, please contact us', ANOZOM_TEXTDOM), $service, $date, $time );
					echo '</div>';
					continue;
				}
				
				$checkedin = get_post_meta(intval($order_id), 'appointment-checkin', true);
				
				$join_url = '';
				
				$join_pass = '';
				
				if ($checkedin == 'yes') {
					$meeting_crids = anony_get_meeting_crids($provider, $order_id, $user_id);
				
					extract($meeting_crids);
				}
				
				
				 		
				ob_start();
				
					anony_checkin_markup($order);
				
				$checkin_link = ob_get_clean();
				
				$user = wp_get_current_user();
				
				$legible_roles = array_intersect(['anony_doctor', 'administrator'], (array) $user->roles);
				
				$appointment_json = json_encode([
				    
				        'customer_name' => $customer_name,
				        'visit_type' => $service,
				        'appointment_date' => $date,
				        'appointment_time' => $time,
				        'join_url' => $join_url,
				        'join_pass' => $join_pass,
				        'is_mobile' => wp_is_mobile(),
				    
				    ]);
				    
				
				?>
				<div class="appointment-item-wrapper">
				    <input class="appointment-json" data-id="<?= $order_id ?>" id='appointment-json-<?= $order_id ?>' type='hidden' value='<?= $appointment_json ?>'/>
				    <ul class="appointment-item">
					
    					<li><i class="fa fa-user"></i>&nbsp;<?= $customer_name ?></li>
    					<li><i class="fa fa-user-md"></i>&nbsp;<?= $provider ?></li>
    					<li><i class="fa fa-stethoscope"></i>&nbsp;<?= $service ?></li>
    					<li><i class="fa fa-calendar"></i>&nbsp;<?= $date ?></li>
    					<li><i class="fa fa-clock"></i>&nbsp;<?= $time ?></li>
    					<?php if(!empty($legible_roles)) : ?>
    					    
    					<li><i class="fa fa-whatsapp"></i>&nbsp;<a class="send-whatsapp-<?= $order_id  ?>" href="#" target="_blank"><?= esc_html__('Send Meeting data') ?></a></li>
    					    
    					<?php endif ?>
    					
    					
    				</ul>
    				<div id="zoom-controls-<?= $order_id ?>">
    				    <?= $checkin_link ?>
    				</div>
    				
				</div>
				
				
			<?php }
		}else{
		    esc_html_e('Sorry! No appointments available. Appointments will appear once your payments are completed. If you think there is a problem, please contact us', ANOZOM_TEXTDOM);
    	    return;
		}
	    
	    
}