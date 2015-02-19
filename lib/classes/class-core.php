<?php

/**
 * Bootstrap
 *
 * @since 1.0.0
 */

namespace UsabilityDynamics\MaestroConference {

  if (!class_exists('UsabilityDynamics\MaestroConference\Core')) {

    final class Core extends Scaffold {

      /**
       *
       */
      public function __construct() {
        parent::__construct();

        /**
         * Register Custom Post Types, meta and set their taxonomies
         */
        $schema = $this->instance->get_schema('extra.schemas.model');
        if (!empty($schema) && is_array($schema)) {
          \UsabilityDynamics\Model::define($schema);
        }

        /**
         * Maybe init shortcodes
         */
        $this->load_files($this->instance->path('lib/shortcodes', 'dir'));

        /**
         * Setup setting for plugin on admin panel
         */
        $this->ui = new UI();
        /* Hooks */
        add_action('save_post', array($this, 'save_conference'), 99, 3);
        add_action('admin_notices', array($this, 'error_notice'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        if (isset($_GET['debug']) && $_GET['debug'] == true && isset($_GET['synchronize']) && $_GET['synchronize'] == true) {
          self::synchronize_conference();
        }
        if (!wp_next_scheduled('mc_synchronize_cron')) {
          wp_schedule_event(time(), 'hourly', 'mc_synchronize_cron');
        }
        add_action('mc_synchronize_cron', 'synchronize_conference');
		    add_filter( 'manage_edit-maestro_conference_columns', array($this, 'set_custom_edit_book_columns') );
        add_action( 'manage_maestro_conference_posts_custom_column' , array($this, 'custom_book_column'), 10, 2 );
      }
	  
	   /**
       * Add custom columns to conference list
       *
       * @param array $columns Columns
       */
	  public function set_custom_edit_book_columns($columns) {

        $new_columns = array(
          'mc_active' => __('Active', ud_get_wp_maestro_conference('domain')),
          'mc_status' => __('Status', ud_get_wp_maestro_conference('domain')),
          'mc_scheduledStartDate' => __('Start date', ud_get_wp_maestro_conference('domain')),
        );
          return array_merge($columns, $new_columns);
      }
      
	   /**
       * Add values to custom columns
       *
       * @param string $column Current column
	   * @param int $post_id Post ID
       */
      public function custom_book_column( $column, $post_id ) {
        switch ( $column ) {
        case 'mc_active' :
          $conference_is_active = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'is_active', true);
          echo ($conference_is_active == 1) ? "Yes" : "No";
          break;
        case 'mc_status' :
          $conference_status = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'status', true);
          echo $conference_status;
          break;
        case 'mc_scheduledStartDate' :
          $conference_date = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'scheduledStartDate', true);
          echo $conference_date;
          break;
        }
    }

      /**
       * Includes all PHP files from specific folder
       *
       * @param string $dir Directory's path
       */
      static public function load_files($dir = '') {
        $dir = trailingslashit($dir);
        if (!empty($dir) && is_dir($dir)) {
          if ($dh = opendir($dir)) {
            while (( $file = readdir($dh) ) !== false) {
              if (!in_array($file, array('.', '..')) && is_file($dir . $file) && 'php' == pathinfo($dir . $file, PATHINFO_EXTENSION)) {
                include_once( $dir . $file );
              }
            }
            closedir($dh);
          }
        }
      }

      /**
       * Conference synchronization between MC and website conferences
       *
       * @todo Vladimir, fix is_active status. It breaks the logic. http://screencast.com/t/zlL33QiNYJix
       * @todo Vladimir, add admin notices for manual syncronization
       * @return string
       */
      public function synchronize_conference() {
        $conference = self::get_nearest_conference();
        
        if ( $conference->post && is_object( $this->instance->client ) ) {
          $current_active_conference = self::get_current_active_conference();
          if ($current_active_conference->post) {
            update_post_meta($current_active_conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'is_active', '0');
            update_post_meta($current_active_conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'status', 'closed');
          }
          update_post_meta($conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'is_active', '1');

          try {

            /* Get details about conference from Maestro Conference service */
            $mc_conference = $this->instance->client->getConferenceData($this->instance->get('api.conference_uid'));
            if ($mc_conference['code'] != 0) {
              throw new \Exception(__($mc_conference['message'], ud_get_wp_maestro_conference('domain')));
            }

            /* Removing old callers from conference */
            if (isset($mc_conference['response']['value']['person'])) {
              foreach ($mc_conference['response']['value']['person'] as $person) {
                if ($person['role'] == 'PARTICIPANT') {
                  $response = $this->instance->client->removePerson($this->instance->get('api.conference_uid'), $person['UID']);
                  if ($response['code'] != 0) {
                    throw new \Exception(__($response['message'], ud_get_wp_maestro_conference('domain')));
                  }
                }
              }
            }

            /* Adding new callers from our DB */
            $local_participants = Utility::get_local_participants($conference->post->ID);
            $count_local_participants = 0;
            if (!empty($local_participants)) {
              foreach ($local_participants as $local_participant) {
                if (!empty($local_participant['wp_user_id'])) {
                  $response = self::_cron_add_person_to_conference($conference->post->ID, $local_participant['wp_user_id']);
                  if ($response['code'] != 0) {
                    throw new \Exception(__($response['message'], ud_get_wp_maestro_conference('domain')));
                  }
                  $response['value']['wp_user_id'] = $local_participant['wp_user_id'];
                  $persons[] = $response['value'];
                  $count_local_participants++; // Increment the added person count.
                }
              }
            }

            /* if our callers < 24 - adding fake users */
            if ($count_local_participants < 24) {
              for ($i = 0; $i < (24 - $count_local_participants); $i++) {
                $response = self::_cron_add_fake_user_to_conference($conference->post->ID);
                if ($response['code'] != 0) {
                  throw new \Exception(__($response['message'], ud_get_wp_maestro_conference('domain')));
                }
                $response['value']['wp_user_id'] = '';
                $persons[] = $response['value'];
              }
            }

            /* remove callers in our conference */
            delete_post_meta( $conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'participants' );

            //adding updated callers to our DB
            add_post_meta($conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'participants', $persons, true);

          } catch (\Exception $e) {
            update_post_meta($conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'is_active', '0');
            /* Add error notice */
            //Add error to log ???
            return false;
          }
        }
      }

      /**
       * Select nearest conference
       *
       * Must not be called directly
       *
       * @author PLAN
       */
      public function get_nearest_conference() {
        $query_string = array(
            'post_type' => 'mconference',
            'posts_per_page' => '1',
            'orderby' => 'post_date',
            'order' => 'DESC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'mc_scheduledStartDate',
                    'value' => date('Y-m-d H:i:s', strtotime('+' . $this->instance->get('conference.cancel_reg_max_time') . ' hours')),
                    'compare' => '<='
                ),
                array(
                    'key' => 'mc_status',
                    'value' => 'active',
                    'compare' => '='
                ),
                array(
                    'key' => ud_get_wp_maestro_conference('prefix') . 'is_active',
                    'value' => '0',
                    'compare' => '='
                )
            ),
            'post_status' => 'publish');
        $conference = new \WP_Query($query_string);
        return $conference;
      }

      /**
       * Select current active conference
       *
       * @author PLAN
       */
      static public function get_current_active_conference() {
        $query_string = array(
            'post_type' => 'mconference',
            'posts_per_page' => '1',
            'meta_query' => array(
                array(
                    'key' => 'mc_is_active',
                    'value' => '1',
                    'compare' => '='
                ),
                array(
                    'key' => 'mc_status',
                    'value' => 'active',
                    'compare' => '='
                )
            ),
            'post_status' => 'any');
        $conference = new \WP_Query($query_string);
        return $conference;
      }

      /**
       * Check nearest by date
       * @param date $date
       * @param int $current_conference_id Wordpress conference ID.
       *
       * @author PLAN
       */
      static public function check_nearest_conferences_by_date($date, $current_conference_id) {
        $query_string = array(
            'post_type' => 'mconference',
            'posts_per_page' => '1',
            'post__not_in' => array($current_conference_id),
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'mc_scheduledStartDate',
                    'value' => array(date('Y-m-d H:i:s', strtotime($date . '-30 hours')), date('Y-m-d H:i:s', strtotime($date . '+30 hours'))),
                    'compare' => 'BETWEEN',
                    'type' => 'DATETIME'
                ),
                array(
                    'key' => 'mc_status',
                    'value' => 'active',
                    'compare' => '='
                )
            ),
            'post_status' => 'publish');
        $conference = new \WP_Query($query_string);
        return $conference;
      }

      /**
       *  Add person to conference
       *
       * @param int $wp_conference_id Wordpress conference ID.
       * @param int $user_id User ID.
       * @param string $role User role.
       *
       *  @author PLAN
       */
      private function _cron_add_person_to_conference($wp_conference_id, $user_id = '', $role = 'PARTICIPANT') {
        $user = get_user_by("id", $user_id);
        //if user not exist or user already has access or can't take part on this conference
        if ($user) {
          $user = apply_filters('before_add_person_conference', $user);
          $result = $this->instance->client->addPerson($this->instance->get('api.conference_uid'), $role, $user->display_name);
          $response = $result['response'];
          if ($response['code'] != 0) {
            apply_filters('error_cron_add_person_conference', $user_id);
            return $response['message'];
          }
          add_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_PIN_' . $wp_conference_id, $response['value']['PIN'], true);
          //add notification to user
          do_action('after_add_person_conference', $user);
          return $response;
        }
        $response = array(
            'code' => '-1',
            'message' => __('User is not exist', ud_get_wp_maestro_conference('domain')),
            'value' => array()
        );
        return $response;
      }

      /**
       *  Add fake user to conference
       *
       * @param int $wp_conference_id Wordpress conference ID.
       *
       * @author PLAN
       * @return array $response
       */
      private function _cron_add_fake_user_to_conference($wp_conference_id) {
        $conference_is_active = get_post_meta($wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'is_active', true);
        //if conference is active now
        if ($conference_is_active == '1') {
          $result = $this->instance->client->addPerson($this->instance->get('api.conference_uid'), 'PARTICIPANT', 'Empty');
          $response = $result['response'];
          if ($response['code'] != 0) {
            apply_filters('error_cron_add_fake_user_conference', $user_id);
            return $response['message'];
          }
          return $response;
        }
        $response = array(
            'code' => '-1',
            'message' => 'Conference is not active',
            'value' => array()
        );
        return $response;
      }

      /**
       * Add meta box for multiple post types
       *
       * @return void
       */
      function add_meta_boxes() {
        add_meta_box( 'mc_callers', __('Participants', ud_get_wp_maestro_conference('domain')), array($this, 'show_metabox'), 'mconference', 'normal', 'high' );
      }

      /**
       * Callback function to show fields in meta box
       *
       * @return void
       */
      function show_metabox() {
        global $post;
        /*enqueue js script*/
        wp_enqueue_script('mc-admin', ud_get_wp_maestro_conference()->path('static/scripts/mc-admin.js'), array('jquery'));

        $participants = get_post_meta($post->ID, ud_get_wp_maestro_conference('prefix') . 'participants', true);
        if( !is_array( $participants ) ) {
          $participants = array();
        }

        echo $this->get_template_part('list_participants', array('participants' => $participants));
      }

      /**
       * Hook on save conference, checking conference date
       * @param int $post_ID
       * @param object $post
       * @param boolean $update
       * @return type
       */
      static public function save_conference($post_ID, $post, $update = '') {
        if ($post->post_type == ud_get_wp_maestro_conference('conference_type') && $update == 1) {
          if (!empty($_POST['mc_scheduledStartDate'])) {
            //Conference check less and than 30 hours
            $nearest_conference = self::check_nearest_conferences_by_date($_POST['mc_scheduledStartDate'], $post_ID);
            if (!empty($nearest_conference->posts)) {
              self::return_error($post_ID);
            } else {
              $local_participants = array();
              for ($i = 0; $i < 24; $i++) {
                $local_participants[$i]['wp_user_id'] = '';
                $local_participants[$i]['name'] = __('Empty', ud_get_wp_maestro_conference('domain'));
              }
              add_post_meta($post_ID, ud_get_wp_maestro_conference('prefix') . 'participants', $local_participants, true);
              add_post_meta($post_ID, ud_get_wp_maestro_conference('prefix') . 'is_active', '0', true);
              return $post_ID;
            }
          } else {
            self::return_error($post_ID);
          }
        }
      }

      /**
       * Notice if wrong confrence date
       */
      static public function error_notice() {
        if (isset($_GET['message']) && $_GET['message'] == 'error_mc_date') {
          echo '<div class="updated error">' . __('Error conference date. Please, select other date.', ud_get_wp_maestro_conference('domain')) .
          __(' Post status set in draft.', ud_get_wp_maestro_conference('domain')) . ' </div>';
        }
      }

      /**
       *
       * @param int $post_ID Conference ID
       */
      static public function return_error($post_ID) {
        $location = add_query_arg('message', 'error_mc_date', get_edit_post_link($post_ID, 'url'));
        $conference = get_post($post_ID);
        if ($conference->post_status != 'draft') {
          wp_update_post(array('ID' => $post_ID, 'post_status' => 'draft'));
        }
        wp_redirect(apply_filters('redirect_post_location', $location, $post_ID));
        exit;
      }

    }

  }
}
