jQuery(document).ready(function() {
    jQuery("#wp_crm_wp_crm_contact_system_data").on("click", ".wp-crm-field-edit", function() {
        var _parent = jQuery(this).parent(".wp-crm-editable-item"), _field = jQuery(".wp-crm-field", _parent);
        _field.prop("readonly", !1), _field.focus(), jQuery(this).hide(), jQuery(".wp-crm-field-save", _parent).show(), 
        jQuery(".wp-crm-field-changed-value", _parent).val("true");
    }), jQuery("#wp_crm_wp_crm_contact_system_data").on("click", ".wp-crm-field-save", function() {
        var _parent = jQuery(this).parent(".wp-crm-editable-item"), _field = jQuery(".wp-crm-field", _parent);
        _field.prop("readonly", !0), jQuery(this).hide(), jQuery(".wp-crm-field-edit", _parent).show();
    }), jQuery(".wp-crm-form-attributes").sortable({
        handle: ".wp-crm-handle",
        containment: "parent",
        classes: {
            "ui-sortable": "highlight"
        }
    }), jQuery(document).on("change", ".wp_crm_force_default", function(event) {
        wp_crm_handle_default_values(this, event);
    }), jQuery(".wp_crm_force_default").each(function() {
        wp_crm_handle_default_values(this);
    }), jQuery(document).on("change", ".wp_crm_trigger_action", function() {
        return;
    }), jQuery("#wp_crm_attribute_fields tbody").sortable({
        delay: 50
    }), jQuery("#wp_crm_show_settings_array, #wp_crm_show_settings_array_cancel").click(function() {
        jQuery("#wp_crm_show_settings_array_result").is(":visible") ? (jQuery("#wp_crm_show_settings_array_cancel").hide(), 
        jQuery("#wp_crm_show_settings_array_result").hide()) : (jQuery("#wp_crm_show_settings_array_cancel").show(), 
        jQuery("#wp_crm_show_settings_array_result").show());
    }), jQuery(".wp_crm_toggle_something").click(function() {
        var settings_block = jQuery(this).parents(".wp_crm_settings_block");
        jQuery(".wp_crm_class_pre", settings_block).is(":visible") ? (jQuery(".wp_crm_class_pre", settings_block).hide(), 
        jQuery(".wp_crm_link", settings_block).hide()) : (jQuery(".wp_crm_class_pre", settings_block).show(), 
        jQuery(".wp_crm_link", settings_block).show());
    }), jQuery("#wp_crm_show_user_object").click(function() {
        var settings_block = jQuery(this).parents(".wp_crm_settings_block");
        jQuery.post(ajaxurl, {
            action: "wp_crm_user_object",
            user_id: jQuery("#wp_crm_user_id").val()
        }, function(result) {
            jQuery(".wp_crm_class_pre", settings_block).show(), jQuery(".wp_crm_class_pre", settings_block).text(result);
        });
    }), jQuery("#wp_crm_generate_fake_users").click(function() {
        var number_of_users = jQuery("#wp_crm_fake_users").val();
        if (confirm("Are you sure you want to generate " + number_of_users + " user(s)?")) {
            var settings_block = jQuery(this).parents(".wp_crm_settings_block");
            jQuery.post(ajaxurl, {
                action: "wp_crm_do_fake_users",
                number: number_of_users,
                do_what: "generate"
            }, function(result) {
                jQuery(".wp_crm_class_pre", settings_block).show(), jQuery(".wp_crm_class_pre", settings_block).text(result);
            });
        }
    }), jQuery("#wp_crm_delete_fake_users").click(function(event) {
        if (event.preventDefault(), confirm("Are you sure you want to delete ALL fake users you have generated?\nNone of the fake user information will not be reassigned, but completely removed.")) {
            var settings_block = jQuery(this).parents(".wp_crm_settings_block");
            jQuery.post(ajaxurl, {
                action: "wp_crm_do_fake_users",
                do_what: "remove"
            }, function(result) {
                jQuery(".wp_crm_class_pre", settings_block).show(), jQuery(".wp_crm_class_pre", settings_block).text(result);
            });
        }
    }), jQuery("#wp_crm_show_meta_report").click(function() {
        var settings_block = jQuery(this).parents(".wp_crm_settings_block");
        jQuery.post(ajaxurl, {
            action: "wp_crm_show_meta_report"
        }, function(result) {
            jQuery(".wp_crm_class_pre", settings_block).show(), jQuery(".wp_crm_class_pre", settings_block).text(result);
        });
    }), jQuery("form#wp_crm_settings").submit(function(form) {
        var error_field = {
            object: !1,
            tab_index: !1
        };
        return jQuery("form#wp_crm_settings :input[validation_required=true],form#wp_crm_settings .wp_crm_required_field :input,form#wp_crm_settings :input[required],form#wp_crm_settings :input.slug_setter").each(function() {
            return jQuery(this).val() ? void 0 : (error_field.object = this, error_field.tab_index = jQuery('#wp_crm_settings_tabs a[href="#' + jQuery(error_field.object).closest(".ui-tabs-panel").attr("id") + '"]').parent().index(), 
            !1);
        }), 0 != error_field.object ? ("undefined" != typeof error_field.tab_index && jQuery("#wp_crm_settings_tabs").tabs("select", error_field.tab_index), 
        jQuery(error_field.object).addClass("ui-state-error").one("keyup", function() {
            jQuery(this).removeClass("ui-state-error");
        }), jQuery(error_field.object).focus(), !1) : void 0;
    });
});