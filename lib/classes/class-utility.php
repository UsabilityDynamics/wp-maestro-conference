<?php

/**
 * Helper Functions List
 *
 * @class Utility
 */

namespace UsabilityDynamics\MaestroConference {

  if (!class_exists('UsabilityDynamics\MaestroConference\Utility')) {

    class Utility {

      /**
       * Get Conference object
       *
       * @return object
       */
      static public function get_conference($the_conference = false, $args = array()) {
        return Conference_Factory::get_conference($the_conference, $args);
      }

      /*
       * Change value 0/1 to false/true for maestroconference API
       */

      static public function change_checkbox_value($value) {
        if ($value == 1) {
          return 'true';
        } elseif ($value == 0) {
          return 'false';
        } else {
          return '';
        }
      }

      /**
       * Returns the list of Conferences
       *
       * Available $args params:
       * - posts_per_page
       * - offset
       * - paid
       * - user_id
       * - status
       */
      static public function get_conferences($args = array()) {
        /* Default query settings */
        $query = array(
            'posts_per_page' => ( isset($args['posts_per_page']) ? $args['posts_per_page'] : 25 ),
            'offset' => ( isset($args['offset']) ? $args['offset'] : 0 ),
            'paged' => ( isset($args['paged']) ? $args['paged'] : 0 ),
            'meta_query' => array(),
        );

        /* Post type must not be changed */
        $query['post_type'] = ud_get_wp_maestro_conference('conference_type');

        /* Maybe filter conference by status */
        if (!empty($args['status']) && in_array($args['status'], array('active', 'closed'))) {
          $query['meta_query'][] = array(
              'key' => ( ud_get_wp_maestro_conference('prefix') . 'status' ),
              'value' => $args['status'],
          );
        }
        /* Maybe filter conference by user */
        if (!empty($args['user_id'])) {
          $post_ids = get_user_meta($args['user_id'], ud_get_wp_maestro_conference('prefix') . 'conference');
          if(!empty($post_ids))
            $query['post__in'] = $post_ids;
          else
            return array();
        }
        $query = apply_filters('pre_get_conferences', $query, $args);
        $conference =  new \WP_Query($query);
        return $conference;
      }
      
      /**
       * Returns Conference by conference ID
       *
       * Available $args params:
       * conference_id
       */
      static public function get_conference_by_id($args = array()) {
        if (empty($args['conference_id'])) {
          return false;
        }
        /* Default query settings */
        $query = array(
            'posts_per_page' => 1,
            'offset' => 0,
            'p' => $args['conference_id'],
            'meta_query' => array(),
            'post_type' => ud_get_wp_maestro_conference('conference_type')
        );

        $conference =  new \WP_Query($query);
        return $conference;
      }

      /**
       * Returns Conference data object
       *
       * @param type $post_id
       */
      static public function get_conference_data($post_id, $output = ARRAY_A) {
        $post = get_post($post_id, ARRAY_A);
        if ($post['post_type'] !== ud_get_wp_maestro_conference('conference_type')) {
          return false;
        }
        /* Add postmeta to result */
        $local_participants = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'participants', true);
        $count_participants = 0;
        if ($local_participants) {
          foreach ($local_participants as $local_participant) {
            if (!empty($local_participant['wp_user_id'])) {
              $participants[] = get_user_by("id", $local_participant['wp_user_id']);
              $count_participants++;
            }
          }
        }

        $post['count_participants'] = $count_participants;
        $post['scheduledStartDate'] = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'scheduledStartDate', true);
        $post['is_active'] = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'is_active', true);
        $post['status'] = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'status', true);
        $post['participants'] = $participants;
        /* Return */
        if ($output == OBJECT) {
          return (object) $post;
        }
        return $post;
      }
      
      /**
       * Returns conference metadata for current user, if registered on this conference
       *
       * @param type $post_id
       */
      static public function get_user_conference_data($post_id = false) {
        if (!$post_id) {
          return false;
        }
        $post = get_post($post_id, ARRAY_A);
        if ($post['post_type'] !== ud_get_wp_maestro_conference('conference_type')) {
          return false;
        }
        /* Add usermeta to result */
        $result['is_registered'] = get_user_meta(get_current_user_id(), ud_get_wp_maestro_conference('prefix') . 'conference_'.$post_id, true);
        $result['PIN'] = get_user_meta(get_current_user_id(), ud_get_wp_maestro_conference('prefix') . 'conference_PIN_'.$post_id, true);
        $result['phone'] = get_user_meta(get_current_user_id(), ud_get_wp_maestro_conference('prefix') . 'conference_phone_'.$post_id, true);
        return (object) $result;
      }
      

      /**
       * Returns PIN for passed user ( or for logged in user if param is not passed )
       */
      static public function get_pin($user_id = false, $conference_id = false) {
        $user_id = $user_id ? $user_id : get_current_user_id();
        if (!$user_id || !$conference_id) {
          return false;
        }
        $pin = get_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_PIN_' . $conference_id, true);
        return $pin;
      }

      /**
       * Returns error notices which are being added person on conference
       */
      static public function get_conference_error_notices($user_id = false, $flush = true) {
        $user_id = $user_id ? !$user_id : get_current_user_id();
        $transient = ud_get_wp_maestro_conference('prefix') . 'mc_err_notices_' . $user_id;
        $value = get_transient($transient);
        if ($flush) {
          delete_transient($transient);
        }
        return $value;
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
      static public function add_conference_error_notice($user_id, $message) {
        $transient = ud_get_wp_maestro_conference('prefix') . 'mc_err_notices_' . $user_id;

        if (empty($user_id) || empty($message)) {
          return false;
        }
        $value = get_transient($transient);
        if (!empty($value) && is_array($value)) {
          $value[] = $message;
        } else {
          $value = array( $message );
        }
        set_transient($transient, $value, MINUTE_IN_SECONDS);
      }

      /**
       * Add person to conference
       *
       * @param array $args Params.
       * @return bool
       */
      public function add_person_to_conference( $args ) {
        global $wpdb;

        $args = wp_parse_args( $args, array(
          'user_id' => get_current_user_id(),
          'wp_conference_id' => false, // Required
          'send_mail' => 'false',
          'force_autocommit' => false,
        ) );
        extract( $args );

        if( !$force_autocommit ) {
          /* Disable autocommit to Database to prevent broken balance transactions. */
          $wpdb->query( 'SET autocommit = 0;' );
          $wpdb->query( 'START TRANSACTION;' );
        }

        try {

          $conference_status = get_post_meta( $wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'status', true );
          /* Conference must be active */
          if ( $conference_status !== 'active' ) {
            throw new \Exception(__('Conference not active.', ud_get_wp_maestro_conference('domain')));
          }
          /* Check user */
          $user = get_userdata( $user_id );
          if( !$user ) {
            throw new \Exception(__('User does not exist', ud_get_wp_maestro_conference('domain')));
          }
          /* User must not be registered before */
          $is_registered = get_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_'.$wp_conference_id, true);
          if ( $is_registered ) {
            throw new \Exception(__('You have already registered in the conference', ud_get_wp_maestro_conference('domain')));
          }

          /* Custom filter */
          $user_can_take_part = apply_filters( 'mc_user_can_take_part', true, $user_id );
          if ( $user_can_take_part === false ) {
            throw new \Exception(__('You can not take part on this conference', ud_get_wp_maestro_conference('domain')));
          } elseif( is_wp_error( $user_can_take_part ) ) {
            throw new \Exception( $user_can_take_part->get_error_message );
          }

          $local_participants = self::get_local_participants( $wp_conference_id );
          if( empty( $local_participants ) ) {
            throw new \Exception(__('Something wrong happened on trying to add person to conference.', ud_get_wp_maestro_conference('domain')));
          }

          $has_vacation = false;
          $conference_is_active = get_post_meta( $wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'is_active', true );

          foreach ( $local_participants as $key => $local_participant ) {

            if ( empty( $local_participant['wp_user_id'] ) ) {
              $has_vacation = true;

              if ($conference_is_active == '1') {

                $result = ud_get_wp_maestro_conference()->client->updatePerson( $local_participants[$key]['UID'], 'name', $user->data->display_name );
                $response = $result['response'];
                if ($response['code'] != 0) {
                  throw new \Exception(__('There is an error on doing request to Maestro Conference.', ud_get_wp_maestro_conference('domain')));
                }

                $local_participants[$key] = $response['value'];
                add_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_PIN_' . $wp_conference_id, $response['value']['PIN'], true);
                add_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_phone_' . $wp_conference_id, $response['value']['callInNumber'], true);

              } else {

                $local_participants[$key]['name'] = $user->display_name;

              }

              add_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference', $wp_conference_id);
              add_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_'.$wp_conference_id, time() );
              $local_participants[$key]['wp_user_id'] = $user_id;
              update_post_meta($wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'participants', $local_participants);

              break;
            }

          }

          if( !$has_vacation ) {
            throw new \Exception(__('There is no free participant\'s vacation.', ud_get_wp_maestro_conference('domain')));
          }

          do_action('after_add_person_conference', $user);

        } catch ( \Exception $e ) {

          if( !$force_autocommit ) {
            /* Rollback all transactions to prevent broken orders, order items, etc. */
            $wpdb->query( 'ROLLBACK' );
            $wpdb->query( 'SET autocommit = 1;' );
          }

          /* Add error notice */
          self::add_conference_error_notice( $user_id, $e->getMessage() );

          return false;

        }

        if( !$force_autocommit ) {
          /* Commit all transactions to Database and enable autocommit again. */
          $wpdb->query( 'COMMIT' );
          $wpdb->query( 'SET autocommit = 1;' );
        }

        /* And Finally, send email if needed */
        if( in_array( $send_mail, array( 'true', 'yes', 'on', '1' ) ) ) {
          Mail::add_to_conference( $user_id, array(
            'data' => array(
              'conference_id' => $wp_conference_id
            )
          ) );
        }

        return true;

      }

      /**
       * Remove person from conference
       *
       * @param int $wp_conference_id The ID of the conference.
       * @param array $user_id User ID.
       * @return bool
       */
      static public function remove_person_from_conference( $args ) {
        global $wpdb;

        $args = wp_parse_args( $args, array(
          'user_id' => get_current_user_id(),
          'wp_conference_id' => false, // Required
          'send_mail' => 'false',
          'force_autocommit' => false,
        ) );
        extract( $args );

        if( !$force_autocommit ) {
          /* Disable autocommit to Database to prevent broken balance transactions. */
          $wpdb->query( 'SET autocommit = 0;' );
          $wpdb->query( 'START TRANSACTION;' );
        }

        try {

          /* If conference is already active, user can not cancel it. */
          $conference_is_active = get_post_meta( $wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'is_active', true );
          if ( $conference_is_active ) {
            throw new \Exception(__('You can not cancel the conference now.', ud_get_wp_maestro_conference('domain')));
          }

          // @todo: if conference is already finished ( closed ),we also should bail here.

          /* Check user */
          $user = get_userdata( $user_id );
          if( !$user ) {
            throw new \Exception(__('User does not exist', ud_get_wp_maestro_conference('domain')));
          }
          /* User must not be registered before */
          $is_registered = get_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_'.$wp_conference_id, true );
          if ( !$is_registered ) {
            throw new \Exception(__('You are not registered in the conference', ud_get_wp_maestro_conference('domain')));
          }

          $local_participants = self::get_local_participants($wp_conference_id);
          if (empty($local_participants)) {
            throw new \Exception(__('Something wrong happened on trying to remove person on conference.', ud_get_wp_maestro_conference('domain')));
          }

          foreach ($local_participants as $key => $local_participant) {

            if ($local_participant['wp_user_id'] == $user_id) {
              $local_participants[$key]['wp_user_id'] = '';
              $local_participants[$key]['name'] = 'Empty';
              update_post_meta($wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'participants', $local_participants);
              delete_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference', $wp_conference_id);
              delete_user_meta($user_id, ud_get_wp_maestro_conference('prefix') . 'conference_'.$wp_conference_id );

            }

          }

        } catch ( \Exception $e ) {

          if( !$force_autocommit ) {
            /* Rollback all transactions to prevent broken orders, order items, etc. */
            $wpdb->query( 'ROLLBACK' );
            $wpdb->query( 'SET autocommit = 1;' );
          }

          /* Add error notice */
          self::add_conference_error_notice( $user_id, $e->getMessage() );

          return false;

        }

        if( !$force_autocommit ) {
          /* Commit all transactions to Database and enable autocommit again. */
          $wpdb->query( 'COMMIT' );
          $wpdb->query( 'SET autocommit = 1;' );
        }

        /* And Finally, send email if needed */
        if( in_array( $send_mail, array( 'true', 'yes', 'on', '1' ) ) ) {
          Mail::remove_from_conference( $user_id, array(
            'data' => array(
              'conference_id' => $wp_conference_id
            )
          ) );
        }

        return true;

      }

      /**
       *  Get local participants from conference
       *
       * @param int $wp_conference_id Wordpress conference ID.
       * @return array
       */
      static public function get_local_participants( $wp_conference_id ) {
        $local_participants = get_post_meta($wp_conference_id, ud_get_wp_maestro_conference('prefix') . 'participants', true);
        if( !is_array( $local_participants ) ) {
          $local_participants = array();
        }
        return $local_participants;
      }

      /**
       *
       * @todo: finish method.
       */
      static public function has_available_callers( $wp_conference_id ) {

        return true;
      }

    }

  }
}