/**
 * WP-Property Global Admin Scripts
 *
 * This file is included on all back-end pages, so extra care needs be taken to avoid conflicts.
 *
 * This file is also referenced as a dependancy by all other WP-CRM scripts, and if wp_enqueue_script() is used will be loaded.
 *
 */

var wp_crm_ui = {};
var wpp_crm_form_stop = false;

jQuery( document ).ready( function() {

  if( typeof wp_crm_dev_mode == 'undefined' ) {
    var wp_crm_dev_mode = false;
  }

  /* Cycle through all advanced UI options and toggle them */
  jQuery( '.wp_crm_show_advanced' ).each( function() {
    wp_crm_toggle_advanced_options( this );
  });

  /* Enable monitoring of toggling of advanced UI options */
  jQuery( '.wp_crm_show_advanced' ).live( 'click', function( event ) {
    wp_crm_toggle_advanced_options( this, event );
  });

  jQuery( '.wp_crm_cancel_ajax_action' ).live( 'click', function() {
    var ajax_wrapper = jQuery( this ).parents( '.wpc_action_wrapper' );
    ajax_wrapper.remove();

  });

  jQuery( 'label.wpc_closest' ).live( 'click',function() {
    var parent = jQuery( this ).closest( "li" );
    var element = jQuery( "input[type=checkbox]", parent );

    if( element.is( ":checked" ) ) {
      element.removeAttr( "checked" );
    } else {
      element.attr( "checked", "checked" );
    }

  });

  jQuery( '.wp_crm_toggle' ).live( 'click',function() {
    jQuery( '.' + jQuery( this ).attr( 'toggle' ) ).toggle();
  });

  jQuery( '.wp_crm_message_quick_action' ).live( 'click',function() {
    var action = jQuery( this ).attr( 'wp_crm_action' );
    var object_id = jQuery( this ).attr( 'object_id' );
    var instant_hide = jQuery( this ).attr( 'instant_hide' );

    var parent_element = jQuery( this ).parents( 'tr' );

    if( jQuery( this ).attr( 'verify_action' ) ) {
      if( !confirm( 'Are you sure?' ) ) {
        return false;
      }
    }

    if( instant_hide == "true" ) {
      jQuery( parent_element ).hide();
    }

    jQuery.post( ajaxurl, {action: 'wp_crm_quick_action', wp_crm_quick_action: action, object_id: object_id}, function( result ) {
      if( result.success = 'true' ) {

      }

      switch( result.action ) {
        case 'hide_element':
          jQuery( parent_element ).hide();
        break;
      }
    }, 'json' );
  });

  // Add row to UD UI Dynamic Table
  jQuery( ".wp_crm_add_row" ).live( 'click' , function() {

    var table = jQuery( this ).parents( '.ud_ui_dynamic_table' );
    var table_id = jQuery( table ).attr( "id" );

    // Clone last row
    var cloned = jQuery( ".wp_crm_dynamic_table_row:last", table ).clone();

    // Find and replace attribute ID ( FOR ) to exclude DOM bugs: ID should be unique.
    jQuery( cloned ).children().each( function( i, e ) {
      if( jQuery( 'ul', e ).length > 0 ) {
        var liEl = jQuery( 'ul', e ).children();
        if( liEl.length > 0 ) {
          liEl.each( function( i,e ){
            var label = jQuery( 'label', e );
            var input = jQuery( 'input', e );
            if( label.length > 0 && input.length > 0 ) {
              var attrFor = label.attr( 'for' );
              var attrId = input.attr( 'id' );
              if( attrFor != '' && attrId != '' ) {
                var rand=Math.floor( Math.random()*10000 );
                label.attr( 'for', 'new_field_'+rand );
                input.attr( 'id', 'new_field_'+rand );
              }
            }
          });
        }
      }
    });

    // Insert new row after last one
    jQuery( cloned ).appendTo( table );

    // Get Last row to update names to match slug
    var added_row = jQuery( ".wp_crm_dynamic_table_row:last", table );

    // Display row ust in case
    jQuery( added_row ).show();

    // Blank out all values
    jQuery( "input[type=text]", added_row ).val( '' );
    jQuery( "input[type=checkbox]", added_row ).attr( 'checked', false );
    jQuery( "textarea", added_row ).val( '' );
    jQuery( "select", added_row ).val( '' );

    // Unset 'new_row' attribute
    jQuery( added_row ).attr( 'new_row', 'true' );

    jQuery( '.slug_setter', added_row ).focus();

  });

  // When the .slug_setter input field is modified, we update names of other elements in row
  jQuery( ".wp_crm_dynamic_table_row[new_row=true] input.slug_setter" ).live( "change", function() {

    var this_row = jQuery( this ).parents( 'tr.wp_crm_dynamic_table_row' );

    // Slug of row in question
    var old_slug = jQuery( this_row ).attr( 'slug' );

    // Get data from input.slug_setter
    var new_slug = jQuery( this ).val();

    // Conver into slug
    var new_slug = wp_crm_create_slug( new_slug );

    // Don't allow to blank out slugs
    if( new_slug == "" )
      return;

    var samename = jQuery( ".wp_crm_dynamic_table_row[new_row=false][slug="+new_slug+"]" ).length;
    if( samename ){
      var rand=Math.floor( Math.random()*10000 );
      new_slug=new_slug+rand;
    }

    // If slug input.slug exists in row, we modify it
    jQuery( ".slug" , this_row ).val( new_slug );

    // Update row slug
    jQuery( this_row ).attr( 'slug', new_slug );

    // Cycle through all child elements and fix names
    jQuery( 'input,select,textarea', this_row ).each( function( element ) {


      var old_name = jQuery( this ).attr( 'name' );

      if ( typeof old_name != 'undefined' ) {

        var new_name =  old_name.replace( old_slug,new_slug );

        if( jQuery( this ).attr( 'id' ) ) {
          var old_id = jQuery( this ).attr( 'id' );
          var new_id =  old_id.replace( old_slug,new_slug );
        }

        // Update to new name
        jQuery( this ).attr( 'name', new_name );
        jQuery( this ).attr( 'id', new_id );
      }

    });

    // Cycle through labels too
      jQuery( 'label', this_row ).each( function( element ) {

      if( !jQuery( this ).attr( 'for' ) ) {
        return;
      }

      var old_for = jQuery( this ).attr( 'for' );
      var new_for =  old_for.replace( old_slug,new_slug );

      // Update to new name
      jQuery( this ).attr( 'for', new_for );


    });

  });


  jQuery( ".wp_crm_delete_row" ).live( 'click', function() {

    var parent = jQuery( this ).parents( 'tr.wp_crm_dynamic_table_row' );
    var row_count = jQuery( ".wp_crm_delete_row:visible" ).length;

    // Blank out all values
    jQuery( "input[type=text]", parent ).val( '' );
    jQuery( "textarea", parent ).val( '' );
    jQuery( "input[type=checkbox]", parent ).attr( 'checked', false );

    jQuery( parent ).attr( 'new_row', 'true' );

    // Don't hide last row
    if( row_count > 1 ) {
      jQuery( parent ).hide();
      jQuery( parent ).remove();
    }

  });

    jQuery( '.wp_crm_overview_filters .all' ).click( function(){
      if ( jQuery( this ).find( 'input' ).attr( 'checked' ) ){
        jQuery( this ).siblings( 'li' ).find( 'input' ).removeAttr( 'checked' );
      }
    } )

    jQuery( '.wp_crm_role_list' ).change( function(){
      jQuery( '.wp_crm_overview_filters .all' ).find( 'input' ).removeAttr( 'checked' );
    } )


    jQuery( '.wpp_crm_filter_section_title' ).click( function(){
      var parent = jQuery( this ).parents( '.wp_crm_overview_filters' );
      jQuery(' .wp_crm_checkbox_filter', parent).slideToggle('fast', function(){
        if(jQuery(this).css('display') == 'none') {
          jQuery('.wpp_crm_filter_show', parent).html('Show');
        } else {
          jQuery('.wpp_crm_filter_show', parent).html('Hide');
        }
      });
    });

  });


function wp_crm_create_slug( slug ) {

    slug = slug.replace( /[^a-zA-Z0-9_\s]/g,"" );
    slug = slug.toLowerCase();
    slug = slug.replace( /\s/g,'_' );

    return slug;
}


/**
 * Ran when user attempt to leave profile with unsaved data.
 *
 */
  function wp_crm_save_stream_message() {
    var user_id = wp_crm.user_id;
    var wp_crm_message_content = jQuery( "#wp_crm_message_content" ).val();
    var wp_crm_message_type = jQuery( "#wp_crm_message_type" ).val();

    if( wp_crm_message_content == '' ) {
      return;
    }

    jQuery.post( ajaxurl, {
        action: 'wp_crm_insert_activity_message',
        time: jQuery( '.wp_crm_message_options .datepicker' ).val(),
        content: wp_crm_message_content,
        user_id: user_id,
        message_type:wp_crm_message_type
      }, function( response ) {

        if( response.success == 'true' ) {
          jQuery( '#wp_crm_user_activity_stream' ).slideUp( 'fast' );
          wp_crm_update_activity_stream({filter_types:false});
          jQuery( '#wp_crm_message_content' ).val( '' );
          jQuery( '.wp_crm_new_message' ).slideUp( 'fast' );


        } else {
          alert( 'Could not save entry' );
        }

      },
      "json"
    );

    return;

  }


  /**
   * Ran when user attempt to leave profile with unsaved data.
   *
   */
  function wp_crm_handle_unload() {

    var changed_fields = wp_crm_ui.changed_fields;

    if( changed_fields.length ) {
      return true;
    }

    return false;
  }


  /**
   * Looks through all input fields and generates new random keys ( should be done after new elements are added to DOM
   *
   */
  function wp_crm_refresh_random_keys( element ) {

    if( jQuery( element ).attr( "random_hash" ) ) {
      var old_hash = jQuery( element ).attr( "random_hash" );
      var new_hash = Math.floor( Math.random()*10000000 );
      var current_html = jQuery( element ).html();

      old_hash = new RegExp( old_hash, 'gi' );

      var new_html = current_html.replace( old_hash,new_hash );

      jQuery( element ).html( new_html );


      jQuery( element ).attr( "random_hash", new_hash );

    }

  }

  /*  */
  jQuery( ".wp_crm_load_more_stream" ).live( 'click', function() {
    var params = {
      per_page : jQuery( this ).attr( 'per_page' ),
      all_messages : jQuery( this ).attr( 'all_messages' ),
      more_per_page : jQuery( this ).attr( 'limited_messages' ),
      limited_messages : jQuery( this ).attr( 'limited_messages' )
    }
    wp_crm_update_activity_stream( params );
  });


  /**
   * Contact history and messages for a user
   *
   *
   */
  function wp_crm_update_activity_stream( params ) {

    var obj = jQuery('#crm_user_activity_filter :input').first();

    var parent =  jQuery( obj ).closest( '#crm_user_activity_filter' );
    var msglist = jQuery( '.wp_crm_stream_status.wp_crm_load_more_stream' );

    var filter_types_visible = jQuery.map( jQuery( ':input', parent ).filter( ':checked' ), function( e, i ) {
      return obj = {
        attribute: jQuery( e ).attr( 'attribute' ),
        other: jQuery( e ).attr( 'other' ),
        hidden: false
      };
    });

    var filter_types_hidden = jQuery.map( jQuery( ':input', parent ).filter( ':not( :checked )' ), function( e, i ) {
      return obj = {
        attribute: jQuery( e ).attr( 'attribute' ),
        other: jQuery( e ).attr( 'other' ),
        hidden: true
      };
    });

    var params = jQuery.extend( true, {
      action: 'wp_crm_get_user_activity_stream',
      user_id: jQuery( "#user_id" ).val(),
      limited_messages : jQuery(msglist).attr('limited_messages'),
      filter_types: filter_types_visible.concat(filter_types_hidden)
    }, params );


    jQuery( "#user_activity_history .loading" ).show();
    jQuery.post(
      ajaxurl, params, function( response ) {
        jQuery( "#user_activity_history .loading" ).hide();
        jQuery( "#wp_crm_user_activity_stream tbody" ).html( response.tbody );
        if ( response.more_per_page ){
          jQuery( "#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream" ).attr( 'per_page',response.per_page );
          jQuery( "#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream span.more_count" ).html( response.more_per_page );
        }
        if( typeof response.total_count!='undefined' ){
          var total = response.total_count;
          jQuery( "#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream" ).attr( 'all_messages',total );
        }else{
          var total = jQuery( "#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream .wp_crm_counts .total_count" ).html();
        }
        if ( response.current_count>=total ){
          jQuery( "#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream" ).hide();
        }else{
          jQuery( "#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream" ).show();
        }
        jQuery( "#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream .wp_crm_counts .current_count" ).html( response.current_count );
        jQuery( "#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream .wp_crm_counts .total_count" ).html( total );


        jQuery( '#wp_crm_user_activity_stream' ).slideDown( "fast" );

      },
      "json"
    );

  }

  /**
   * Function ran before form is saved.
   *
   */
  function wp_crm_save_user_form( form ) {
    var form = jQuery( "#crm_user" );
    var stop_form = false;
    var password_1 = jQuery( "#wp_crm_password_1" ).val();
    var password_2 = jQuery( "#wp_crm_password_2" ).val();

    wp_crm_developer_log( 'wp_crm_save_user_form() initiated.' );
    wp_crm_developer_log( 'Required fields found: ' + jQuery( ".wp_crm_required_field", form ).length );

    jQuery( "*", form ).removeClass( "wp_crm_input_error" );

    /* Cycle Through all Requires fields wrappers */
    jQuery( ".wp_crm_required_field", form ).each( function()  {

      var meta_key = jQuery( this ).attr( 'meta_key' );
      var input_type = jQuery( this ).attr( 'wp_crm_input_type' );
      var has_options = jQuery( this ).hasClass( 'wp_crm_has_options' ) ? true : false;

      wp_crm_developer_log( {meta_key : meta_key, has_options: has_options, input_type: input_type});

      if( input_type == 'text' || input_type == 'date' ) {

        if( jQuery( "input.regular-text:first", this ).val() == '' ) {
          jQuery( "input.regular-text:first", this ).addClass( "wp_crm_input_error" );
          jQuery( "input.regular-text:first", this ).focus();
          jQuery( ".blank_slate", this ).hide();
          jQuery( ".input_div", this ).show();
          stop_form = true;
        }

        /* If this text input element has options, we make sure a value is selected */
        if( has_options && jQuery( "select.wp_crm_input_options:first", this ).val() == '' ) {
          jQuery( "select.wp_crm_input_options:first", this ).addClass( "wp_crm_input_error" );
          jQuery( "select.wp_crm_input_options:first", this ).focus();
          stop_form = true;
        }

      }

      if( input_type == 'textarea' ) {

        if( jQuery( "textarea.wp_crm_required_field:first", this ).val() == '' ) {
          jQuery( "textarea.wp_crm_required_field:first", this ).addClass( "wp_crm_input_error" );
          jQuery( "textarea.wp_crm_required_field:first", this ).focus();
          jQuery( ".blank_slate", this ).hide();
          jQuery( ".input_div", this ).show();
          stop_form = true;
        }

        /* If this text input element has options, we make sure a value is selected */
        if( has_options && jQuery( "select.wp_crm_input_options:first", this ).val() == '' ) {
          jQuery( "select.wp_crm_input_options:first", this ).addClass( "wp_crm_input_error" );
          jQuery( "select.wp_crm_input_options:first", this ).focus();
          stop_form = true;
        }

      }

      if( input_type == 'dropdown' && jQuery( "select:first", this ).val() == '' ) {
        jQuery( "select:first", this ).addClass( 'wp_crm_input_error' );
        jQuery( "select:first", this ).focus();
        jQuery( ".blank_slate", this ).hide();
        jQuery( ".input_div", this ).show();
        stop_form = true;
      }

    });

    if( jQuery( "input.wp_crm_user_email_field:not(.email_validated)", form ).length) {

      jQuery( "input.wp_crm_user_email_field:not(.email_validated):first", form ).addClass( "wp_crm_input_error" );
      jQuery( "input.wp_crm_user_email_field:not(.email_validated):first", form ).focus();
      jQuery( ".blank_slate", form ).hide();
      jQuery( ".input_div", form ).show();
      stop_form = true;

    }

    if( stop_form ) {
      return false;
    }

    /*  Check if password has been entered and they match */
    if( password_1 != "" ) {

      if( password_1 != password_2 ) {
        jQuery( ".wp_crm_advanced_user_actions" ).show();
        jQuery( "#wp_crm_password_1" ).focus();
        return false;
      }
    }

    jQuery( document ).trigger( 'wp_crm_user_profile_save', {object: this, action: 'option_mousedown'});

    if( wpp_crm_form_stop ) {
      return false;
    }

    return true;
  }



  /**
   * Function to display admin console log messages.
   *
   * Function executed on the spot, as opposed to WP_CRM_F::console_log() which runs
   * all the scripts in the footer.
   *
   */
  function wp_crm_developer_log( m ) {

    if( typeof wp_crm_dev_mode == 'undefined' ) {
      return false;
    }

    if( typeof console == "object" && typeof console.log == "function" ) {
      console.log( m );
    }

  }




/**
 * Automatically sets default values for fields that are required to have them.
 *
 * triggered_event determines if function is triggered by a clicked event, or .ready()
 *
 * Copyright 2011 Usability Dynamics, Inc. <info@usabilitydynamics.com>
 */
function wp_crm_handle_default_values( this_element, triggered_event ) {
  var current_value = jQuery( this_element ).val();
  var default_value = jQuery( this_element ).attr( "default_value" );

  if( typeof triggered_event != 'object' ) {
    triggered_event = false;
  } else {
    triggered_event = true;
  }

  if( default_value == "" ) {
    return;
  }

  /* If no value, we force default value */
  if( current_value == "" || current_value == default_value ) {
    jQuery( this_element ).val( default_value );
    jQuery( this_element ).addClass( "wp_crm_forced_default_value" );

    if( triggered_event ) {
      jQuery( "<span class=\"wp_crm_input_quick_fade\">Default set.</span>" ).insertAfter( this_element ).fadeOut( 1000 );
    }

  } else {
    jQuery( this_element ).val( current_value );
    jQuery( this_element ).removeClass( "wp_crm_forced_default_value" );
  }

}


/**
 * Toggle advanced options that are somehow related to the clicked trigger
 *
 * triggered_event determines if function is triggered by a clicked event, or .ready()
 *
 * If trigger element has an attr of 'show_type_source', then function attempt to find that element and get its value
 * if value is found, that value is used as an additional requirement when finding which elements to toggle
 *
 * Example: <span class="wp_crm_show_advanced" show_type_source="id_of_input_with_a_string" advanced_option_class="class_of_elements_to_trigger" show_type_element_attribute="attribute_name_to_match">Show Advanced</span>
 * The above, when clicked, will toggle all elements within the same parent tree of cicked element, with class of "advanced_option_class" and with attribute of "show_type_element_attribute" the equals value of "#id_of_input_with_a_string"
 *
 * Clicking the trigger in example when get the value of:
 * <input id="value_from_source_element" value="some_sort_of_identifier" />
 *
 * And then toggle all elements like below:
 * <li class="class_of_elements_to_trigger" attribute_name_to_match="some_sort_of_identifier">Data that will be toggled.</li>
 *
 * Copyright 2011 Usability Dynamics, Inc. <info@usabilitydynamics.com>
 */
function wp_crm_toggle_advanced_options( this_element, triggered_event ) {
    var advanced_option_class = false;
    var show_type = false;
    var show_type_element_attribute = false;

    if( typeof triggered_event != 'object' ) {
      triggered_event = false;
    } else {
      triggered_event = true;
    }

    //* Try getting arguments automatically */
    var wrapper = ( jQuery( this_element ).attr( 'wrapper' ) ? jQuery( this_element ).closest( '.' + jQuery( this_element ).attr( 'wrapper' ) )  : jQuery( this_element ).parents( '.wp_crm_dynamic_table_row' ) );

    if( jQuery( this_element ).attr( "advanced_option_class" ) !== undefined ) {
      var advanced_option_class = "." + jQuery( this_element ).attr( "advanced_option_class" );
    }

    if( jQuery( this_element ).attr( "show_type_element_attribute" ) !== undefined ) {
      var show_type_element_attribute = jQuery( this_element ).attr( "show_type_element_attribute" );
    }

    //* If no advanced_option_class is found in attribute, we default to 'wp_crm_advanced_configuration' */
    if( !advanced_option_class ) {
      advanced_option_class = ".wp_crm_advanced_configuration";
    }

    //* If element does not have a table row wrapper, we look for the closts .wp_crm_something_advanced_wrapper wrapper */
    if( wrapper.length == 0 ) {
      var wrapper = jQuery( this_element ).parents( '.wp_crm_something_advanced_wrapper' );
    }

    //* get_show_type_value forces the a look up a value of a passed element, ID of which is passed, which is then used as another conditional argument */
    if( show_type_source = jQuery( this_element ).attr( "show_type_source" ) ) {
      var source_element = jQuery( "#" + show_type_source );

      if( source_element ) {
        //* Element found, determine type and get current value */
        if( jQuery( source_element ).is( "select" ) ) {
          show_type = jQuery( "option:selected", source_element ).val();
        }
      }
    }

    if( !show_type ) {
      element_path = jQuery( advanced_option_class, wrapper );
    }

    //** Look for advanced options with show type */
    if( show_type ) {
      element_path = jQuery( advanced_option_class + "[" + show_type_element_attribute + "='"+show_type+"']", wrapper );
    }

    /* Check if this_element element is a checkbox, we assume that we always show things when it is checked, and hiding when unchecked */
    if( jQuery( this_element ).is( "input[type=checkbox]" ) ) {

      var toggle_logic = jQuery( this_element ).attr( "toggle_logic" );

      if( jQuery( this_element ).is( ":checked" ) ) {
        if( toggle_logic == 'reverse' ) {
          jQuery( element_path ).show();
        } else {
          jQuery( element_path ).hide();
        }
      } else {
        if( toggle_logic == 'reverse' ) {
          jQuery( element_path ).hide();
        } else {
          jQuery( element_path ).show();
        }
      }

      return;

    }

    if( triggered_event ) {
      jQuery( element_path ).toggle();
    }

}

