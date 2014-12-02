<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\MaestroConference {

  if( !class_exists( 'UsabilityDynamics\MaestroConference\Bootstrap' ) ) {

    final class Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Core
       *
       * @var object \UsabilityDynamics\MaestroConference\Core
       */
      public $core = NULL;
      
      /**
       * API Client
       *
       * @var object \UsabilityDynamics\MaestroConference\Client
       */
      public $client = NULL;
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WP_MC\Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {
        
        $this->define_settings();
        
        $this->core = new Core();
        
        /**
         * Init API CLient
         */
        $customer = $this->get( 'api.customer' );
        $auth_key = $this->get( 'api.auth_key' );
        if( !empty( $customer ) && !empty( $auth_key ) ) {
          $this->client = new Client( $customer, $auth_key );
        }
        
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {}
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {}
      
      /**
       * Define Plugin Settings
       * 
       * Examples:
       * 
       * to get text domain:
       * $this->get( 'domain' );
       * 
       * to get Maestro Conference customer ID:
       * $this->get( 'api.customer' );
       * 
       * to set Maestro Conference customer ID:
       * $this->set( 'api.customer', 'johndoe' );
       */
      private function define_settings() {
        $this->settings = new \UsabilityDynamics\Settings( array(
          'key'  => 'maconf_settings',
          'store'  => 'options',
          'data' => array(
            'name' => $this->name,
            'version' => $this->args[ 'version' ],
            'domain' => $this->domain,
          )
        ) );
        
        /* Probably add default settings */
        $default = $this->get_schema( 'extra.schemas.settings' );
        if( is_array( $default ) ) {
          $this->set( \UsabilityDynamics\Utility::extend( $default, $this->get() ) );  
        }
        
      }
      
      /**
       * Determine if Utility class contains missed function
       * in other case, just return NULL to prevent ERRORS
       * 
       * @author peshkov@UD
       */
      public function __call( $name, $arguments ) {
        if( is_callable( array( "\UsabilityDynamics\MaestroConference\Utility", $name ) ) ) {
          return call_user_func_array( array( "\UsabilityDynamics\MaestroConference\Utility", $name ), $arguments );
        } else {
          return NULL;
        }
      }

    }

  }

}
