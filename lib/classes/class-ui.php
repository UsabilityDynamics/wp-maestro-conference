<?php
/**
 * UI
 *
 * @author UsabilityDynamics, inc
 */
namespace UsabilityDynamics\MaestroConference {

  use UsabilityDynamics\Model\Post;

  if( !class_exists( 'UsabilityDynamics\MaestroConference\UI' ) ) {

    /**
     *
     *
     * @author UsabilityDynamics, inc
     */
    class UI extends Scaffold {
      
      /**
       * Constructor
       *
       * @author peshkov@UD
       */
      public function __construct() {
        parent::__construct();
        
        /* Setup Admin Interface */
        new \UsabilityDynamics\UI\Settings( $this->instance->settings, $this->instance->get_schema( 'extra.schemas.ui' ) );
        
        /**
         * @see vendor/usabilitydynamics/lib-ui/static/templates/admin/main.php
         */
        add_action( 'ud:ui:settings:view:main:top', array( $this, 'custom_ui' ) );
        // Adds Tab Item ( Link )
        add_action( 'ud:ui:settings:view:tab_link', array( $this, 'custom_ui' ) );
        // Adds Panel for Tab
        add_action( 'ud:ui:settings:view:tab_container', array( $this, 'custom_ui' ) );
        add_action( 'ud:ui:settings:view:main:bottom', array( $this, 'custom_ui' ) );
        // Adds Custom Actions ( e.g. Synchronize with Maestro Conference )
        add_action( 'ud:ui:settings:view:main:actions', array( $this, 'custom_ui' ) );
        
        /**
         * @see vendor/usabilitydynamics/lib-ui/static/templates/admin/tab.php
         */
        //
        add_action( 'ud:ui:settings:view:tab:api:top', array( $this, 'custom_ui' ) );
        add_action( 'ud:ui:settings:view:tab:api:bottom', array( $this, 'custom_ui' ) );
        
        /**
         * @see vendor/usabilitydynamics/lib-ui/static/templates/admin/section.php
         */
        add_action( 'ud:ui:settings:view:section:credentials:top', array( $this, 'custom_ui' ) );
        add_action( 'ud:ui:settings:view:section:sync:top', array( $this, 'custom_ui' ) );
        add_action( 'ud:ui:settings:view:section:credentials:bottom', array( $this, 'custom_ui' ) );
        add_action( 'ud:ui:settings:view:section:sync:bottom', array( $this, 'custom_ui' ) );
        
      }
      
      /**
       * 
       */
      public function custom_ui() {
        
        $hook = current_filter();
        
        switch( $hook ) {
          
          case 'ud:ui:settings:view:main:top':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:tab_link':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:tab_container':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:main:bottom':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:main:actions':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:tab:api:top':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:tab:api:bottom':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:credentials:top':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:sync:top':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:credentials:bottom':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:sync:bottom':
            // Add custom content here
            break;
        }
        
      }
      
      
    }
  
  }

}
