<?php
/**
 * Conference
 *
 * @class Conference
 */
namespace UsabilityDynamics\MaestroConference {
  
  if( !class_exists( 'UsabilityDynamics\MaestroConference\Conference' ) ) {

    class Conference extends Scaffold {

      public function __construct( $id, $args ) {
        parent::__construct( $id, $args );
        
      }
      
    }

  }
  
}