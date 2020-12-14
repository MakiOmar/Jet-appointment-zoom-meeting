<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

function anozom_scripts(){
    $scripts = array('zoom');
    
    $libs_scripts = [];
    
    $scripts = array_merge($scripts, $libs_scripts);
    
    foreach($scripts as $script){
		
		$handle = in_array($script, $libs_scripts) ? $script : 'anozom-' . $script;
		
		wp_register_script( 
			$handle , 
			ANOZOM_URI . '/assets/js/'.$script.'.js' ,
			['jquery'],
			filemtime(
				wp_normalize_path(ANOZOM_DIR . '/assets/js/'.$script.'.js' )
			), 
			true 
		);
	}
	
	// Localize the script with new data tinymce_comments
	$anozom_loca = array(
		'ajaxURL'          => admin_url( 'admin-ajax.php' ),
		'textDir'          => (is_rtl() ? 'rtl' : 'ltr'),
		'themeLang'        => get_bloginfo('language'),
	);
	wp_localize_script( 'anozom-zoom', 'anozomLoca', $anozom_loca );
}


//Theme Scripts
add_action('admin_enqueue_scripts',function() {
		
	anozom_scripts();
	
	wp_enqueue_script('anozom-zoom');

});

add_action('admin_footer', function(){?>
    
    <script type="text/javascript">
        
        
        
    </script>
    
<?php });