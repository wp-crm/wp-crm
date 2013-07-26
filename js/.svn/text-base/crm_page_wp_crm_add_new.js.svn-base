/**
 * Scripts specific only to the WP-CRM Profile Editor
 * Form-specific functionality is handled by wp_crm_profile_editor.js
 *
 * This file may be loaded on the front-end.
 *
 * For example, Role and User Delection specific things are handled here because
 * they are not part of the editor.
 *
 */

wp_crm_ui.changed_fields = new Array();

/**
 * Hack fo IE7,8
 * Adds standart function trim() to string
 * @author peshkov@UD
 */
if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, '');
  }
}

jQuery( document ).ready( function() {

  if( typeof wp_crm_dev_mode == 'undefined' ) {
    var wp_crm_dev_mode = false;
  }

  /* Hide Toggle Settings link if there are no settings */
  if( !jQuery( '.wp_crm_advanced_user_actions.wp-tab-panel' ).text().trim().length ) {
    jQuery( 'ul.wp_crm_advanced_user_actions_wrapper' ).hide();
  }

  jQuery( "form#crm_user input[ type=text ], form#crm_user input[ type=checkbox ], form#crm_user select" ).change( function() {
    var this_attribute = jQuery( this ).attr( "wp_crm_slug" );
    wp_crm_ui.change_made = true;
    wp_crm_ui.changed_fields.push( this_attribute );
  });

  /**
   * Validate new user email for duplacates
   * @Author odokienko@UD
   */
  jQuery( "input.wp_crm_user_email_field").change( function ( ) {
      var obj = this;
      var user_id = jQuery("#user_id").val();

      /* elear all error notifications */
      jQuery( obj ).removeClass( "wp_crm_input_error" ).parent().removeClass( "wp_crm_input_error" );
      jQuery( obj ).removeClass( "email_validated" ).parent().removeClass( "email_validated" );
      jQuery( 'span.error', jQuery( obj ).parent()).remove();

      /* indicate validationg state */
      if ( jQuery( obj ).val() ){
        jQuery( obj ).attr('disabled', obj).parent().addClass( "email_validating" );
        jQuery.post(ajaxurl, {
          action:'wp_crm_check_email_for_duplicates',
          email: jQuery( obj ).val(),
          user_id: user_id
        },function(response) {
          /* double check: submit may set error status and not wait for response */
          jQuery( obj ).removeClass( "wp_crm_input_error" ).parent().removeClass( "wp_crm_input_error" );

          if(response == 'Ok') {
            /* class 'email_validated' are used by .submit(). If class is not present then form will be invalid and won't  submitted */
            jQuery( obj ).addClass( "email_validated" ).parent().addClass( "email_validated" );
          }else{
            jQuery( obj ).addClass( "wp_crm_input_error" ).parent().addClass( "wp_crm_input_error" ).append("<span class='error'>"+response+"</span>");
          }
          /* remove validation state */
          jQuery( obj ).attr('disabled', false).parent().removeClass( "email_validating" );
        });
      }else{
        /** Empty field is valid of course if it is not required */
        jQuery( obj ).addClass( "email_validated" ).parent().addClass( "email_validated" );
      }

  });


   /* Handles form saving */
  jQuery( "form#crm_user" ).submit( function( form ) {
    return wp_crm_save_user_form( form );
  });

  jQuery( 'ul.wp-tab-panel-nav a' ).click( function(){
    var panel_wrapper = jQuery( this ).parents( '.wp-tab-panel-wrapper' );

    var t = jQuery( this ).attr( 'href' );
    jQuery( this ).parent().addClass( 'tabs' ).siblings( 'li' ).removeClass( 'tabs' );
    jQuery( '.wp-tab-panel', panel_wrapper ).hide();
    jQuery( t, panel_wrapper ).show();

    return false;
  });

  /*  Verify deletion saving */
  jQuery( '.submitdelete' ).click( function() {
    return confirm( 'Are you sure you want to delete user?' );
  });

  jQuery( 'div.wp_crm_toggle_advanced_user_actions' ).click( function() {
    jQuery( 'div.wp_crm_advanced_user_actions' ).toggle();
  });

  jQuery( 'tr.not_primary .wp_crm_input_wrap select,  tr.not_primary .wp_crm_input_wrap select' ).live( 'mousedown', function() {
    jQuery( this ).trigger( 'wp_crm_value_changed', {object: this, action: 'option_mousedown'});
  });

  jQuery( ".wp_crm_truncated_show_hidden" ).click( function() {
    var parent = jQuery( this ).parent();
    jQuery( '.truncated_content:first', parent ).toggle();
  });

  jQuery( ".wp_crm_show_message_options" ).click( function() {
      jQuery( '.wp_crm_message_options' ).toggle();
  });

  jQuery( ".wp_crm_toggle_message_entry" ).click( function() {
    jQuery( ".wp_crm_new_message" ).toggle();
    jQuery( ".wp_crm_new_message #wp_crm_message_content" ).focus();
  });

  jQuery( "#wp_crm_role" ).change( function() {
    jQuery( ".wp_crm_user_entry_row" ).show();
    var new_setting = jQuery( 'option:selected', this ).val();
    jQuery( wp_crm.hidden_attributes[ new_setting ] ).each( function( index,value ) {
      jQuery( 'tr.wp_crm_' + value + '_row' ).hide();
    });
  });

  jQuery( "#wp_crm_add_message" ).click( function() {
    wp_crm_save_stream_message();
  });


/**
 * Adds another attribute field
 *
 * @todo Should migrate functionality into UD Dynamic Rows
 *
 */
  jQuery( '.add_another' ).live( "click", function() {
    var parent_row =  jQuery( this ).closest( ".wp_crm_user_entry_row" );
    var input_div =  jQuery( '.input_div:last', parent_row );
    var new_input_div =  input_div.clone();

    jQuery( 'input', new_input_div ).val( '' );

    /** Get current hash */
    var current_hash = jQuery( 'input', new_input_div ).attr( 'random_hash' );

    /** Fix hashes */
    var new_hash = Math.floor( ( 9999 )*Math.random() ) + 1000;

    /** Need a more elegant way of doing this */
    if( jQuery( 'input', new_input_div ).length ) {
      jQuery( 'input', new_input_div ).attr( 'random_hash', new_hash )
      var old_name = jQuery( 'input', new_input_div ).attr( 'name' );
      jQuery( 'input', new_input_div ).attr( 'name', old_name.replace( current_hash, new_hash ) );

    }

    if( jQuery( 'select', new_input_div ).length ) {
      jQuery( 'select', new_input_div ).attr( 'random_hash', new_hash )
      var old_name = jQuery( 'select', new_input_div ).attr( 'name' );
       jQuery( 'select', new_input_div ).attr( 'name', old_name.replace( current_hash, new_hash ) );
    }

    /** Insert row */
      jQuery( new_input_div ).insertAfter( input_div );

    /** hide 'add another' */
    jQuery( this ).hide();

  });


  /**
   * Execute filter on User Activity Stream
   *
   * @author Odokienko@UD
   */
  jQuery('#crm_user_activity_filter input').change(function() {
    wp_crm_update_activity_stream();
  });



});
