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
Post & Page Status Dashboard Widgets
Scheduled Page Status Updates

*/


/**
* Upload File Types
*/

function lap_add_upload_types( $mime_types ){
    $mime_types[ 'json' ] = 'application/json';
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
* Post & Page Status Dashboard Widgets
*/
function lap_page_status_dashboard_widgets() {

  wp_add_dashboard_widget(
    'lap_product_page_status_widget',                 // Widget slug.
    esc_html__( 'Product Page Status', 'lawyerist' ), // Title.
    'lap_product_page_status_widget_render'           // Display function.
  );

  wp_add_dashboard_widget(
    'lap_page_status_widget',                       // Widget slug.
    esc_html__( 'Other Page Status', 'lawyerist' ), // Title.
    'lap_page_status_widget_render'                 // Display function.
  );

  wp_add_dashboard_widget(
    'lap_blog_post_status_widget',                 // Widget slug.
    esc_html__( 'Blog Post Status', 'lawyerist' ), // Title.
    'lap_blog_post_status_widget_render'           // Display function.
  );

}

add_action( 'wp_dashboard_setup', 'lap_page_status_dashboard_widgets' );


function lap_product_page_status_widget_render() {

  $args = array(
    'meta_key'        => '_wp_page_template',
    'meta_value'      => 'product-page.php',
    'post_status'     => 'any',
    'post_type'       => 'page',
    'posts_per_page'	=> -1,
  );

  $page_status_query = new WP_Query( $args );

  if ( $page_status_query->have_posts() ) :

    echo lap_render_status_widget( $page_status_query );

  endif;

}


function lap_page_status_widget_render() {

  $args = array(
    'meta_key'        => '_wp_page_template',
    'meta_value'      => 'product-page.php',
    'meta_compare'    => '!=',
    'post_status'     => 'any',
    'post_type'       => 'page',
    'posts_per_page'	=> -1,
  );

  $page_status_query = new WP_Query( $args );

  if ( $page_status_query->have_posts() ) :

    echo lap_render_status_widget( $page_status_query );

  endif;

}


function lap_blog_post_status_widget_render() {

  $args = array(
    'cat'             => 'blog-post',
    'post_status'     => 'any',
    'post_type'       => 'post',
    'posts_per_page'	=> -1,
  );

  $page_status_query = new WP_Query( $args );

  if ( $page_status_query->have_posts() ) :

    echo lap_render_status_widget( $page_status_query );

  endif;

}


function lap_render_status_widget( $page_status_query ) {

  $up_to_date   = 0;
  $needs_update = 0;
  $critical     = 0;
  $to_create    = 0;
  $no_status    = 0;

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

          $needs_update++;
          break;

        case null :
        default :

          $no_status++;
          break;

      }

    } elseif ( in_array( get_post_status(), [ 'draft', 'pending' ] ) ) {

      $to_create++;

    }

  endwhile;

  ob_start();

    ?>

    <style>

      table#page-status th {
        font-weight: bold;
        text-align: center;
      }

      table#page-status .published td  {
        text-align: center;
      }

      table#page-status .bignum {
        font-size: 300%;
        margin-bottom: 10.4px;
      }

    </style>

    <table id="page-status" class="widefat fixed" cellspacing="0">
      <thead>
        <tr>
          <th>Up to Date</th>
          <th>Needs Update</th>
          <th>Critical</th>
          <th>To Create</th>
        </tr>
      </thead>
      <tbody>
        <tr class="published">
          <td style="background-color: #b1ffb1;">
            <p class="bignum"><?php echo $up_to_date; ?></p>
            <p class="detail"><?php echo round( $up_to_date / $page_status_query->post_count * 100 ); ?>% of total (<?php echo $page_status_query->post_count; ?>)</p>
          </td>
          <td style="background-color: #ffffb1;">
            <p class="bignum"><?php echo $needs_update; ?></p>
          </td>
          <td style="background-color: #ffb1b1;">
            <p class="bignum"><?php echo $critical; ?></p>
          </td>
          <td>
            <p class="bignum"><?php echo $to_create; ?></p>
          </td>
        </tr>
      </tbody>
    </table>

    <p>No status: <?php echo $no_status; ?></p>

    <?php

  return ob_get_clean();

}



/**
* Scheduled Page Status Updates
*/
function lap_scheduled_page_status_updates() {

  $args = array(
    'ignore_sticky_posts' => 1,
    'post_status'         => 'publish',
    'post_type'           => array( 'page', 'post' ),
    'posts_per_page'      => -1,
  );

  $status_query = new WP_Query( $args );

  if ( $status_query->have_posts() ) :

    while ( $status_query->have_posts() ) : $status_query->the_post();

      $post_id        = get_the_ID();
      $date           = get_the_time( 'Ymd' );
      $last_modified  = get_the_modified_date( 'Ymd', $post_id );
      $six_months_ago = date( 'Ymd', strtotime( '-6 months' ) );
      $one_year_ago   = date( 'Ymd', strtotime( '-1 year' ) );

      if ( $last_modified < $one_year_ago ) {
        update_field( 'page_status', 'Critical', $post_id );
        update_post_meta( $post_id, gmdate( 'Y-m-d H:i:s', $last_modified ) ); // Resets the last-modified date.
      } elseif ( $last_modified < $six_months_ago ) {
        update_field( 'page_status', 'Needs Update', $post_id );
        update_post_meta( $post_id, gmdate( 'Y-m-d H:i:s', $last_modified ) ); // Resets the last-modified date.
      }

    endwhile;

  endif;

}

if( !wp_next_scheduled( 'import_into_db' ) ) {
  wp_schedule_event( strtotime( '02:00:00' ), 'daily', 'lap_cron_hook' );
}

add_action( 'lap_cron_hook', 'lap_scheduled_page_status_updates' );
