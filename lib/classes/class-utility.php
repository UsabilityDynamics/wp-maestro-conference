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
        $result = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'participants', true);
        $local_participants = array();
        $count_participants = 0;
        if ($result) {
          $local_participants = unserialize($result);
          foreach ($local_participants as $local_participant) {
            if (!empty($local_participant['wp_user_id'])) {
              $count_participants++;
            }
          }
        }

        $post['count_participants'] = $count_participants;
        $post['scheduledStartDate'] = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'scheduledStartDate', true);
        $post['is_active'] = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'is_active', true);
        $post['status'] = get_post_meta($post_id, ud_get_wp_maestro_conference('prefix') . 'status', true);
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
      static public function get_user_conference_data($post_id) {
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

    }

  }
}