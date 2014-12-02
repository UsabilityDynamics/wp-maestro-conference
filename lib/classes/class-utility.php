<?php
/**
 * Helper Functions List
 *
 * @class Utility
 */
namespace UsabilityDynamics\MaestroConference {
  
  if( !class_exists( 'UsabilityDynamics\MaestroConference\Utility' ) ) {

    class Utility {

      /**
       * Get Conference object
       *
       * @return object
       */
      static public function get_conference( $the_conference = false, $args = array() ) {
        return Conference_Factory::get_conference( $the_conference, $args );
      }
      
    }

  }
  
}