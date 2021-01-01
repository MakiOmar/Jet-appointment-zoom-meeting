<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.


add_action('admin_init', function() {  
    //add the new user role
    add_role(
        'anony_doctor',
        'Doctor',
        array(
            'read'          => true,
        )
    );
    
    add_role(
        'anony_patient',
        'Patient',
        array(
            'read'          => true,
        )
    );
 
});

add_filter('wp_dropdown_users', function ($output) 
    {
        global $post;

        //Doing it only for the custom post type
        if($post->post_type == 'doctors')
        {
            $users = get_users(array('role__in'=>['anony_doctor', 'administrator']));
           //We're forming a new select with our values, you can add an option 
           //with value 1, and text as 'admin' if you want the admin to be listed as well, 
           //optionally you can use a simple string replace trick to insert your options, 
           //if you don't want to override the defaults
           $output .= "<select id='post_author_override' name='post_author_override' class=''>";
        foreach($users as $user)
        {
            $output .= "<option value='".$user->id."'".selected($user->id, $post->post_author, false).">".$user->user_login."</option>";
        }
        $output .= "</select>";
     }
     return $output;
    });
    