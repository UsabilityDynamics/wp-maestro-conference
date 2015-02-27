<?php

/**
 * UI
 *
 * @author UsabilityDynamics, inc
 */

namespace UsabilityDynamics\MaestroConference {

  use UsabilityDynamics\Model\Post;

if (!class_exists('UsabilityDynamics\MaestroConference\UI')) {

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
        $this->ui = new \UsabilityDynamics\UI\Settings($this->instance->settings, $this->instance->get_schema('extra.schemas.ui'));
        add_action('admin_menu', array($this, 'admin_menu'), 101);
        /**
         * @see vendor/usabilitydynamics/lib-ui/static/templates/admin/main.php
         */
        add_action('ud:ui:settings:view:main:top', array($this, 'custom_ui'));
        // Adds Tab Item ( Link )
        add_action('ud:ui:settings:view:tab_link', array($this, 'custom_ui'));
        // Adds Panel for Tab
        add_action('ud:ui:settings:view:tab_container', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:main:bottom', array($this, 'custom_ui'));
        // Adds Custom Actions ( e.g. Synchronize with Maestro Conference )
        add_action('ud:ui:settings:view:main:actions', array($this, 'custom_ui'));

        /**
         * @see vendor/usabilitydynamics/lib-ui/static/templates/admin/tab.php
         */
        //
        add_action('ud:ui:settings:view:tab:api:top', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:tab:api:bottom', array($this, 'custom_ui'));

        add_action('ud:ui:settings:view:tab:conference:top', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:tab:conference:bottom', array($this, 'custom_ui'));

        add_action('ud:ui:settings:view:tab:meta:top', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:tab:meta:bottom', array($this, 'custom_ui'));

        /**
         * @see vendor/usabilitydynamics/lib-ui/static/templates/admin/section.php
         */
        add_action('ud:ui:settings:view:section:credentials:top', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:section:credentials:bottom', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:section:sync:top', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:section:sync:bottom', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:section:registration:top', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:section:registration:bottom', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:section:pre_registration:top', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:section:pre_registration:bottom', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:section:meta_fields:top', array($this, 'custom_ui'));
        add_action('ud:ui:settings:view:section:meta_fields:bottom', array($this, 'custom_ui'));
      }

      /**
       * Multiple actions (action on admin_menu hook):
       * - parse (validate) schema
       * - add settings page to menu
       * - add specific hooks
       *
       */
      public function admin_menu() {
        if (has_action('load-mconference_page_maestro_conferences_settings', array($this->ui, 'request'))) {
          remove_action('load-mconference_page_maestro_conferences_settings', array($this->ui, 'request'));
          add_action('load-mconference_page_maestro_conferences_settings', array($this, 'request'));
        }
      }

      /**
       * Saves data.
       *
       */
      public function request() {
        // Determine if we have to do form submit
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'ui_settings')) {
          return false;
        }

        $changed = false;
        foreach ($_POST as $i => $v) {
          $id = str_replace('|', '.', $i);
          $changed = true;
          $this->instance->settings->set($id, $v);
        }
        if ($changed) {
          $this->instance->settings->commit();
          wp_redirect($_POST['_wp_http_referer'] . '&message=updated');
          exit;
        }
      }

      /**
       *
       */
      public function custom_ui() {

        $screen = get_current_screen();

        if ($screen->id !== 'mconference_page_maestro_conferences_settings') {
          return false;
        }
        $hook = current_filter();

        switch ($hook) {

          case 'ud:ui:settings:view:main:top':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:tab_link':            
            $this->get_template_part('synchronization_button');            
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
          case 'ud:ui:settings:view:tab:conference:top':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:tab:conference:bottom':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:tab:meta:top':            
            // Add custom content here
            break;
          case 'ud:ui:settings:view:tab:meta:bottom':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:credentials:top':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:credentials:bottom':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:sync:top':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:sync:bottom':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:registration:top':
            //$this->get_template_part('section_registration');
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:registration:bottom':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:pre_registration:top':
            //$this->get_template_part('section_pre_registration');
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:pre_registration:bottom':
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:meta_fields:top':
            //$this->get_template_part('section_meta', $this);
            // Add custom content here
            break;
          case 'ud:ui:settings:view:section:meta_fields:bottom':
            // Add custom content here
            break;
        }
      }

    }

  }
}
