<?php
/**
 * Shortcode: [mc_button]
 *
 *  Available vars:
 *  action
 *  conference_id
 *  label
 *  success_label
 *  hide_on_success
 *  send_mail
 *  desc
 *  extra
 *  callback
 *  template
 *  wpnonce
 */

printf( '<button class="btn button mc-button btn-success" data-wpnonce="%s" data-action="%s" data-conference_id="%s" data-send-mail="%s" data-desc="%s" data-extra="%s" data-callback="%s" data-success-label="%s" data-success-hide="%s" >%s</button>',
  esc_attr( $wpnonce ),
  esc_attr( $action ),
  esc_attr( $conference_id ),
  esc_attr( $send_mail ),
  htmlentities( $desc ),
  urlencode( htmlspecialchars_decode( $extra ) ),
  esc_attr( $callback ),
  esc_attr( $success_label ),
  esc_attr( $hide_on_success ),
  $label
);