;(function($){
            
            "use strict";
            
            $(".check-in").on('click', function(e){
                
                e.preventDefault();
                
                var checkin = confirm(anozomLoca.confirmCheckIn);
                
                if(!checkin) return;
                
                var id = $(this).data('id');
                var order_id = $(this).data('order');
                
                $.ajax({
					type : 'POST',
					url : anozomLoca.ajaxURL, // admin-ajax.php URL
				
					data: 'action=anozom_create_meeting&doctors_id=' + id + '&order_id=' + order_id, // send form data + action parameter
					
					beforeSend: function(){},
					
					error: function (request, status, error) {
							if( status === 500 ){
								
								alert( 'Error while adding comment' );
							} else if( status === 'timeout' ){
								
								alert('Error: Server doesn\'t respond.');
							}
						},
					success: function(response){
					    
					    if( response.access === 'allow'){
					        window.open(response.link, '_blank') ;
					    }
						
						
					},
					
					complete: function(){}
	
			});
			
			
			
            });
            
            $(".check-out").on('click', function(e){
                
                e.preventDefault();
                
                var id = $(this).data('id');
                var order_id = $(this).data('order');
                
                $.ajax({
					type : 'POST',
					url : anozomLoca.ajaxURL, // admin-ajax.php URL
				
					data: 'action=anozom_appointment_checkout&doctors_id=' + id + '&order_id=' + order_id, // send form data + action parameter
					
					beforeSend: function(){},
					
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
					
					complete: function(){}
	
			});
			
			
			
            });
            
        })(jQuery);