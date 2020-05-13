<?php
/*
Plugin Name: Media Library Alt Fields
Description: Lets you change image alt text from the media library. Enable column under the <a href="http://codex.wordpress.org/Administration_Screens#Screen_Options">Screen Options</a> dropdown and click on the 'Image Alt Text' check box.
Version: 1.1
Author: Jarret Cade
Author URI: http://810media.com
Plugin URI: http://jarretcade.com/wordpress-plugins/media-library-alt-fields/
License: GPLv2 or later
*/

/*

COPYRIGHT 2012 Jarret Cade (email: jarret@810media.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define( 'MLAF_VERSION', '1.1' );

add_action( 'admin_enqueue_scripts', 'mlaf_add_js' );

function mlaf_add_js($hook) {
	// Check if we are on upload.php and enqueue script
	if ( $hook != 'upload.php' )
		return;
	wp_enqueue_script( 'mlaf', plugin_dir_url( __FILE__ ).'js/mlaf-script.js', array('jquery'), MLAF_VERSION, true );
}

add_action( 'manage_media_custom_column', 'mlaf_field_input' );
add_filter( 'manage_media_columns', 'mlaf_display_column' );

function mlaf_field_input( $column ) {
	// Set inputs to display in column
	if ( $column == 'mlaf-column' ) {
	global $post;
	?>
	<div class="altwrapper" id="wrapper-<?php echo $post->ID; ?>">
		<input type="text" id="alt-<?php echo $post->ID; ?>" value="<?php echo wp_strip_all_tags( get_post_meta( $post->ID, '_wp_attachment_image_alt', true ) ); ?>" />
		<input type="button" id="pid-<?php echo $post->ID; ?>" value="Save" class="button-primary mlaf-update" />
		<img class="waiting" style="display: none" src="<?php echo esc_url( admin_url( "images/wpspin_light.gif" ) ); ?>" />
	</div>
	<?php
}
}

function mlaf_display_column( $columns ) {
	// Register the column to display
	$columns['mlaf-column'] = 'Image Alt Text';
	return $columns;
	//return array_merge( $columns,
			//array( 'alt' => __( 'Image Alt Text' ) ) );
}

add_action( 'wp_ajax_mlaf_alt_update' , 'mlaf_alt_update' );

function mlaf_alt_update() {
	// Grab info from submission and update alt text
	$mlaf_post_id = absint ( $_POST['post_id'] );
	$mlaf_alt_text = wp_strip_all_tags( $_POST['alt_text'] );

	if ( !empty( $_POST['alt_text'] ) ) {
		update_post_meta( $mlaf_post_id, '_wp_attachment_image_alt', $mlaf_alt_text );
	}
}
