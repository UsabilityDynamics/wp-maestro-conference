<?php
/**
 * Conference Factory Class
 *
 * @class Conference_Factory
 */
namespace UsabilityDynamics\MaestroConference {
  
  if( !class_exists( 'UsabilityDynamics\MaestroConference\Conference_Factory' ) ) {

    class Conference_Factory {

      /**
       * get_conference function.
       *
       * @access public
       * @param bool $the_conference (default: false)
       * @param array $args (default: array())
       * @return object
       */
      static public function get_conference( $the_conference = false, $args = array() ) {
        global $post;
        
        if ( false === $the_conference ) {
          $the_conference = $post;
        } elseif ( is_numeric( $the_conference ) ) {
          $the_conference = get_post( $the_conference );
        }

        if ( ! $the_conference ) {
          return false;
        }

        if ( is_object ( $the_conference ) ) {
          $conference_id = absint( $the_conference->ID );
        }

        $classname = false;
        $status = get_post_meta( $conference_id, 'status', true );
        if( !empty( $status ) ) {
          $classname = '\UsabilityDynamics\MaestroConference\Conference_' . $status;
        }

        /* Filter classname so that the class can be overridden if extended. */
        $classname = apply_filters( 'woocommerce_product_class', $classname, $status, $product_id );

        if ( !$classname || !class_exists( $classname ) ) {
          $classname = '\UsabilityDynamics\MaestroConference\Conference';
        }
        
        return new $classname( $the_conference, $args );
      }
      
    }

  }
  
}