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

		$options = array(
			'id' => 'mc_conference',
			'params' => array(
				'user_id' => array(
					'name' => __('User ID', ud_get_wp_maestro_conference('domain')),
				),
				'offset' => array(
					'name' => __('Offset', ud_get_wp_maestro_conference('domain')),
				),
				'paid' => array(
					'name' => __('Is paid conference', ud_get_wp_maestro_conference('domain')),
					'default' => 'paid',
					'enum' => array(
						'paid',
						'free'
					)
				),
				'status' => array(
					'name' => __('Status conference', ud_get_wp_maestro_conference('domain')),
					'default' => 'active',
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
		);

		parent::__construct($options);
	  }

	  /**
	   *  Renders Shortcode
	   */
	  public function call($atts = "") {

		$data = shortcode_atts(array(
			'user_id' => '',
			'paid' => 'paid',
			'per_page' => '25',
			'offset' => '0',
			'status' => 'active',
			'template' => str_replace('_', '-', $this->id),
				), $atts);

		$data['posts'] = ud_get_wp_maestro_conference()->get_conferences(array(
			'user_id' => $data['user_id'],
			'isPaid' => $data['paid'],
			'posts_per_page' => $data['per_page'],
			'offset' => $data['offset'],
			'status' => $data['status']
		));

		$this->render($data, $data['template']);
	  }

	}

	new Conferences_Shortcode();
  }
}

