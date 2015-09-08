<?php

/*
Plugin Name: Lawyerist Admin Plugin
Plugin URI: http://lawyerist.com
Description: An admin plugin for Lawyerist.com.
Author: Sam Glover
Version: [See README.md for changelog]
Author URI: http://samglover.net
*/


/*------------------------------
Stylesheets
------------------------------*/

function lawyerist_admin_stylesheet() {
	wp_register_style( 'admin-stylesheet', plugins_url('lawyerist-admin-styles.css', __FILE__) );
}

add_action('admin_enqueue_scripts', 'lawyerist_admin_stylesheet');
add_action('login_enqueue_scripts', 'lawyerist_admin_stylesheet');
