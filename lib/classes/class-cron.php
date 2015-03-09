<?php
/**
 * Cron Manager
 *
 * @since 5.0.0
 */
namespace UsabilityDynamics\WPP {

  if( !class_exists( 'UsabilityDynamics\MaestroConference\Cron' ) ) {

    final class Cron {

      /**
       * Constructor
       * Adds all specific hooks for managing cron stuff.
       *
       */
      public function __construct() {
        if ( !defined('MC_CRON_RUNNING') ) {
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
        if ( true || !$trnst = get_transient('mc:cron_job')) {
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

        echo "<pre>";
        var_dump( $request );
        echo "</pre>";
        die();

        switch ( $request ) {

          case 'http':
            $url = add_query_arg(array(
              'action' => 'do_xml_import',
              'hash' => $args['hash'],
              'cb' => rand(),
            ), ud_get_wpp_importer()->path('cron.php', 'url'));

            @wp_remote_get($url, array('blocking' => false, 'headers' => array('Cache-Control' => 'private, max-age=0, no-cache, no-store, must-revalidate')));
            break;

          case 'shell':
            $cron_path = ud_get_wpp_importer()->path( 'cron.php', 'dir' );
            //@exec('nohup curl "' . $url . '" > /dev/null 2>&1 &');
            @exec( 'nohup php -q ' . $cron_path . ' do_xml_import ' . $args['hash'] . ' > /dev/null 2>&1 &' );
            break;

        }

        return true;
      }

    }

  }

}
