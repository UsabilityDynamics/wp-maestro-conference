<?php
/**
 * WP Mail Handler
 *
 * @since 0.1.0
 */
namespace UsabilityDynamics\Mail {

  if( !class_exists( 'UsabilityDynamics\Mail\WPMail' ) ) {

    class WPMail  {
      
      /**
       * Wrapper function to send notification with WP-CRM or without one
       *
       * @uses self::replace_data()
       * @uses wp_crm_send_notification()
       * @return boolean false if notification was not sent successfully
       */
      static public function send_notification( $args ) {
        
        $args = apply_filters( 'ud_wpmail_send_notification', wp_parse_args( $args, array(
          'ignore_wp_crm'   => false,
          'user'            => false,
          'trigger_action'  => false,
          'data'            => array(),
          'message'         => '',
          'subject'         => '',
          'crm_log_message' => ''
        ) ) );

        if( is_numeric( $args[ 'user' ] ) ) {
          $args[ 'user' ] = get_user_by( 'id', $args[ 'user' ] );
        } elseif( filter_var( $args[ 'user' ], FILTER_VALIDATE_EMAIL ) ) {
          $args[ 'user' ] = get_user_by( 'email', $args[ 'user' ] );
        } elseif( is_string( $args[ 'user' ] ) ) {
          $args[ 'user' ] = get_user_by( 'login', $args[ 'user' ] );
        }

        if( !is_object( $args[ 'user' ] ) || empty( $args[ 'user' ]->data->user_email ) ) {
          return false;
        }

        /* Maybe send notification via WP-CRM plugin */
        if( function_exists( 'wp_crm_send_notification' ) && empty( $args[ 'ignore_wp_crm' ] ) ) {
          if( !empty( $args[ 'crm_log_message' ] ) ) {
            wp_crm_add_to_user_log( $args[ 'user' ]->ID, self::replace_data( $args[ 'crm_log_message' ], $args[ 'data' ] ) );
          }
          if ( !empty( $args[ 'trigger_action' ] ) && is_callable( 'WP_CRM_N', 'get_trigger_action_notification' ) ) {
            $notifications = \WP_CRM_N::get_trigger_action_notification( $args[ 'trigger_action' ] );
            if ( !empty( $notifications ) ) {
              return wp_crm_send_notification( $args[ 'trigger_action' ], $args[ 'data' ] );
            }
          }
        }

        if( empty( $args[ 'message' ] ) ) {
          return false;
        }

        return wp_mail( $args[ 'user' ]->data->user_email, self::replace_data( $args[ 'subject' ], $args[ 'data' ] ), self::replace_data( $args[ 'message' ], $args[ 'data' ] ) );
      }
      
      /**
       * Replace in $str all entries of keys of the given $values
       * where each key will be rounded by $brackets['left'] and $brackets['right']
       * with the relevant values of the $values
       *
       * @param string|array $str
       * @param array        $values
       * @param array        $brackets
       *
       * @return string|array
       */
      static public function replace_data( $str = '', $values = array(), $brackets = array( 'left' => '[', 'right' => ']' ) ) {
        $values       = (array) $values;
        $replacements = array_keys( $values );
        array_walk( $replacements, create_function( '&$val', '$val = "' . $brackets[ 'left' ] . '".$val."' . $brackets[ 'right' ] . '";' ) );
        return str_replace( $replacements, array_values( $values ), $str );
      }
      
      /**
       * Returns default notification arguments
       */
      static public function notification_template() {
        return apply_filters( 'ud_wpmail_notification_template', array(
          'trigger_action' => 'wpiw_default_action',
          'data' => array(),
          'user' => false,
          'subject' => __( 'No Subject' ),
          'message' => '',
          'crm_log_message' => '',
        ) );
      }
      
    }

  }

}
