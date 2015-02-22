<?php

/**
 * Shortcode: [mc_conference]
 * Template: static/views/shortcodes/mc-conference.php
 *
 * @since 1.0.0
 */

namespace UsabilityDynamics\MaestroConference {

  if (!class_exists('UsabilityDynamics\MaestroConference\Conference_Shortcode')) {

    class Conference_Shortcode extends Shortcode {

      /**
       * Constructor
       */
      public function __construct() {

        $options = apply_filters('mc_conference_shortcode_construct', array(
            'id' => 'mc_conference',
            'params' => array(
                'conference_id' => array(
                    'name' => __('Conference ID', ud_get_wp_maestro_conference('domain')),
                )
            ),
            'description' => __('Renders Conference', ud_get_wp_maestro_conference('domain')),
            'group' => 'Maestro Conference',
        ));

        parent::__construct($options);
      }

      /**
       *  Renders Shortcode
       */
      public function call($atts = "") {
        $is_available_page = apply_filters('mc_conferences_shortcode_pre', $atts);
        if (!$is_available_page)
          return false;
        $data = shortcode_atts(apply_filters('mc_conference_shortcode_pre_call', array(
            'conference_id' => '',         
            'template' => str_replace('_', '-', $this->id),
                ), $atts), 
                $atts);

        $data['posts'] = ud_get_wp_maestro_conference()->get_conference_by_id(
                apply_filters('mc_conference_shortcode_call', array(
            'conference_id' => $data['conference_id']), $atts)
        );
        $this->render($data, $data['template']);
      }

    }

    new Conference_Shortcode();
  }
}

