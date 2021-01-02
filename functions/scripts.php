<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
function anozom_styles(){

	
	$styles = array('zoom');
		
	$styles_libs = [];
	
	
	$styles = array_merge($styles, $styles_libs);
	
	foreach($styles as $style){
		
		$handle = in_array($style, $styles_libs) ? $style : 'anozom-' . $style;
		
		wp_enqueue_style( 
			$handle, 
			ANOZOM_URI . '/assets/css/'.$style.'.css', 
			false,
			filemtime(
				wp_normalize_path(ANOZOM_DIR . '/assets/css/'.$style.'.css' )
			) 
		);
	}
	
	if(is_rtl()){
		
	}

}
function anozom_scripts(){
    $scripts = array('zoom');
    
    $libs_scripts = [];
    
    $scripts = array_merge($scripts, $libs_scripts);
    
    foreach($scripts as $script){
		
		$handle = in_array($script, $libs_scripts) ? $script : 'anozom-' . $script;
		
		wp_enqueue_script( 
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
		'confirmCheckIn'   => esc_html__('Are you sure to check in?', ANOZOM_TEXTDOM),
	);
	wp_localize_script( 'anozom-zoom', 'anozomLoca', $anozom_loca );
}


//Theme Scripts
add_action('admin_enqueue_scripts',function() {
		
	anozom_scripts();
	anozom_styles();
	
});

add_action('wp_enqueue_scripts',function() {
		
	anozom_scripts();
	anozom_styles();

});

add_action('wp_footer', function(){?>

<?php if(is_singular('visit-type')) :

$providers = get_post_meta(get_the_ID(), 'relation_893768943c7de4ee57d2ed1ddb2e2c95', true);
//var_dump($providers);

?>
    
    <script type="text/javascript">
        
        jQuery(document).ready(function(){
            $('select[name ="doctors_id"]').val('<?= $providers ?>');
        });
        
        
    </script>
    
<?php endif ?>
    
<?php });

add_action('wp_head', function(){?>

<?php if(is_singular('visit-type')) : ?>
    
    <style type="text/css">
        
		.field-type-appointment_provider{
			display:none;
		}
        
        
    </style>
    
<?php endif ?>
    
<?php });