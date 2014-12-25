<?php

/**
 * Helper Functions List
 *
 * @class Utility
 */

namespace UsabilityDynamics\MaestroConference {

  if (!class_exists('UsabilityDynamics\MaestroConference\Client')) {

    class Client extends \UsabilityDynamics\MC\Client {

      /**
       *
       * @var object
       */
      public $instance;

      /**
       * Constructor
       *
       * @author peshkov@UD
       */
      public function __construct($customer, $auth_key) {
        parent::__construct($customer, $auth_key);
        //** Get our Bootstrap Singleton object */
        $this->instance = ud_get_wp_maestro_conference();
      }

    }

  }
}