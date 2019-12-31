<?php

/*
Plugin Name: Lawyerist Survival Plugin
Plugin URI: https://lawyerist.com
Description: An admin plugin for Lawyerist.com.
Author: Sam Glover
Version: [See README.md for changelog]
Author URI: http://lawyerist.com
*/

/* INDEX

Upload File Types
Stop Password Reset Emails
Remove Quickpress

*/


/*------------------------------
Upload File Types
------------------------------*/

function lap_add_upload_types($mime_types){
    $mime_types['json'] = 'application/json';
    return $mime_types;
}

add_filter( 'upload_mimes', 'lap_add_upload_types', 1, 1 );


/*------------------------------
Stop Password Reset Emails
------------------------------*/

if ( !function_exists( 'wp_password_change_notification' ) ) {
  function wp_password_change_notification() {}
}


/*------------------------------
Remove Quickpress
------------------------------*/

function remove_quickpress() {
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
}

add_action( 'wp_dashboard_setup', 'remove_quickpress' );
