<?php

/**
* Plugin Name: Lawyerist Survival Plugin
* Plugin URI: https://lawyerist.com
* Description: An admin plugin for Lawyerist.com.
* Author: Sam Glover
* Version: [See README.md for changelog]
* Author URI: http://lawyerist.com
*/

/* INDEX

Upload File Types
Stop Password Reset Emails
Remove Quickpress
Page Status Dashboard Widget

*/


/**
* Upload File Types
*/

function lap_add_upload_types($mime_types){
    $mime_types['json'] = 'application/json';
    return $mime_types;
}

add_filter( 'upload_mimes', 'lap_add_upload_types', 1, 1 );


/**
* Stop Password Reset Emails
*/

if ( !function_exists( 'wp_password_change_notification' ) ) {
  function wp_password_change_notification() {}
}


/**
* Remove Quickpress
*/

function remove_quickpress() {
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
}

add_action( 'wp_dashboard_setup', 'remove_quickpress' );


/**
* Page Status Dashboard Widget
*/
function lap_page_status_dashboard_widgets() {

  wp_add_dashboard_widget(
    'lap_page_status_widget',           // Widget slug.
    esc_html__( 'Product Page Status', 'lap' ), // Title.
    'lap_page_status_widget_render'     // Display function.
  );

}

add_action( 'wp_dashboard_setup', 'lap_page_status_dashboard_widgets' );

function lap_page_status_widget_render() {

  $args = array(
    'meta_key'        => '_wp_page_template',
    'meta_value'      => 'product-page.php',
    'post_status'     => 'any',
    'post_type'       => 'page',
    'posts_per_page'	=> -1,
  );

  $page_status_query = new WP_Query( $args );

  if ( $page_status_query->have_posts() ) :

    $up_to_date   = 0;
    $needs_update = 0;
    $critical     = 0;
    $to_create    = 0;

    while ( $page_status_query->have_posts() ) : $page_status_query->the_post();

      if ( get_post_status() == 'publish' ) {

        switch ( get_field( 'page_status' ) ) {

          case 'Up to Date' :
            $up_to_date++;
            break;

          case 'Critical' :
            $critical++;
            break;

          case 'Needs Update' :
          default :
            $needs_update++;
            break;

        }

      } elseif ( in_array( get_post_status(), [ 'draft', 'pending' ] ) ) {

        $to_create++;

      }

    endwhile;

  endif;

  ?>

  <style>

  table#page-status th {
    font-weight: bold;
    text-align: center;
  }

  table#page-status .published td {
    font-size: 300%;
    text-align: center;
  }

  table#page-status .to-create td {
    font-size: 14px;
    font-weight: bold;
    text-align: center;
  }

  </style>

  <table id="page-status" class="widefat fixed" cellspacing="0">
    <thead>
      <tr>
        <th>Up to Date</th>
        <th>Needs Update</th>
        <th>Critical</th>
      </tr>
    </thead>
    <tbody>
      <tr class="published">
        <td style="background-color: #b1ffb1;"><?php echo $up_to_date; ?></td>
        <td style="background-color: #ffffb1;"><?php echo $needs_update; ?></td>
        <td style="background-color: #ffb1b1;"><?php echo $critical; ?></td>
      </tr>
      <tr class="to-create">
        <td colspan="3">To create: <?php echo $to_create; ?></td>
      </tr>
    </tbody>
  </table>

  <?php

}
