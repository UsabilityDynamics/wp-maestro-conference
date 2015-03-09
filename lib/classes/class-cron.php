<?php
/**
 * Cron Manager
 *
 * @since 5.0.0
 */
namespace UsabilityDynamics\MaestroConference {

  if( !class_exists( 'UsabilityDynamics\MaestroConference\Cron' ) ) {

    final class Cron {

      /**
       * Constructor
       * Adds all specific hooks for managing cron stuff.
       *
       */
      public function __construct() {
        if ( !defined('MC_CRON_RUNNING') && !$this->get_cron_lock() ) {
          set_transient( 'mc_doing_cron', '1', 600 );
          /* SYNCHRONIZE DATA WITH MAESTRO CONFERENCE */
          if (isset($_GET['debug']) && $_GET['debug'] == true && isset($_GET['synchronize']) && $_GET['synchronize'] == true) {
            ud_get_wp_maestro_conference()->core->synchronize_conference();
            wp_redirect(admin_url() . '/edit.php?post_type=mconference&page=maestro_conferences_settings');
            exit();
          }
          /* MAYBE RUN CRON JOB */
          else {
            $this->maybe_run_cron();
          }
        }

      }

      /**
       * Runs every hour
       *
       * @author peshkov@UD
       */
      public function maybe_run_cron() {
        /* Break on AJAX, XMLRPC and POST requests */
        if (!empty($_POST) || defined('DOING_AJAX') || defined('XMLRPC_REQUEST')) {
          return;
        }
        /* Run cron once per hour. */
        if ( !$trnst = get_transient('mc:cron_job')) {
          $this->spawn_cron();
          set_transient('mc:cron_job', time(), 1 * HOUR_IN_SECONDS);
        }

      }

      /**
       * Send request to run cron through HTTP request that doesn't halt page loading
       * or via exec.
       *
       * @author peshkov@UD
       */
      public function spawn_cron() {

        $request = ud_get_wp_maestro_conference( 'cron.type' );

        switch ( $request ) {

          case 'http':
            $url = add_query_arg(array(
              'cb' => rand(),
            ), ud_get_wp_maestro_conference()->path('cron.php', 'url'));
            @wp_remote_get($url, array('blocking' => false, 'headers' => array('Cache-Control' => 'private, max-age=0, no-cache, no-store, must-revalidate')));
            break;

          case 'shell':
            $cron_path = ud_get_wp_maestro_conference()->path( 'cron.php', 'dir' );
            if( is_multisite() ) {
              @exec( 'nohup php -q ' . $cron_path . ' ' . get_site_url() . '  > /dev/null 2>&1 &' );
            } else {
              @exec( 'nohup php -q ' . $cron_path . '  > /dev/null 2>&1 &' );
            }
            break;

        }

        return true;
      }

      /**
       * Uncached doing_cron transient fetch
       *
       * @return bool|int|mixed
       */
      private function get_cron_lock() {
        global $wpdb;

        $value = 0;
        if ( wp_using_ext_object_cache() ) {
          /*
           * Skip local cache and force re-fetch of doing_cron transient
           * in case another process updated the cache.
           */
          $value = wp_cache_get( 'mc_doing_cron', 'transient', true );
        } else {
          $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", '_transient_mc_doing_cron' ) );
          if ( is_object( $row ) )
            $value = $row->option_value;
        }

        return $value;
      }

    }

  }

}
