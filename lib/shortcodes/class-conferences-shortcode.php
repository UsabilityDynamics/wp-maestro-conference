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
                'paged' => array(
                    'name' => __('Page', ud_get_wp_maestro_conference('domain')),
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
        add_action('wp_ajax_nopriv_mc_conferences_filter', array($this, 'conferences_filter'));
      }

      /**
       *  Renders Shortcode
       */
      public function call( $atts = "" ) {
        global $paged;

        if(!$paged) $paged = 1;

        $is_available_page = apply_filters('mc_conferences_shortcode_pre', true, $atts );
        if (!$is_available_page)
          return false;

        $data = shortcode_atts( apply_filters( 'mc_conferences_shortcode_pre_call', array(
            'user_id' => '',
            'per_page' => '25',
            'paged' => $paged,
            'status' => '',            
            'template' => str_replace('_', '-', $this->id),
        ), $atts ), $atts );

        $data['posts'] = ud_get_wp_maestro_conference()->get_conferences(
                apply_filters('mc_conferences_shortcode_call', array(
            'user_id' => $data['user_id'],
            'posts_per_page' => $data['per_page'],
            'paged' => $data[ 'paged' ],
            'status' => $data['status']
                        ), $atts)
        );
        
        $big = 999999999; // need an unlikely integer
        $args = array(
            'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format' => '#',
            'current' => $paged,
            'total' => $data['posts']->max_num_pages
        );
        $data['pagination'] = paginate_links( $args );

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

      /**
       * Get conferences via AJAX
       */
      public function conferences_filter() {
        $request = $_REQUEST;

        if( !isset( $request[ 'paged' ] ) || $request[ 'paged' ] == '0' ) {
          $request['paged'] = '1';
        } elseif ( !is_numeric( $request['paged'] ) ) {


          $s = get_query_var( 'paged' );

          $link = explode("/", trim( $request['paged'], '\/' ));
          if (is_array($link) && strpos($link[count($link)-2], 'page') !== false && is_numeric($link[count($link)-1]))
            $request['paged'] = $link[count($link)-1];
          else
            $request['paged'] = 1;
        }

        $data = shortcode_atts(apply_filters('mc_conferences_shortcode_pre_ajax', array(
            'user_id' => '',
            'per_page' => '25',
            'status' => '',
            'paged' => '1',
            'template' => str_replace('_', '-', $this->id),
                ), $request), $request);

        if (in_array($request['mc_filter_field'], array('active', 'closed'))) {
          $data['status'] = $request['mc_filter_field'];
        }

        $query = apply_filters('mc_conferences_shortcode_ajax', array(
          'user_id' => $data['user_id'],
          'posts_per_page' => $data['per_page'],
          'offset' => $data['offset'],
          'paged' => $data['paged'],
          'status' => $data['status'],
          'post_status' => 'publish'
        ), $request);

        $data['posts'] = ud_get_wp_maestro_conference()->get_conferences( $query );

        $args = array(
            'base' => '/conferences/page/%#%/',
            'format' => '#',
            'current' => $request['paged'],
            'total' => $data['posts']->max_num_pages
        );
        $data['pagination'] = paginate_links( $args );
        
        $data['is_ajax'] = true;

        ob_start();
        $this->render( $data['template'], $data );
        $html = ob_get_clean();

        wp_send_json_success( array(
          'total' => count( $data['posts']->posts ),
          'query' => $query,
          'html' => $html,
        ) );

        exit();
      }

    }

    new Conferences_Shortcode();
  }
}

