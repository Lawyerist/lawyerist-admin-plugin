<?php

/*
Plugin Name: Lawyerist Survival Plugin
Plugin URI: https://lawyerist.com
Description: An admin plugin for Lawyerist.com.
Author: Sam Glover
Version: [See README.md for changelog]
Author URI: http://samglover.net
*/

/* INDEX

Admin Stylesheets
Remove Quickpress
Draft Posts Dashboard Widget
Custom Post Statuses

*/


/*------------------------------
Admin Stylesheets
------------------------------*/

function lap_admin_stylesheets() {
	wp_enqueue_style( 'admin-stylesheet', plugins_url('lap-admin-styles.css', __FILE__) );

	/* Stylesheet for the WordPress Text Editor */
	wp_enqueue_style('editor-styles', plugins_url('lap-text-editor-styles.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'lap_admin_stylesheets');
add_action('login_enqueue_scripts', 'lap_admin_stylesheets');


/* Add Stylesheet for the TinyMCE/Visual Editor */

function lap_mce_css( $mce_css ) {
	if ( ! empty( $mce_css ) )
		$mce_css .= ',';
	$mce_css .= plugins_url( 'lap-visual-editor-styles.css', __FILE__ );
	return $mce_css;
}
add_filter( 'mce_css', 'lap_mce_css' );


/*------------------------------
Remove Quickpress
------------------------------*/

function remove_quickpress() {
	remove_meta_box('dashboard_quick_press','dashboard','side');
}

add_action('wp_dashboard_setup','remove_quickpress');


/*------------------------------
Draft Posts Dashboard Widget
Mostly taken from this plugin: wordpress.org/plugins/draft-posts-widget
------------------------------*/

function draft_posts_widget_function() {

  $curr_user_id = get_current_user_id( );

  global $wpdb,$post;

  $posts = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_status = 'draft' AND ( post_type = 'post' AND post_author = $curr_user_id ) ORDER BY post_modified DESC LIMIT 10" );

	if ( $posts ) {

		echo "<ul>\n";

		foreach ( $posts as $post ) {

			setup_postdata( $post );

			$time = get_post_modified_time('G', true);

			if ( ( abs(time() - $time) ) < 86400 )

				$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );

			else

				$h_time = mysql2date(__('Y-m-d'), $post->post_modified);

			$posttitle = get_the_title();

			if ( empty($posttitle) )

				$posttitle = __('(no title)');

			$title = ( current_user_can( 'edit_post', $post->ID ) ) ? '<a href="post.php?action=edit&amp;post=' . $post->ID . '">' . $posttitle . '</a>' : '<span style="text-decoration:underline">' . $posttitle . '</span>';

			echo '	<li>' . sprintf( __('%s &#40;last updated %s&#41;', 'dashboard-draft-posts'), $title,  '<abbr title="' . get_the_modified_time(__('Y/m/d g:i:s A')) . '">' . $h_time . '</abbr>' ) . "</li>\n";

		}

	echo "</ul>\n";

	echo '<p class="textright"><a class="button" href="edit.php?post_status=draft&author=' . $curr_user_id . '">View All Drafts</a></p>';

	} else {

		echo '<p>' . __("No drafts.", 'dashboard-draft-posts') . "</p>\n";

	}

}


function add_draft_posts_widget() {
  wp_add_dashboard_widget('draft_posts_widget', 'My Drafts', 'draft_posts_widget_function');
}

add_action('wp_dashboard_setup', 'add_draft_posts_widget');


/*------------------------------
Custom Post Statuses

Cannot implement until WordPress fixes this:
https://core.trac.wordpress.org/ticket/12706

function post_status_submit_for_review() {

	$args = array(
		'label'                     => 'Submit for Review',
		'label_count'               => 'Submit for Review (%s)',
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	);
	register_post_status( 'Submit for Review', $args );

}
add_action( 'init', 'post_status_submit_for_review', 0 );


function post_status_in_revision() {

	$args = array(
		'label'                     => 'In Revision',
		'label_count'               => 'In Revision (%s)',
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	);
	register_post_status( 'In Revision', $args );

}
add_action( 'init', 'post_status_in_revision', 0 );


// Register Custom Status
function post_status_editing() {

	$args = array(
		'label'                     => 'Editing',
		'label_count'               => 'Editing (%s)',
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	);
	register_post_status( 'Editing', $args );

}
add_action( 'init', 'post_status_editing', 0 );


// Register Custom Status
function post_status_eic_review() {

	$args = array(
		'label'                     => 'EIC Review',
		'label_count'               => 'EIC Review (%s)',
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	);
	register_post_status( 'EIC Review', $args );

}
add_action( 'init', 'post_status_eic_review', 0 );


// Register Custom Status
function post_status_proof() {

	$args = array(
		'label'                     => 'Proof',
		'label_count'               => 'Proofs (%s)',
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	);
	register_post_status( 'Proof', $args );

}
add_action( 'init', 'post_status_proof', 0 );


// Register Custom Status
function post_status_sponsored_proof() {

	$args = array(
		'label'                     => 'Sponsored Proof',
		'label_count'               => 'Sponsored Proofs (%s)',
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	);
	register_post_status( 'Sponsored Proof', $args );

}
add_action( 'init', 'post_status_sponsored_proof', 0 );

------------------------------*/
