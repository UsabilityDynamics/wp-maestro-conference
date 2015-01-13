<?php

/**
 * Shortcode: [mc_conferences]
 * Template: static/views/shortcodes/mc-conferences.php
 *
 * @since 1.0.0
 */

namespace UsabilityDynamics\MaestroConference {

  if (!class_exists('UsabilityDynamics\MaestroConference\Conferences_Shortcode')) {

    class Conferences_Shortcode extends Shortcode {

      /**
       * Constructor
       */
      public function __construct() {

        $options = apply_filters('mc_conferences_shortcode_construct', array(
            'id' => 'mc_conferences',
            'params' => array(
                'user_id' => array(
                    'name' => __('User ID', ud_get_wp_maestro_conference('domain')),
                ),
                'offset' => array(
                    'name' => __('Offset', ud_get_wp_maestro_conference('domain')),
                ),
                'status' => array(
                    'name' => __('Status conference', ud_get_wp_maestro_conference('domain')),
                    'default' => '',
                    'enum' => array(
                        'active',
                        'closed'
                    )
                ),
                'per_page' => array(
                    'name' => __('Per Page', ud_get_wp_maestro_conference('domain')),
                    'default' => 25
                ),
                'template' => array(
                    'name' => __('Template', ud_get_wp_maestro_conference('domain')),
                )
            ),
            'description' => __('Renders Conferences', ud_get_wp_maestro_conference('domain')),
            'group' => 'Maestro Conference',
        ));

        parent::__construct($options);

        /* Hooks */
        add_action('wp_ajax_mc_conferences_filter', array($this, 'conferences_filter'));
      }

      /**
       *  Renders Shortcode
       */
      public function call($atts = "") {

        $data = shortcode_atts(array(
            'user_id' => '',
            'per_page' => '25',
            'offset' => '0',
            'status' => '',
            'template' => str_replace('_', '-', $this->id),
                ), $atts);

        $data['posts'] = ud_get_wp_maestro_conference()->get_conferences(
                apply_filters('mc_conferences_shortcode_call', array(
            'user_id' => $data['user_id'],
            'posts_per_page' => $data['per_page'],
            'offset' => $data['offset'],
            'status' => $data['status']
                        ), $atts)
        );

        wp_enqueue_script('mc-front', ud_get_wp_maestro_conference()->path('static/scripts/mc-front.js'), array('jquery'));
        wp_localize_script('mc-front', '_mc_front', array(
            'admin_ajax' => admin_url('admin-ajax.php'),
        ));
        $data['select_options'] = apply_filters('mc_conferences_shortcode_select', array(
            '' => __('All', ud_get_wp_maestro_conference('domain')),
            'active' => __('Active', ud_get_wp_maestro_conference('domain')),
            'closed' => __('Closed', ud_get_wp_maestro_conference('domain')),
        ));
        $this->render( $data['template'], $data );
      }

      public function conferences_filter() {
        $request = $_REQUEST;

        $data = shortcode_atts(array(
            'user_id' => '',
            'per_page' => '25',
            'offset' => '0',
            'status' => '',
            'template' => str_replace('_', '-', $this->id),
                ), $request);

        if (in_array($request['mc_filter_field'], array('active', 'closed'))) {
          $data['status'] = $request['mc_filter_field'];
        }
        $data['posts'] = ud_get_wp_maestro_conference()->get_conferences(
                apply_filters('mc_conferences_shortcode_ajax', array(
            'user_id' => $data['user_id'],
            'posts_per_page' => $data['per_page'],
            'offset' => $data['offset'],
            'status' => $data['status']
                        ), $request)
        );
        $data['is_ajax'] = true;
        $this->render( $data['template'], $data );
        exit();
      }

    }

    new Conferences_Shortcode();
  }
}

