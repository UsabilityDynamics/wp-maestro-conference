<?php
/**
Name: Cron Job
*/

ini_set( "display_errors", 1);
set_time_limit(0);
ignore_user_abort(true);

if ( !empty($_POST) || defined('DOING_AJAX') || defined('DOING_CRON') )
  die();

/**
 * Tell WordPress we are doing the CRON task.
 *
 * @var bool
 */
if( !defined( 'DOING_CRON' ) ) define( 'DOING_CRON', true );
if( !defined( 'MC_CRON_RUNNING' ) ) define( 'MC_CRON_RUNNING', true );
// Pretend that we're executing an AJAX process. This should help WordPress not load all of the things.
if( !defined( 'DOING_AJAX' ) ) define('DOING_AJAX',true);
// Stop WordPress doing any of its normal output handling.
if( !defined( 'WP_USE_THEMES' ) ) define('WP_USE_THEMES',false);

/**
 * Determine request type ( HTTP or SHELL COMMAND )
 */

/* HTTP */
if( isset( $_SERVER[ 'REQUEST_URI' ] ) && isset( $_SERVER[ 'QUERY_STRING' ] ) ) {
  // Do nothing here since we know blog on HTTP request.
}
/* SHELL COMMAND */
else {
  // Multisite conditions
  if (!empty($_SERVER['argv'])){
    $argv = $_SERVER['argv'];

    $site_name = parse_url( $argv[1], PHP_URL_PATH );
    $site_domain = parse_url( $argv[1], PHP_URL_HOST );
    /**
     * Construct a fake $_SERVER global to get WordPress to load a specific site.
     * This avoids alot of messing about with switch_to_blog() and all its pitfalls.
     */
    $_SERVER=array(
      'HTTP_HOST'=>$site_domain,
      'REQUEST_METHOD'=>'GET',
      'REQUEST_URI'=>"{$site_name}/",
      'SERVER_NAME'=>$site_domain,
    );
    // Remove all our bespoke variables as they'll be in scope as globals and could affect WordPress
    unset($site_name,$site_domain);
  }
}

if ( !defined('ABSPATH') ) {

  $path = preg_replace( '%wp-content[/\\\\]plugins[/\\\\]wp-maestro-conference[/\\\\]cron.php%ix', 'wp-load.php', __FILE__ );

  if( !file_exists( $path ) ) {
    exit(1);
  }

  /** Set up WordPress environment */
  require_once( $path );

}

//** Ensure file was loaded and procesed */
if( !ABSPATH || !is_callable( 'ud_get_wp_maestro_conference' ) ) {
  exit(1);
}

/** Begin Loading Import*/
ud_get_wp_maestro_conference()->core->synchronize_conference();
exit(0);