/**
 * Scripts related to CRM Profile form editing and validation.
 * This functionality is for manipulating the UI rendered by WPP_F::user_input_field();
 *
 * Loaded globally on admin by default.
 * This file may be loaded on the front-end. Will call wp_crm_global.js if loaded via wp_enqueue_script()
 *
 *
 */



if(typeof wp_crm == 'undefined') {
  var wp_crm = {};
}

if(typeof wp_crm_ui == 'undefined') {
  var wp_crm_ui = {};
}


/**
 * Ran when user attempt to leave profile with unsaved data.
 *
 * @todo This is actually triggered on focusout, so semantically inaccurate.
 *
 */
jQuery(document).bind('wp_crm_value_changed', function(event, data) {

  var object = data.object;
  var parent = jQuery(object).parents('tr');

  if(data.action == 'option_mousedown'){
    return;
  }

  /** We need to use timeout here because value of field sometimes is not updated in time. E.g.: datepicker's field */
  setTimeout(function(){
    if(jQuery(object).val() == '') {
      jQuery('.input_div', parent).hide();
      jQuery('.blank_slate', parent).show();
    }
  }, 100);

});

jQuery(document).ready(function() {

  if(wp_crm.standardize_display_name) {
    var listener_keys = wp_crm_standardized_display_name();

    jQuery.each(listener_keys, function(i, listener_object) {

        jQuery(listener_object).live("change", function() {
          wp_crm_standardized_display_name();
        });


    });
  }

  if(typeof jQuery.fn.datepicker == 'function') {
    jQuery('input.wpc_date_picker').datepicker({
      dateFormat: 'yy-mm-dd',
      changeMonth: true,
      changeYear: true
    });
  }


  /* Convert a uneditable element into something used for display only */
  jQuery('.wp_crm_attribute_uneditable:not(div)').each(function() {

    var this_element = this;
    var this_class = jQuery(this_element).attr("class");
    var this_attribute = jQuery(this_element).attr("wp_crm_slug");
    var value = jQuery(this_element).val();
    var placeholder;

    jQuery(this_element).attr("readonly", true);
    jQuery(this_element).hide();

    placeholder = "<div class=\"wp_crm_uneditable_placeholder " + this_class + "\">" + value + "</div>";

    jQuery(placeholder).insertAfter(this_element);


  });

  jQuery('.form-table tr.not_primary').each(function() {

    /* Don't hide anything for checkboxes */
    if(jQuery('div.wp_checkbox_input', this).length > 0) {
      return;
    }

    if(jQuery('input,textarea', this).val() == '') {
      jQuery('.input_div', this).hide();
      jQuery('.blank_slate', this).show();
    }

  });

  jQuery('tr.not_primary .wp_crm_input_wrap input,  tr.not_primary .wp_crm_input_wrap textarea').live('focusout', function() {
    var parent = jQuery(this).parents('.wp_crm_input_wrap');

    if(jQuery('select', parent).length) {
      /*  Don't hide element if there's a select field in there */
      return;
    }

    jQuery(this).trigger('wp_crm_value_changed', {object: this, action: 'input_focusout'});

  });

  jQuery('.blank_slate').click(function() {
    var parent_row = jQuery(this).closest(".wp_crm_user_entry_row");

    jQuery('.input_div', parent_row).show();
    jQuery('input,textarea', parent_row).focus();
    jQuery('.blank_slate', parent_row).hide();

  });

  /**
   * Shows "Add Another" element to additional profile fields can be edited when a multi-edit field is modified
   *
   */
  jQuery('div.allow_multiple input').live("keyup", function() {
    var parent_row =  jQuery(this).closest(".wp_crm_user_entry_row");

    if(jQuery(this).val() != '') {
      jQuery('.add_another', parent_row).show();
    } else {
      jQuery('.add_another', parent_row).hide();
    }

  });


  /**
   * Handles display name standardization
   *
   * @todo Need to add check to make sure last character is not a comma, or dash, and remove any other characters that result from not having a matched value
   *
   */
  function wp_crm_standardized_display_name() {
    var name_rule = wp_crm.display_name_rule;
    var matches = name_rule.match(/\[.*?\]/g);
    var display_name = name_rule;
    var option_element;
    var listener_keys = [];

    jQuery.each(matches, function(key, replacement_key) {
      var meta_value;

      meta_key = replacement_key.replace('[', '');
      meta_key = meta_key.replace(']', '');

      if(jQuery("[wp_crm_slug=" + meta_key + "], [wp_crm_option_for=" + meta_key + "]").length) {

        var element = jQuery("[wp_crm_slug=" + meta_key + "]");

        listener_keys.push(element);

        var type = element.get(0).tagName;

        switch (type) {

          case 'INPUT':
            meta_value = jQuery(element).val();
          break;

          case 'SELECT':
            meta_value = jQuery(":selected", element).text();
          break;

        }

        /* Check if options exist and is not empty, then add to listener. */
        if(jQuery(element).hasClass("has_options")) {
          option_element = jQuery("[wp_crm_option_for=" + meta_key + "]");

          if(jQuery("option:selected, ", option_element).text() != "") {
            meta_value = meta_value + ', ' + jQuery("option:selected, ", option_element).text();
          }

          listener_keys.push(option_element)
        }

      } else {
        meta_value = '';
      }

      /* Clean up name to remove any orphaned characters */
      meta_value = jQuery.trim(meta_value);

      display_name = display_name.replace(replacement_key,meta_value);

    });

    jQuery(".wp_crm_display_name_field").val(display_name);
    jQuery(".wp_crm_display_name_field.wp_crm_uneditable_placeholder").text(display_name);
    jQuery("h2.wp_crm_page_title").text(display_name);

    return listener_keys;

  }



});
