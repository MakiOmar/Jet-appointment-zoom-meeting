;(function($){
            
            "use strict";
            $(".is-out-tip").on('click', function(e){
                var parent = $(this).parent();
                
                var target = parent.find('.appointment-tip');
                if(target.hasClass('show-tip')){
                    target.removeClass('show-tip');
                }else{
                    target.addClass('show-tip');
                }
            });
            
            $(".check-in").on('click', function(e){
                
                e.preventDefault();
                
                var checkin = confirm(anozomLoca.confirmCheckIn);
                
                if(!checkin) return;
                
                var id = $(this).data('id');
                var order_id = $(this).data('order');
                var clicked = $(this);
                
	
                
                $.ajax({
					type : 'POST',
					url : anozomLoca.ajaxURL, // admin-ajax.php URL
				
					data: 'action=anozom_create_meeting&doctors_id=' + id + '&order_id=' + order_id, // send form data + action parameter
					
					beforeSend: function(){
					    clicked.find('.zoom-loading, .zoom-loading-bg').show();
					},
					
					error: function (request, status, error) {
							if( status === 500 ){
								
								alert( 'Error while adding comment' );
							} else if( status === 'timeout' ){
								
								alert('Error: Server doesn\'t respond.');
							}
						},
					success: function(response){
					    console.log(response.msg)
					    if( response.access === 'allow'){
					        window.open(response.link, '_blank') ;
					    }
						
					},
					
					complete: function(){
					    
					    clicked.find('.zoom-loading, .zoom-loading-bg').hide();
					}
	
			});
			
			
			
            });
            
            $(".check-out").on('click', function(e){
                
                e.preventDefault();
                
                var id = $(this).data('id');
                var order_id = $(this).data('order');
                var clicked = $(this);
                
                $.ajax({
					type : 'POST',
					url : anozomLoca.ajaxURL, // admin-ajax.php URL
				
					data: 'action=anozom_appointment_checkout&doctors_id=' + id + '&order_id=' + order_id, // send form data + action parameter
					
					beforeSend: function(){
					    clicked.find('.zoom-loading, .zoom-loading-bg').show();
					},
					
					error: function (request, status, error) {
							if( status === 500 ){
								
								alert( 'Error while adding comment' );
							} else if( status === 'timeout' ){
								
								alert('Error: Server doesn\'t respond.');
							}
						},
					success: function(response){
					    if(response.updated === true){
					        location.reload();
					    }
					},
					
					complete: function(){
					    clicked.find('.zoom-loading, .zoom-loading-bg').hide();
					}
	
			});
			
			
			
            });
            
            $(".appointment-json").each(function(){
                var el = $(this);
                
                var order_id= el.data('id');
                
                var appointment_json = JSON.parse(el.val());
                
                var sub_domain = 'api';
                
                console.log(appointment_json.is_mobile);
                
                if(appointment_json.is_mobile === false){
                    
                    sub_domain = 'web';
                }
                
                var whatsAppText =    'Zoom URL : ' + appointment_json.join_url  + "\r\n\r\n" 
                + 'Zoom password : ' + appointment_json.join_pass + "\r\n\r\n" 
                + 'Customer name : ' + appointment_json.customer_name + "\r\n\r\n" 
                + 'Visit type : ' + appointment_json.visit_type + "\r\n\r\n" 
                + 'Date : ' + appointment_json.appointment_date + "\r\n\r\n" 
                + 'Time : ' + appointment_json.appointment_time;
                
                var _text_ = window.encodeURIComponent(whatsAppText);
                $('.send-whatsapp-' + order_id).attr('href', 'https://'+sub_domain+'.whatsapp.com/send?text=' + _text_ );
            });
            
        })(jQuery);