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
       * @return string
       */
      public function synchronize_conference() {
        $conference = self::get_nearest_conference();
        
        if ($conference->post) {
          $current_active_conference = self::get_current_active_conference();
          if ($current_active_conference->post) {
            update_post_meta($current_active_conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'is_active', '0');
            update_post_meta($current_active_conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'status', 'closed');
          }
          update_post_meta($conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'is_active', '1');

          try {
            $mc_conference = $this->instance->client->getConferenceData($this->instance->get('api.conference_uid'));
            if ($mc_conference['code'] != 0) {
              throw new \Exception(__($mc_conference['message'], ud_get_wp_maestro_conference('domain')));
            }
            if (isset($mc_conference['response']['value']['person'])) {
              //removing old callers from conference
              foreach ($mc_conference['response']['value']['person'] as $person) {
                if ($person['role'] == 'PARTICIPANT') {
                  $response = $this->instance->client->removePerson($this->instance->get('api.conference_uid'), $person['UID']);
                  if ($response['code'] != 0) {
                    throw new \Exception(__($response['message'], ud_get_wp_maestro_conference('domain')));
                  }
                }
              }
            }

            $local_participants = self::_get_local_participants($conference->post->ID);
            //addin new callers from our DB
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

            //if our callers < 24 - adding fake users
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

            //remove callers in our conference
            delete_post_meta($conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'participants');
            //adding updated callers to our DB
            add_post_meta($conference->post->ID, ud_get_wp_maestro_conference('prefix') . 'participants', serialize($persons), true);
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
            'post_type' => 'maestro_conference',
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
            'post_type' => 'maestro_conference',
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
            'post_type' => 'maestro_conference',
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
          $result = $this->instance->client->addPerson($this->instance->get('api.conference_uid'), $role, $user->user_nicename);
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
            'message' => 'User is not exist',
            'value' => array()
        );
        return $response;
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
      public function add_person_to_conference($wp_conference_id, $user_id = '', $role = 'PARTICIPANT') {
        $conference_status = get_post_meta($wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'status', true);
        $conference_is_active = get_post_meta($wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'is_active', true);
        try {
          if ($conference_status == 'active') {
            $user = get_userdata( $user_id);

            $user_meta = get_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_'.$wp_conference_id, true);
            if ($user_meta) {
              throw new \Exception(__('You have already registered in the conference', ud_get_wp_maestro_conference('domain')));
            }
            $user_can_take_part = true;
            $user_can_take_part = apply_filters('mc_user_can_take_part', $user_id);
            //if user not exist or user already has access or can't take part on this conference
            if ($user && $user_can_take_part) {
              $user = apply_filters('before_add_person_conference', $user);

              $local_participants = self::_get_local_participants($wp_conference_id);
              if (!empty($local_participants)) {
                foreach ($local_participants as $key => $local_participant) {
                  if (empty($local_participant['wp_user_id'])) {
                    if ($conference_is_active == '1') {
                      $result = $this->instance->client->updatePerson($local_participants[$key]['UID'], 'name', $user->data->display_name);
                      $response = $result['response'];
                      if ($response['code'] != 0) {
                        apply_filters('error_add_person_conference', $user);
                        throw new \Exception(__('Something wrong happened on trying to add person to conference.', ud_get_wp_maestro_conference('domain')));
                      }
                      $local_participants[$key] = $response['value'];
                      add_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_PIN_' . $wp_conference_id, $response['value']['PIN'], true);
                      add_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_phone_' . $wp_conference_id, $response['value']['callInNumber'], true);

                      Mail::add_to_conference( $user_id, array(
                        'subject' => '',
                        'message' => '',
                        'data' => array(
                          'conference_id' => $wp_conference_id,
                          'description' => '',
                        )
                      ) );
                    } else {                      
                      $local_participants[$key]['name'] = $user->user_nicename;
                    }
                    add_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference', $wp_conference_id);
                    add_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_'.$wp_conference_id, '1');
                    $local_participants[$key]['wp_user_id'] = $user_id;
                    update_post_meta($wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'participants', serialize($local_participants));
                    //add notification to user
                    do_action('after_add_person_conference', $user);
                    return $local_participants[$key];
                  }
                }
                return false;
              } else {
                throw new \Exception(__('Something wrong happened on trying to add person to conference.', ud_get_wp_maestro_conference('domain')));
              }
            } else {
              throw new \Exception(__('You can not take part on this conference', ud_get_wp_maestro_conference('domain')));
            }
          } else {
            throw new \Exception(__('Conference not active.', ud_get_wp_maestro_conference('domain')));
          }
        } catch (\Exception $e) {
          /* Add error notice */
          self::_add_conference_error_notice($user_id, $e->getMessage());

          return false;
        }
        return false;
      }

      /**
       *  Get local participants from conference
       *
       * @param int $wp_conference_id Wordpress conference ID.
       *
       *  @author PLAN
       */
      static private function _get_local_participants($wp_conference_id) {
        $result = get_post_meta($wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'participants', true);
        $local_participants = array();
        if ($result)
          $local_participants = unserialize($result);
        return $local_participants;
      }

      /**
       *  Add fake user to conference
       *
       * @param int $wp_conference_id Wordpress conference ID.
       *
       *  @author PLAN
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
       *  Remove person from conference
       *
       * @param int $wp_conference_id The ID of the conference.
       * @param array $user_id User ID.
       *
       *  @author PLAN
       */
      static public function remove_person_from_conference($wp_conference_id = null, $user_id = null) {
        $conference_is_active = get_post_meta($wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'is_active', true);
        try {
          if (!$conference_is_active) {
            $user = get_user_by("id", $user_id);
            $user_meta = get_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_'.$wp_conference_id);
            if (!$user_meta) {
              throw new \Exception(__('You are not registered in the conference', ud_get_wp_maestro_conference('domain')));
            }
            //if user exist and user already has access
            if ($user) {
              $user = apply_filters('before_remove_person_conference', $user);

              $local_participants = self::_get_local_participants($wp_conference_id);
              if (!empty($local_participants)) {
                foreach ($local_participants as $key => $local_participant) {
                  if ($local_participant['wp_user_id'] == $user_id) {
                    $local_participants[$key]['wp_user_id'] = '';
                    $local_participants[$key]['name'] = 'Empty';
                    update_post_meta($wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'participants', serialize($local_participants));
                    delete_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference', $wp_conference_id);
                    delete_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_'.$wp_conference_id, '1');
                    apply_filters('after_remove_person_conference', $user);
                    return true;
                  }
                }
              } else {
                throw new \Exception(__('Something wrong happened on trying to remove person on conference.', ud_get_wp_maestro_conference('domain')));
              }
            } else {
              throw new \Exception(__('You are not registered in the conference', ud_get_wp_maestro_conference('domain')));
            }
          } else {
            throw new \Exception(__('You can not give up the conference', ud_get_wp_maestro_conference('domain')));
          }
        } catch (\Exception $e) {
          /* Add error notice */
          self::_add_conference_error_notice($user_id, $e->getMessage());

          return false;
        }
        return false;
      }

      /*       * ************************************************
       * SHOW META BOX
       * ************************************************ */

      /**
       * Add meta box for multiple post types
       *
       * @return void
       */
      function add_meta_boxes() {
        add_meta_box(
                'mc_callers', 'Participants', array($this, 'show_metabox'), 'maestro_conference', 'normal', 'high'
        );
      }

      /**
       * Callback function to show fields in meta box
       *
       * @return void
       */
      function show_metabox() {
        wp_enqueue_script('mc-admin', ud_get_wp_maestro_conference()->path('static/scripts/mc-admin.js'), array('jquery'));
        global $post;
        $participants = array();
        $post_meta = get_post_meta($post->ID, ud_get_wp_maestro_conference('prefix') . 'participants', true);
        if ($post_meta) {
          $participants = unserialize($post_meta);
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
                $local_participants[$i]['name'] = 'Empty';
              }
              add_post_meta($post_ID, ud_get_wp_maestro_conference('prefix') . 'participants', serialize($local_participants), true);
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

      /**
       * Adds error notice on adding to conference failure for specific user.
       * Note, notices are being stored one minute.
       *
       * to show and flush notices for specific user just call:
       * ud_get_wp_maestro_conference()->get_conference_error_notices( 777 );
       *
       * Must not be called directly.
       */
      static private function _add_conference_error_notice($user_id, $message) {
        $transient = ud_get_wp_maestro_conference('prefix') . 'mc_err_notices_' . $user_id;
        if (empty($user_id) || empty($message)) {
          return false;
        }
        $value = get_transient($transient);
        if (!empty($value) && is_array($value)) {
          $value[$type] = $message;
        } else {
          $value = array($type => $message);
        }
        set_transient($transient, $value, MINUTE_IN_SECONDS);
      }

      /**
       * Returns error notices which are being added person on conference
       */
      static public function get_conference_error_notices($user_id = false, $flush = true) {
        $user_id ? !$user_id : get_current_user_id();
        $transient = ud_get_wp_maestro_conference('prefix') . 'mc_err_notices_' . $user_id;
        $value = get_transient($transient);
        if ($flush) {
          delete_transient($transient);
        }
        return $value;
      }

    }

  }
}
