/**
 * Migrated from toplevel_page_wp_crm
 *
 */
if( 'undefined' !== typeof google ) {

  google.load( "visualization", "1", {
    packages: [ "corechart" ]
  } );

  jQuery( ".wp_crm_visualize_results" ).click( function () {

    var filters = jQuery( '#wp-crm-filter' ).serialize()

    jQuery.ajax( {
      url: ajaxurl,
      context: document.body,
      data: {
        action: 'wp_crm_visualize_results',
        filters: filters
      },
      success: function ( result ) {
        jQuery( '.wp_crm_ajax_result' ).html( result );
        jQuery( '.wp_crm_ajax_result' ).show( "slide", { direction: "down" }, 1000 )
      }
    } );

  } );

}