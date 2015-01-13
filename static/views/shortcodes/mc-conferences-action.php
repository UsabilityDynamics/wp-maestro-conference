<?php
/**
 * Shortcode: [mc_conferences]
 * Action Template
 */

/* Show only buttons for active conferences */
if( $conference->status == 'active' ) {

  /* If conference will be started less then in 24h and user is already registered, just show disabled button */
  if( $conference->is_active && $user_conference_data->is_registered ) {
    echo '<button class="btn button mc-button disabled">' . __( 'Registered', ud_get_wp_maestro_conference( 'domain' )  ) . '</button>';
  }
  /* No available callers in conference. */
  elseif( !ud_get_wp_maestro_conference()->has_available_callers( $conference->ID ) && !$user_conference_data->is_registered ) {
    echo '<button class="btn button mc-button disabled">' . __( 'Registration Full', ud_get_fsw_membership('domain') ) . '</button>';
  }
  /* Show Action button to Register or Cancel conference  */
  else {
    $label = $user_conference_data->is_registered ? __( 'Cancel', ud_get_wp_maestro_conference( 'domain' ) ) : __( 'Pre-Register', ud_get_wp_maestro_conference( 'domain' ) );
    $action = $user_conference_data->is_registered ? 'remove' : 'add';
    /* If conference will be less then in 24h we only can Register to it ( not Pre-Register ) */
    if( $conference->is_active ) {
      $label = __( 'Register', ud_get_wp_maestro_conference( 'domain' ) );
    }

    do_shortcode( "[mc_button conference_id='{$conference->ID}' action='{$action}' label='{$label}' ]" );
  }

}