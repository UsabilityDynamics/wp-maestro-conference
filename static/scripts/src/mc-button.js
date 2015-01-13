( function( $, s ){

  $( '.mc-button' ).click( function(){

    var e = $( this );
    var data = {
      action: "mc_button_action",
      wpnonce: e.data("wpnonce"),
      type: e.data("action"),
      conference_id: e.data("conference_id"),
      send_mail: e.data("send-mail"),
      extra: e.data("extra"),
      callback: e.data("callback"),
      redirect_to: e.data( 'redirect-to' ),
      success_label: e.data("success-label"),
      success_hide: e.data("success-hide")
    };

    $.ajax( {
      'type': 'POST',
      'url': s.admin_ajax,
      'data': data,
      'dataType': 'json',
      'cache': false,
      'complete': function( r, status ) {
        if( status === 'success' ) {
          if( r.responseJSON.success ) {
            e.addClass( 'success disabled' );
            if( data.success_hide == 'true' ) {
              e.hide();
            } else if( data.success_label !== '' ) {
              e.html( data.success_label );
            }
            $( document ).trigger( "mc_btn_success", [ data, e, r ] );
            if( typeof r.responseJSON.redirect_to == 'string' && r.responseJSON.redirect_to.length > 0 ) {
              window.location.replace( r.responseJSON.redirect_to );
            }
          } else {
            e.addClass( 'fail' );
            $( document ).trigger( "mc_btn_fail", [ data, e, r ] );
          }
        }
        else {
          e.addClass( 'fail' );
          $( document ).trigger( "mc_btn_fail", [ data, e, r ] );
        }
      }
    } );

  } );

}( jQuery, _mc_button ) );