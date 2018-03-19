wp_crm_ui.changed_fields = new Array(), "function" != typeof String.prototype.trim && (String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, "");
}), jQuery(document).ready(function() {
    if ("undefined" == typeof wp_crm_dev_mode) var wp_crm_dev_mode = !1;
    !jQuery(".wp_crm_advanced_user_actions.wp-tab-panel").text().trim().length, jQuery("form#crm_user input[ type=text ], form#crm_user input[ type=checkbox ], form#crm_user select").change(function() {
        var this_attribute = jQuery(this).attr("data-crm-slug");
        wp_crm_ui.change_made = !0, wp_crm_ui.changed_fields.push(this_attribute);
    }), jQuery("input.wp_crm_user_email_field").change(function() {
        var obj = this, user_id = jQuery("#user_id").val();
        jQuery(obj).removeClass("wp_crm_input_error").parent().removeClass("wp_crm_input_error"), 
        jQuery(obj).removeClass("email_validated").parent().removeClass("email_validated"), 
        jQuery("span.error", jQuery(obj).parent()).remove(), jQuery(obj).val() ? (jQuery(obj).attr("disabled", obj).parent().addClass("email_validating"), 
        jQuery.post(ajaxurl, {
            action: "wp_crm_check_email_for_duplicates",
            email: jQuery(obj).val(),
            user_id: user_id
        }, function(response) {
            jQuery(obj).removeClass("wp_crm_input_error").parent().removeClass("wp_crm_input_error"), 
            "Ok" == response ? jQuery(obj).addClass("email_validated").parent().addClass("email_validated") : jQuery(obj).addClass("wp_crm_input_error").parent().addClass("wp_crm_input_error").append("<span class='error'>" + response + "</span>"), 
            jQuery(obj).attr("disabled", !1).parent().removeClass("email_validating");
        })) : jQuery(obj).addClass("email_validated").parent().addClass("email_validated");
    }), jQuery("form#crm_user").submit(function(form) {
        return wp_crm_save_user_form(form);
    }), jQuery("ul.wp-tab-panel-nav a").click(function() {
        var panel_wrapper = jQuery(this).parents(".wp-tab-panel-wrapper"), t = jQuery(this).attr("href");
        return jQuery(this).parent().addClass("tabs").siblings("li").removeClass("tabs"), 
        jQuery(".wp-tab-panel", panel_wrapper).hide(), jQuery(t, panel_wrapper).show(), 
        !1;
    }), jQuery(".submitdelete").click(function() {
        return confirm("Are you sure you want to delete user?");
    }), jQuery("div.wp-crm-toggle-action").click(function() {
        var _toggle_target = jQuery(this).data("toggle-target");
        jQuery(_toggle_target).toggle();
    }), jQuery("div.wp_crm_toggle_advanced_user_actions").click(function() {
        jQuery("div.wp_crm_advanced_user_actions").toggle();
    }), jQuery(document).on("mousedown", "tr.not_primary .wp_crm_input_wrap select,  tr.not_primary .wp_crm_input_wrap select", function() {
        jQuery(this).trigger("wp_crm_value_changed", {
            object: this,
            action: "option_mousedown"
        });
    }), jQuery(".wp_crm_truncated_show_hidden").click(function() {
        var parent = jQuery(this).parent();
        jQuery(".truncated_content:first", parent).toggle();
    }), jQuery(".wp_crm_show_message_options").click(function() {
        jQuery(".wp_crm_message_options").toggle();
    }), jQuery(".wp_crm_toggle_message_entry").click(function() {
        jQuery(".wp_crm_new_message").toggle(), jQuery(".wp_crm_new_message #wp_crm_message_content").focus();
    }), jQuery("#wp_crm_role").change(function() {
        jQuery(".wp_crm_user_entry_row").show();
        var new_setting = jQuery("option:selected", this).val();
        jQuery(wp_crm.hidden_attributes[new_setting]).each(function(index, value) {
            jQuery("tr.wp_crm_" + value + "_row").hide();
        });
    }), jQuery("#wp_crm_add_message").click(function() {
        wp_crm_save_stream_message();
    }), jQuery(document).on("click", ".add_another", function() {
        var parent_row = jQuery(this).closest(".wp_crm_user_entry_row"), input_div = jQuery(".input_div:last", parent_row), new_input_div = input_div.clone();
        jQuery("input", new_input_div).val("");
        var current_hash = jQuery("input", new_input_div).attr("data-random-hash"), new_hash = Math.floor(9999 * Math.random()) + 1e3;
        if (jQuery("input", new_input_div).length) {
            jQuery("input", new_input_div).attr("data-random-hash", new_hash);
            var old_name = jQuery("input", new_input_div).attr("name");
            jQuery("input", new_input_div).attr("name", old_name.replace(current_hash, new_hash));
        }
        if (jQuery("select", new_input_div).length) {
            jQuery("select", new_input_div).attr("data-random-hash", new_hash);
            var old_name = jQuery("select", new_input_div).attr("name");
            jQuery("select", new_input_div).attr("name", old_name.replace(current_hash, new_hash));
        }
        jQuery(new_input_div).insertAfter(input_div), jQuery(this).hide();
    }), jQuery("#crm_user_activity_filter input").change(function() {
        wp_crm_update_activity_stream();
    }), jQuery("#crm_new_invioce").click(function() {
        window.location.href = jQuery(this).data("gotourl");
    });
});