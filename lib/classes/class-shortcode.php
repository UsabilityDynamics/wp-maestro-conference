<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\MaestroConference {

  if (!class_exists('UsabilityDynamics\MaestroConference\Shortcode')) {

    class Shortcode extends \UsabilityDynamics\Shortcode\Shortcode {

      /**
       * Determines template and renders it
       * 
       * 
       */
      public function render( $vars, $template, $output = true ) {
        $name = apply_filters( $this->id . '_template_name', array( $template ), $this );
        /* Set possible pathes where templates could be stored. */
        $path = apply_filters( $this->id . '_template_path', array(
          ud_get_wp_maestro_conference()->path( 'static/views/shortcodes', 'dir' ),
        ) ); 
        $path = \UsabilityDynamics\Utility::get_template_part( $name, $path, array(
          'load' => false
        ) );
        if( $output ) {
          extract( $vars );
          include $path;
        } else {
          return $path;
        }
      }

    }

  }
}
