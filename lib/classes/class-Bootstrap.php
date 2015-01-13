<?php

/**
 * Bootstrap
 *
 * @since 1.0.0
 */

namespace UsabilityDynamics\MaestroConference {

  if (!class_exists('UsabilityDynamics\MaestroConference\Bootstrap')) {

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

        /**
         * Init API CLient
         */
        $customer = $this->get('api.customer');
        $auth_key = $this->get('api.auth_key');
        $conference_uid = $this->get('api.conference_uid');
        if (!empty($customer) && !empty($auth_key) && !empty( $conference_uid ) ) {
          $this->client = new Client($customer, $auth_key);
        }
        $this->core = new Core();
      }

      /**
       * Return localization's list.
       *
       * Example:
       * If schema contains l10n.{key} values:
       *
       * { 'config': 'l10n.hello_world' }
       *
       * the current function should return something below:
       *
       * return array(
       *   'hello_world' => __( 'Hello World', $this->domain ),
       * );
       *
       * @author PLAN
       * @return array
       */
      public function get_localization() {
        return apply_filters('wp_maestro_conference_localization', array(
            'maestro_conference_page_title' => __('Maestro Conferences Settings', $this->domain),
            'maestro_conference_settings' => __('Settings', $this->domain),
            'mc_api_credentials' => __('Maestro Conference API Credentials', $this->domain),
            'mc_api' => __('API', $this->domain),
            'mc_meta_fields' => __('Meta Fields', $this->domain),
            'mc_meta_fields_availability' => __('Meta Fields Availability', $this->domain),
            'mc_conference' => __('Conference', $this->domain),
            'mc_conferences' => __('Conferences', $this->domain),
            'mc_general_pt_setting' => __('General Conference Post Type Settings', $this->domain),
            'mc_credentials' => __('Credentials', $this->domain),
            'mc_registration' => __("Registration", $this->domain),
            'mc_pre_registration' => __('Pre-registration', $this->domain),
            'mc_customer_uid' => __('Customer UID', $this->domain),
            'mc_security_token' => __('Security Token', $this->domain),
            'mc_conference_unique_id' => __('Conference unique ID', $this->domain)
                ));
      }

      /**
       * Plugin Activation
       *
       */
      public function activate() {

      }

      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {

      }

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
        $this->settings = new \UsabilityDynamics\Settings(array(
            'key' => 'maconf_settings',
            'store' => 'options',
            'data' => array(
                'name' => $this->name,
                'version' => $this->args['version'],
                'domain' => $this->domain,
                'prefix' => 'mc_',
                'conference_type' => 'maestro_conference',
            )
                ));

        /* Probably add default settings */
        $default = $this->get_schema('extra.schemas.settings');
        if (is_array($default)) {
          $this->set(\UsabilityDynamics\Utility::extend($default, $this->get()));
        }
      }

      /**
       * Determine if Utility class contains missed function
       * in other case, just return NULL to prevent ERRORS
       *
       * @author peshkov@UD
       */
      public function __call($name, $arguments) {
        if (is_callable(array("\UsabilityDynamics\MaestroConference\Utility", $name))) {
          return call_user_func_array(array("\UsabilityDynamics\MaestroConference\Utility", $name), $arguments);
        } else {
          return NULL;
        }
      }

    }

  }
}
