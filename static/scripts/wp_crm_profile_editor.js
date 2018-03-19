if ("undefined" == typeof wp_crm) var wp_crm = {};

if ("undefined" == typeof wp_crm_ui) var wp_crm_ui = {};

jQuery(document).bind("wp_crm_value_changed", function(event, data) {
    var object = data.object, parent = jQuery(object).parents("tr");
    "option_mousedown" != data.action && setTimeout(function() {
        "" == jQuery(object).val() && (jQuery(".input_div", parent).hide(), jQuery(".blank_slate", parent).show());
    }, 100);
}), jQuery(document).ready(function() {
    function wp_crm_standardized_display_name() {
        var option_element, name_rule = wp_crm.display_name_rule, matches = name_rule.match(/\[.*?\]/g), display_name = name_rule, listener_keys = [];
        return jQuery.each(matches, function(key, replacement_key) {
            var meta_value;
            if (meta_key = replacement_key.replace("[", ""), meta_key = meta_key.replace("]", ""), 
            jQuery("[data-crm-slug=" + meta_key + "], [wp_crm_option_for=" + meta_key + "]").length) {
                var element = jQuery("[data-crm-slug=" + meta_key + "]");
                listener_keys.push(element);
                var type = element.get(0).tagName;
                switch (type) {
                  case "INPUT":
                    meta_value = jQuery(element).val();
                    break;

                  case "SELECT":
                    meta_value = jQuery(":selected", element).text();
                }
                jQuery(element).hasClass("has_options") && (option_element = jQuery("[wp_crm_option_for=" + meta_key + "]"), 
                "" != jQuery("option:selected, ", option_element).text() && (meta_value = meta_value + ", " + jQuery("option:selected, ", option_element).text()), 
                listener_keys.push(option_element));
            } else meta_value = "";
            meta_value = jQuery.trim(meta_value), display_name = display_name.replace(replacement_key, meta_value);
        }), jQuery(".wp_crm_display_name_field").val(display_name), jQuery(".wp_crm_display_name_field.wp_crm_uneditable_placeholder").text(display_name), 
        jQuery("h2.wp_crm_page_title").text(display_name), listener_keys;
    }
    if (wp_crm.standardize_display_name) {
        var listener_keys = wp_crm_standardized_display_name();
        jQuery.each(listener_keys, function(i, listener_object) {
            jQuery(document).on("change", listener_object, function() {
                wp_crm_standardized_display_name();
            });
        });
    }
    "function" == typeof jQuery.fn.datepicker && jQuery("input.wpc_date_picker").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: !0,
        changeYear: !0
    }), jQuery(".wp_crm_attribute_uneditable:not(div)").each(function() {
        var value, chkBoxs, placeholder, this_element = jQuery(this), this_class = this_element.attr("class");
        this_element.attr("data-crm-slug");
        this_element.is("select") ? value = this_element.find(":selected").html() : this_element.is(":checkbox") ? (chkBoxs = this_element.closest(".wp_crm_checkbox_list").find("input[type=checkbox]"), 
        chkBoxs.filter(":checked").length > 0 && chkBoxs.filter(":not(:checked)").parent().remove(), 
        value = this_element.is(":checked") ? this_element.siblings("label").html() : "") : (value = this_element.val(), 
        1 == this_element.siblings(".wp_crm_input_options").length && (value += " " + this_element.siblings(".wp_crm_input_options").find(":selected").html(), 
        this_element.siblings(".wp_crm_input_options").remove())), "" == value ? (this_element.removeClass("wp_crm_attribute_uneditable"), 
        this_element.parents(".wp_crm_attribute_uneditable").removeClass("wp_crm_attribute_uneditable")) : (this_element.is(":checkbox") || (placeholder = '<div class="wp_crm_uneditable_placeholder ' + this_class + '">' + value + "</div>", 
        jQuery(placeholder).insertAfter(this_element)), this_element.siblings("input").remove(), 
        this_element.remove());
    }), jQuery(".form-table tr.not_primary").each(function() {
        jQuery("div.wp_checkbox_input", this).length > 0 || "" == jQuery("input,textarea", this).val() && (jQuery(".input_div", this).hide(), 
        jQuery(".blank_slate", this).show());
    }), jQuery(document).on("focusout", "tr.not_primary .wp_crm_input_wrap input,  tr.not_primary .wp_crm_input_wrap textarea", function() {
        var parent = jQuery(this).parents(".wp_crm_input_wrap");
        jQuery("select", parent).length || jQuery(this).trigger("wp_crm_value_changed", {
            object: this,
            action: "input_focusout"
        });
    }), jQuery(".blank_slate").click(function() {
        var parent_row = jQuery(this).closest(".wp_crm_user_entry_row");
        jQuery(".input_div", parent_row).show(), jQuery("input,textarea", parent_row).focus(), 
        jQuery(".blank_slate", parent_row).hide();
    }), jQuery(document).on("keyup", "div.allow_multiple input", function() {
        var parent_row = jQuery(this).closest(".wp_crm_user_entry_row");
        "" != jQuery(this).val() ? jQuery(".add_another", parent_row).show() : jQuery(".add_another", parent_row).hide();
    });
});