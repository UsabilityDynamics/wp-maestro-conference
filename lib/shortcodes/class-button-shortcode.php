<?php
/**
 * Shortcode: [mc_button]
 * Template: static/views/shortcodes/mc-button.php
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\MaestroConference {

  if( !class_exists( 'UsabilityDynamics\MaestroConference\Button_Shortcode' ) ) {

    class Button_Shortcode extends Shortcode {
      
      /**
       * Constructor
       */
      public function __construct() {
        
        $options = array(
          'id' => 'mc_button',
          'params' => array(
            'action' => array(
              'name' => __( 'Action', ud_get_wp_maestro_conference('domain') ),
              'enum' => array(
                'add',
                'remove'
              ),
              'description' => __( 'Required. Add or remove user from conference.', ud_get_wp_maestro_conference('domain') ),
            ),
            'conference_id' => array(
              'name' => __( 'Conference ID', ud_get_wp_maestro_conference('domain') ),
              'description' => __( 'Required. Conference ID which will be added or removed to user.', ud_get_wp_maestro_conference('domain') ),
            ),
            'label' => array(
              'name' => __( 'Label', ud_get_wp_maestro_conference('domain') ),
              'description' => __( 'Optional. Label for Button.', ud_get_wp_maestro_conference('domain') ),
            ),
            'send_mail' => array(
              'name' => __( 'Send Mail', ud_get_wp_maestro_conference('domain') ),
              'enum' => array(
                'true',
                'false',
              ),
              'default' => 'true',
              'description' => __( 'Optional. Send Email with Information about conference.', ud_get_wp_maestro_conference('domain') ),
            ),
            'extra' => array(
              'name' => __( 'Extra Arguments', ud_get_wp_maestro_conference('domain') ),
              'description' => __( 'Optional. Extra arguments which can be used on action process. Must be set in GET format. Example: data1=a&data2=b.', ud_get_wp_maestro_conference('domain') ),
            ),
            'callback' => array(
              'name' => __( 'Callback Function', ud_get_wp_maestro_conference('domain') ),
              'description' => __( 'Optional. Callback function which will be called on success action.', ud_get_wp_maestro_conference('domain') ),
            ),
            'redirect_to' => array(
              'name' => __( 'Redirect To URL', ud_get_wp_maestro_conference( 'domain' ) ),
              'description' => __( 'Optional. Redirects to specific url on success.', ud_get_wpi_wallet( 'domain' ) ),
            ),
            'template' => array(
              'name' => __( 'Template', ud_get_wp_maestro_conference('domain') ),
              'description' => __( 'Optional. If not set, default template\'s name is used.', ud_get_wp_maestro_conference('domain') ),
            ),
            'classes' => array(
              'name' => __( 'CSS Classes', ud_get_wp_maestro_conference( 'domain' ) ),
              'description' => __( 'Optional. Custom CSS classes.', ud_get_wp_maestro_conference( 'domain' ) ),
            ),
          ),
          'description' => __( 'Renders Button to add/withdraw credits from balance. User must be logged in.', ud_get_wp_maestro_conference('domain') ),
          'group' => 'Maestro Conference',
        );
        
        parent::__construct( $options );
        
        /* Hooks */
        add_action( 'wp_ajax_mc_button_action', array( $this, 'process_action' ) );
        
      }
      
      /**
       *  Renders Shortcode
       */
      public function call( $atts = "" ) {

        $data = shortcode_atts( array(
          'action' => '', // Required
          'conference_id' => '', // Required
          'label' => '', // Optional
          'hide_on_success' => 'false', // Optional
          'success_label' => __( 'Done', ud_get_wp_maestro_conference('domain') ), // Optional
          'send_mail' => 'true', // Optional
          'extra' => '', // Optional
          'redirect_to' => '', // Optional
          'callback' => '', // Optional
          'template' => str_replace( '_', '-', $this->id ), // Optional
          'classes' => '' // Optional
        ), $atts );
        
        /* Be sure that user is logged in */
        if( !is_user_logged_in() ) {
          do_action( 'mc_button_user_is_not_logged_in', $data );
          return false;
        }
        
        /* Check if action is valid */
        if( !in_array( $data[ 'action' ], array( 'add', 'remove' ) ) ) {
          return false;
        }

        /* Check conference ID. */
        if( empty( $data[ 'conference_id' ] ) || !is_numeric( $data[ 'conference_id' ] ) || $data[ 'conference_id' ] <= 0 ) {
          return false;
        }
        
        /* Prepare/Fix attributes values */
        if( !in_array( $data[ 'send_mail' ], array( 'true', 'false' ) ) ) {
          if( in_array( $data[ 'send_mail' ], array( 'no', 'off', '0' ) ) ) {
            $data[ 'send_mail' ] = 'false';
          } else {
            $data[ 'send_mail' ] = 'true';
          }
        }
        
        $data[ 'wpnonce' ] = $this->mc_generate_wpnonce();
        
        wp_enqueue_script( 'mc-button', ud_get_wp_maestro_conference()->path( 'static/scripts/mc-button.js' ), array( 'jquery' ) );
        wp_localize_script( 'mc-button', '_mc_button', array(
          'admin_ajax' => admin_url( 'admin-ajax.php' ),
        ) );
        
        $this->render( $data[ 'template' ], $data );
      }

      /**
       * Process Action
       * AJAX
       *
       * Available hooks:
       *
       * Manage arguments before proceed:
       * mc_button_process_action_params
       *
       * Do some custom stuff on success:
       * mc_button_on_success
       *
       */
      public function process_action() {
        global $wpdb;

        $request = $_REQUEST;

        /* Disable autocommit to Database to prevent broken balance transactions. */
        $wpdb->query( 'SET autocommit = 0;' );
        $wpdb->query( 'START TRANSACTION;' );

        try {
          /* User must be logged in */
          if( !is_user_logged_in() ) {
            throw new \Exception( __( 'Cheating?', ud_get_wp_maestro_conference( 'domain' ) ) );
          }
          /* Check wpnonce */
          if( empty( $request[ 'wpnonce' ] ) || !$this->is_valid_wpnonce( $request[ 'wpnonce' ] ) ) {
            throw new \Exception( __( 'Cheating?', ud_get_wp_maestro_conference('domain') ) );
          }

          /* Check if type ( action ) is valid */
          if( !in_array( $request[ 'type' ], array( 'add', 'remove' ) ) ) {
            throw new \Exception( __( 'Invalid Action. Ask site administrator for help.', ud_get_wp_maestro_conference('domain') ) );
          }


          /* Prepare extra data if it exists */
          $request[ 'extra' ] = wp_parse_args( str_replace( '&amp;', '&', urldecode( $request[ 'extra' ] ) ) );

          $request = apply_filters( 'mc_button_process_action_params', $request );

          /* Maybe do some custom stuff here */
          $custom_stuff = apply_filters( 'mc_button_before_process', true, $request );

          if( is_wp_error( $custom_stuff ) ) {
            throw new \Exception( $custom_stuff->get_error_message );
          } else if ( !$custom_stuff ) {
            throw new \Exception( __( 'There is a some error. Please, notify site administrator about this issue, if it will happen again.', ud_get_wpi_wallet( 'domain' ) )  );
          }

          /* Prepare arguments for add or cancel registration */
          $params = array_filter( array(
            'send_mail' => ( !empty( $request[ 'send_mail' ] ) ? $request[ 'send_mail' ] : 'false' ),
            'wp_conference_id' => $request[ 'conference_id' ],
            'force_autocommit' => true,
          ) );

          /*
           * Here we go now!
           *
           * Note!
           * We must be careful here since we deal with remote service.
           * So if something will go wrong we may not be able to revert changes on Maestro Conference.
           * It means, we must not throw new Exception manually ( some custom stuff )
           * after add_person_to_conference / remove_person_from_conference
           */
          if( $request[ 'type' ] == 'add' ) {
            $r = ud_get_wp_maestro_conference()->add_person_to_conference( $params );
          }
          else if ( $request[ 'type' ] == 'remove' ) {
            $r = ud_get_wp_maestro_conference()->remove_person_from_conference( $params );
          }

          /* WHAT? Looks like there is an error on trying to operate with registration. Get error. */
          if( !$r || is_wp_error( $r ) ) {
            $errors = ud_get_wp_maestro_conference()->get_conference_error_notices();
            if( !empty( $errors ) && is_array( $errors ) ) {
              end( $errors );
              $key = key( $errors );
              if( !empty( $errors[ $key ] ) ) {
                throw new \Exception( $errors[ $key ] );
              }
            }
            throw new \Exception( __( 'There is an error on trying to add/cancel conference. Please try later.', ud_get_wpi_wallet( 'domain' ) )  );
          }

        } catch ( \Exception $e ) {

          /* Rollback all transactions to prevent broken stuff. */
          $wpdb->query( 'ROLLBACK' );
          $wpdb->query( 'SET autocommit = 1;' );

          wp_send_json( array(
            'success' => false,
            'message' => '',
            'error' => $e->getMessage(),
          ) );

        }

        /* Commit all transactions to Database and enable autocommit again. */
        $wpdb->query( 'COMMIT' );
        $wpdb->query( 'SET autocommit = 1;' );


        /* Run callback if it set */
        if( !empty( $request[ 'callback' ] ) && is_callable( $request[ 'callback' ] ) ) {
          @call_user_func( $request[ 'callback' ], $request );
        }

        wp_send_json( array(
          'success' => true,
          'message' => '',
          'error' => '',
          'redirect_to' => ( !empty( $request[ 'redirect_to' ] ) ? urldecode( $request[ 'redirect_to' ] ) : false )
        ) );
      }
      
      /**
       * Simple HASH generator
       * 
       * MUST NOT BE USED DIRECTLY
       */
      private function mc_generate_wpnonce() {
        $transient = $this->ID . '_' . get_current_user_id();
        $hash = md5( rand( 1, 999999 ) );
        $value = get_transient( $transient );
        if( !empty( $value ) && is_array( $value ) ) {
          $value[] = $hash;
        } else {
          $value = array( $hash );
        }

        set_transient( $transient, $value, 30 * MINUTE_IN_SECONDS );
        return $hash;
      }
      
      /**
       * Determine if wpnonce hash is valid for current user
       * 
       * MUST NOT BE USED DIRECTLY
       */
      private function is_valid_wpnonce( $hash ) {
        /* Cheating? Hah? */
        if( !is_user_logged_in() || empty( $hash ) ) {
          return false;
        }
        $transient = $this->ID . '_' . get_current_user_id();
        $value = get_transient( $transient );
        if( empty( $value ) || !is_array( $value )  ) {
          return false;
        }        
        
        foreach( $value as $k => $v ) {
          if( $v == $hash ) {
            unset( $value[ $k ] );
            set_transient( $transient, $value, 30 * MINUTE_IN_SECONDS );
            return true;
          }
        }
        return false;
      }
      
    }
    
    new Button_Shortcode();

  }

}

