<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\MaestroConference {

  if( !class_exists( 'UsabilityDynamics\MaestroConference\Core' ) ) {

    final class Core extends Scaffold {
      
      /**
       * 
       */
      public function __construct() {
        parent::__construct();
        
        /* Register Custom Post Types, meta and set their taxonomies */
        $schema = $this->instance->get_schema( 'extra.schemas.model' );
        
        if( !empty( $schema ) && is_array( $schema ) ) {
          \UsabilityDynamics\Model::define( $schema );
        }
        
        /* Setup setting for plugin on admin panel */
        $this->ui = new UI();
        
      }
      
      

    }

  }

}
