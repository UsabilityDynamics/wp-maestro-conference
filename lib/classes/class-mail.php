<?php
/**
 * Mail Notifications Handler
 * 
 * @class Mail
 */
namespace UsabilityDynamics\MaestroConference {
  
  if( !class_exists( 'UsabilityDynamics\MaestroConference\Mail' ) ) {

    class Mail extends \UsabilityDynamics\Mail\WPMail {
      
      /**
       * Send notification to user on adding to conference.
       * 
       * 
       */
      static public function add_to_conference( $user_id, $args = array() ) {
        global $wp_post_statuses;

        $notification = self::notification_template();

        $user = get_user_by( 'id', $user_id );

        $notification[ 'trigger_action' ] = 'mc_added_person';
        $notification[ 'user' ] = $user;
        $notification[ 'subject' ] = __( 'You added to the conference', 'wpp' );
        $notification[ 'message' ] = sprintf( __( 'Hello.%1$s%1$sYou added to the conference.%1$s%1$s', ud_get_wp_maestro_conference( 'domain' ) ), PHP_EOL );
        $notification[ 'crm_log_message' ] = sprintf( __( 'User %1$s added to conference.', ud_get_wp_maestro_conference( 'domain' ) ), $user_id );

        $notification[ 'data' ][ 'display_name' ] = $user->data->display_name;
        $notification[ 'data' ][ 'user_email' ] = $user->data->user_email;
        $notification[ 'data' ][ 'site_url' ] = site_url();
        $notification[ 'data' ][ 'conference_title' ] = '';
        $notification[ 'data' ][ 'conference_id' ] = '';
        $notification[ 'data' ][ 'description' ] = '';

        $notification = apply_filters( 'mc_mail_added_to_conference', \UsabilityDynamics\Utility::array_merge_recursive_distinct( $notification, $args ) );

        self::send_notification( $notification );
      }
      
      /**
       * Send notification to user on removing from conference.
       * 
       * 
       */
      static public function remove_from_conference( $user_id, $args = array() ) {
        global $wp_post_statuses;

        $notification = self::notification_template();

        $user = get_user_by( 'id', $user_id );

        $notification[ 'trigger_action' ] = 'mc_removed_person';
        $notification[ 'user' ] = $user;
        $notification[ 'subject' ] = __( 'You removed from the conference', 'wpp' );
        $notification[ 'message' ] = sprintf( __( 'Hello.%1$s%1$sYou removed from the conference.%1$s%1$s', ud_get_wp_maestro_conference( 'domain' ) ), PHP_EOL );
        $notification[ 'crm_log_message' ] = sprintf( __( 'User %1$s removed from conference.', ud_get_wp_maestro_conference( 'domain' ) ), $user_id );

        $notification[ 'data' ][ 'display_name' ] = $user->data->display_name;
        $notification[ 'data' ][ 'user_email' ] = $user->data->user_email;
        $notification[ 'data' ][ 'site_url' ] = site_url();
        $notification[ 'data' ][ 'conference_title' ] = '';
        $notification[ 'data' ][ 'conference_id' ] = '';
        $notification[ 'data' ][ 'description' ] = '';

        $notification = apply_filters( 'mc_mail_removed_from_conference', \UsabilityDynamics\Utility::array_merge_recursive_distinct( $notification, $args ) );

        self::send_notification( $notification );
      }
      
    }

  }
  
}


