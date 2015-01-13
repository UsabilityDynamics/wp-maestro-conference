<?php

/**
 * Shortcode: [mc_pin]
 * Template: static/views/shortcodes/mc-pin.php
 *
 * @since 1.0.0
 */

namespace UsabilityDynamics\MaestroConference {

  if (!class_exists('UsabilityDynamics\MaestroConference\Pin_Shortcode')) {

	class Pin_Shortcode extends Shortcode {

	  /**
	   * Constructor
	   */
	  public function __construct() {

		$options = array(
			'id' => 'mc_pin',
			'params' => array(
				'user_id' => array(
					'name' => __('User ID', ud_get_wp_maestro_conference('domain')),
					'description' => __('Optional. If not set, current user ID is used.', ud_get_wp_maestro_conference('domain')),
				),
				'conference_id' => array(
					'name' => __('Conference ID', ud_get_wp_maestro_conference('domain')),
					'description' => __('Must not be empty.', ud_get_wp_maestro_conference('domain')),
				),
				'template' => array(
					'name' => __('Template', ud_get_wp_maestro_conference('domain')),
					'description' => __('Optional. If not set, default template\'s name is used.', ud_get_wp_maestro_conference('domain')),
				)
			),
			'description' => __('Shows conference PIN of the current or specified user', ud_get_wp_maestro_conference('domain')),
			'group' => 'WP-Invoice',
		);

		parent::__construct($options);
	  }

	  /**
	   *  Renders Shortcode
	   */
	  public function call($atts = "") {

		$data = shortcode_atts(array(
			'user_id' => get_current_user_id(),
			'conference_id' => '',
			'template' => str_replace('_', '-', $this->id),
				), $atts);

		$data['pin'] = Utility::get_pin($data['user_id'], $data['conference_id']);

		$this->render( $data['template'], $data );
	  }

	}

	new Pin_Shortcode();
  }
}

