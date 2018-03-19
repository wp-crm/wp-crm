function crm_recaptcha_onload(argument) {
    jQuery(".crm-g-recaptcha").each(function(argument) {
        var container = jQuery(this), formID = container.parents("form").attr("id"), callback = formID + "_recaptcha_cb", expiredCallback = formID + "_expired_recaptcha_cb", parameters = {
            sitekey: container.data("sitekey"),
            tabindex: container.data("tabindex"),
            callback: callback,
            "expired-callback": expiredCallback
        };
        window[formID + "_recaptcha"] = grecaptcha.render(this, parameters);
    });
}

function wp_crm_create_slug(slug) {
    return slug = slug.replace(/[^a-zA-Z0-9_\s]/g, ""), slug = slug.toLowerCase(), slug = slug.replace(/\s/g, "_");
}

function wp_crm_save_stream_message() {
    var user_id = wp_crm.user_id, wp_crm_message_content = jQuery("#wp_crm_message_content").val(), wp_crm_message_type = jQuery("#wp_crm_message_type").val();
    "" != wp_crm_message_content && jQuery.post(ajaxurl, {
        action: "wp_crm_insert_activity_message",
        time: jQuery(".wp_crm_message_options .datepicker").val(),
        content: wp_crm_message_content,
        user_id: user_id,
        message_type: wp_crm_message_type
    }, function(response) {
        "true" == response.success ? (jQuery("#wp_crm_user_activity_stream").slideUp("fast"), 
        wp_crm_update_activity_stream({
            filter_types: !1
        }), jQuery("#wp_crm_message_content").val(""), jQuery(".wp_crm_new_message").slideUp("fast")) : alert("Could not save entry");
    }, "json");
}

function wp_crm_handle_unload() {
    var changed_fields = wp_crm_ui.changed_fields;
    return changed_fields.length ? !0 : !1;
}

function wp_crm_refresh_random_keys(element) {
    if (jQuery(element).attr("data-random-hash")) {
        var old_hash = jQuery(element).attr("data-random-hash"), new_hash = Math.floor(1e7 * Math.random()), current_html = jQuery(element).html();
        old_hash = new RegExp(old_hash, "gi");
        var new_html = current_html.replace(old_hash, new_hash);
        jQuery(element).html(new_html), jQuery(element).attr("data-random-hash", new_hash);
    }
}

function wp_crm_update_activity_stream(params) {
    var obj = jQuery("#crm_user_activity_filter :input").first(), parent = jQuery(obj).closest("#crm_user_activity_filter"), msglist = jQuery(".wp_crm_stream_status.wp_crm_load_more_stream"), filter_types_visible = jQuery.map(jQuery(":input", parent).filter(":checked"), function(e, i) {
        return obj = {
            attribute: jQuery(e).attr("attribute"),
            other: jQuery(e).attr("other"),
            hidden: !1
        };
    }), filter_types_hidden = jQuery.map(jQuery(":input", parent).filter(":not( :checked )"), function(e, i) {
        return obj = {
            attribute: jQuery(e).attr("attribute"),
            other: jQuery(e).attr("other"),
            hidden: !0
        };
    }), params = jQuery.extend(!0, {
        action: "wp_crm_get_user_activity_stream",
        user_id: jQuery("#user_id").val(),
        limited_messages: jQuery(msglist).attr("limited_messages"),
        filter_types: filter_types_visible.concat(filter_types_hidden)
    }, params);
    jQuery("#user_activity_history .loading").show(), jQuery.post(ajaxurl, params, function(response) {
        if (jQuery("#user_activity_history .loading").hide(), jQuery("#wp_crm_user_activity_stream tbody").html(response.tbody), 
        response.more_per_page && (jQuery("#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream").attr("per_page", response.per_page), 
        jQuery("#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream span.more_count").html(response.more_per_page)), 
        "undefined" != typeof response.total_count) {
            var total = response.total_count;
            jQuery("#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream").attr("all_messages", total);
        } else var total = jQuery("#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream .wp_crm_counts .total_count").html();
        response.current_count >= total ? jQuery("#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream").hide() : jQuery("#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream").show(), 
        jQuery("#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream .wp_crm_counts .current_count").html(response.current_count), 
        jQuery("#user_activity_history .wp_crm_stream_status.wp_crm_load_more_stream .wp_crm_counts .total_count").html(total), 
        jQuery("#wp_crm_user_activity_stream").slideDown("fast");
    }, "json");
}

function wp_crm_save_user_form(form) {
    var form = jQuery("#crm_user"), stop_form = !1, password_1 = jQuery("#wp_crm_password_1").val(), password_2 = jQuery("#wp_crm_password_2").val();
    return wp_crm_developer_log("wp_crm_save_user_form() initiated."), wp_crm_developer_log("Required fields found: " + jQuery(".wp_crm_required_field", form).length), 
    jQuery("*", form).removeClass("wp_crm_input_error"), jQuery(".wp_crm_required_field", form).each(function() {
        var meta_key = jQuery(this).attr("meta_key"), input_type = jQuery(this).attr("wp_crm_input_type"), has_options = jQuery(this).hasClass("wp_crm_has_options") ? !0 : !1;
        wp_crm_developer_log({
            meta_key: meta_key,
            has_options: has_options,
            input_type: input_type
        }), ("text" == input_type || "date" == input_type) && ("" == jQuery("input.regular-text:first", this).val() && (jQuery("input.regular-text:first", this).addClass("wp_crm_input_error"), 
        jQuery("input.regular-text:first", this).focus(), jQuery(".blank_slate", this).hide(), 
        jQuery(".input_div", this).show(), stop_form = !0), has_options && "" == jQuery("select.wp_crm_input_options:first", this).val() && (jQuery("select.wp_crm_input_options:first", this).addClass("wp_crm_input_error"), 
        jQuery("select.wp_crm_input_options:first", this).focus(), stop_form = !0)), "textarea" == input_type && ("" == jQuery("textarea.wp_crm_required_field:first", this).val() && (jQuery("textarea.wp_crm_required_field:first", this).addClass("wp_crm_input_error"), 
        jQuery("textarea.wp_crm_required_field:first", this).focus(), jQuery(".blank_slate", this).hide(), 
        jQuery(".input_div", this).show(), stop_form = !0), has_options && "" == jQuery("select.wp_crm_input_options:first", this).val() && (jQuery("select.wp_crm_input_options:first", this).addClass("wp_crm_input_error"), 
        jQuery("select.wp_crm_input_options:first", this).focus(), stop_form = !0)), "dropdown" == input_type && "" == jQuery("select:first", this).val() && (jQuery("select:first", this).addClass("wp_crm_input_error"), 
        jQuery("select:first", this).focus(), jQuery(".blank_slate", this).hide(), jQuery(".input_div", this).show(), 
        stop_form = !0);
    }), jQuery("input.wp_crm_user_email_field:not(.email_validated)", form).length && (jQuery("input.wp_crm_user_email_field:not(.email_validated):first", form).addClass("wp_crm_input_error"), 
    jQuery("input.wp_crm_user_email_field:not(.email_validated):first", form).focus(), 
    jQuery(".blank_slate", form).hide(), jQuery(".input_div", form).show(), stop_form = !0), 
    stop_form ? !1 : "" != password_1 && password_1 != password_2 ? (jQuery(".wp_crm_advanced_user_actions").show(), 
    jQuery("#wp_crm_password_1").focus(), !1) : (jQuery(document).trigger("wp_crm_user_profile_save", {
        object: this,
        action: "option_mousedown"
    }), wpp_crm_form_stop ? !1 : !0);
}

function wp_crm_developer_log(m) {
    return "undefined" == typeof wp_crm_dev_mode ? !1 : void ("object" == typeof console && "function" == typeof console.log && console.log(m));
}

function wp_crm_handle_default_values(this_element, triggered_event) {
    var current_value = jQuery(this_element).val(), default_value = jQuery(this_element).attr("default_value");
    triggered_event = "object" != typeof triggered_event ? !1 : !0, "" != default_value && ("" == current_value || current_value == default_value ? (jQuery(this_element).val(default_value), 
    jQuery(this_element).addClass("wp_crm_forced_default_value"), triggered_event && jQuery('<span class="wp_crm_input_quick_fade">Default set.</span>').insertAfter(this_element).fadeOut(1e3)) : (jQuery(this_element).val(current_value), 
    jQuery(this_element).removeClass("wp_crm_forced_default_value")));
}

function wp_crm_toggle_advanced_options(this_element, triggered_event) {
    var advanced_option_class = !1, show_type = !1, show_type_element_attribute = !1;
    triggered_event = "object" != typeof triggered_event ? !1 : !0;
    var wrapper = jQuery(this_element).attr("wrapper") ? jQuery(this_element).closest("." + jQuery(this_element).attr("wrapper")) : jQuery(this_element).parents(".wp_crm_dynamic_table_row");
    if (void 0 !== jQuery(this_element).attr("advanced_option_class")) var advanced_option_class = "." + jQuery(this_element).attr("advanced_option_class");
    if (void 0 !== jQuery(this_element).attr("show_type_element_attribute")) var show_type_element_attribute = jQuery(this_element).attr("show_type_element_attribute");
    if (advanced_option_class || (advanced_option_class = ".wp_crm_advanced_configuration"), 
    0 == wrapper.length) var wrapper = jQuery(this_element).parents(".wp_crm_something_advanced_wrapper");
    if (show_type_source = jQuery(this_element).attr("show_type_source")) {
        var source_element = jQuery("#" + show_type_source);
        source_element && jQuery(source_element).is("select") && (show_type = jQuery("option:selected", source_element).val());
    }
    if (show_type || (element_path = jQuery(advanced_option_class, wrapper)), show_type && (element_path = jQuery(advanced_option_class + "[" + show_type_element_attribute + "='" + show_type + "']", wrapper)), 
    jQuery(this_element).is("input[type=checkbox]")) {
        var toggle_logic = jQuery(this_element).attr("toggle_logic");
        return void (jQuery(this_element).is(":checked") ? "reverse" == toggle_logic ? jQuery(element_path).show() : jQuery(element_path).hide() : "reverse" == toggle_logic ? jQuery(element_path).hide() : jQuery(element_path).show());
    }
    triggered_event && jQuery(element_path).toggle();
}

var wp_crm_ui = {}, wpp_crm_form_stop = !1;

jQuery(document).ready(function($) {
    if ("undefined" == typeof wp_crm_dev_mode) var wp_crm_dev_mode = !1;
    jQuery(".wpc_file_upload").on("click", function(event) {
        var _this = jQuery(this);
        event.preventDefault();
        var media_uploader = null;
        media_uploader = new wp.media({
            title: "Select File",
            button: {
                text: "Select File"
            },
            multiple: !1
        }), media_uploader.on("select", function() {
            var data = media_uploader.state().get("selection").first().toJSON();
            _this.siblings("input").val(data.url), media_uploader.off("insert"), media_uploader.off("select"), 
            delete media_uploader;
        }), media_uploader.open();
    }), jQuery("#wp_crm_clear_cache").on("click", function(e) {
        e.preventDefault();
        var $this = jQuery(this), msgHolder = jQuery("#clear_cache_status").html("").fadeOut().removeClass(), $dots = $this.find(".dots"), dots = 0, dotting = function() {
            0 == dots ? ($dots.html(".&nbsp;&nbsp;"), dots++) : 1 == dots ? ($dots.html("..&nbsp;"), 
            dots++) : 2 == dots ? ($dots.html("..."), dots++) : ($dots.html("&nbsp;&nbsp;&nbsp;"), 
            dots = 0);
        }, timer = setInterval(dotting, 600);
        return $this.attr("disabled", "disabled"), jQuery.post(ajaxurl, {
            action: "wpc_ajax_clear_cache"
        }, function(data) {
            msgHolder.html(data).addClass("updated").fadeIn();
        }).fail(function() {
            msgHolder.html(wpc.strings.something_wrong).addClass("error").fadeIn();
        }).always(function() {
            clearInterval(timer), $dots.html(""), $this.removeAttr("disabled");
        }), !1;
    }), jQuery(".wp_crm_show_advanced").each(function() {
        wp_crm_toggle_advanced_options(this);
    }), jQuery(document).on("click", ".wp_crm_show_advanced", function(event) {
        wp_crm_toggle_advanced_options(this, event);
    }), jQuery(document).on("click", ".wp_crm_cancel_ajax_action", function() {
        var ajax_wrapper = jQuery(this).parents(".wpc_action_wrapper");
        ajax_wrapper.remove();
    }), jQuery(document).on("click", "label.wpc_closest", function() {
        var parent = jQuery(this).closest("li"), element = jQuery("input[type=checkbox]", parent);
        element.is(":checked") ? element.removeAttr("checked") : element.attr("checked", "checked");
    }), jQuery(document).on("click", ".wp_crm_toggle", function() {
        jQuery("." + jQuery(this).attr("toggle")).toggle();
    }), jQuery(document).on("click", ".wp_crm_message_quick_action", function() {
        var action = jQuery(this).attr("wp_crm_action"), object_id = jQuery(this).attr("object_id"), instant_hide = jQuery(this).attr("instant_hide"), parent_element = jQuery(this).parents("tr");
        return jQuery(this).attr("verify_action") && !confirm("Are you sure?") ? !1 : ("true" == instant_hide && jQuery(parent_element).hide(), 
        void jQuery.post(ajaxurl, {
            action: "wp_crm_quick_action",
            wp_crm_quick_action: action,
            object_id: object_id
        }, function(result) {
            switch (result.success = "true", result.action) {
              case "hide_element":
                jQuery(parent_element).hide();
            }
        }, "json"));
    }), jQuery(document).on("click", ".wp_crm_add_row", function() {
        var table = jQuery(this).parents(".ud_ui_dynamic_table"), cloned = (jQuery(table).attr("id"), 
        jQuery(".wp_crm_dynamic_table_row:last", table).clone());
        jQuery(cloned).children().each(function(i, e) {
            if (jQuery("ul", e).length > 0) {
                var liEl = jQuery("ul", e).children();
                liEl.length > 0 && liEl.each(function(i, e) {
                    var label = jQuery("label", e), input = jQuery("input:eq(0)", e);
                    if (label.length > 0 && input.length > 0) {
                        var attrFor = "undefined" != label.attr("for") ? label.attr("for") : "", attrId = "undefined" != input.attr("id") ? input.attr("id") : "";
                        if (console.log(typeof attrFor), "" != attrFor && "" != attrId) {
                            var rand = Math.floor(1e4 * Math.random());
                            label.attr("for", "new_field_" + rand), input.attr("id", "new_field_" + rand);
                        }
                    }
                });
            }
        }), jQuery(cloned).appendTo(table);
        var added_row = jQuery(".wp_crm_dynamic_table_row:last", table);
        jQuery(added_row).show(), jQuery("input[type=text]:not(.wp-crm-field)", added_row).val("").removeAttr("disabled"), 
        jQuery("input[type=checkbox]", added_row).attr("checked", !1).removeAttr("disabled"), 
        jQuery("textarea", added_row).val("").show().removeAttr("disabled"), jQuery("select", added_row).val("").removeAttr("disabled"), 
        jQuery(".ace_editor", added_row).remove(), jQuery("button", added_row).removeAttr("disabled"), 
        jQuery("input[type=text].wp-crm-field", added_row).prop("readonly", !0), jQuery(".wp-crm-field-edit", added_row).show(), 
        jQuery(added_row).attr("new_row", "true"), jQuery(".slug_setter", added_row).focus();
    }), jQuery(document).on("change", ".wp_crm_dynamic_table_row[new_row=true] input.slug_setter", function() {
        var this_row = jQuery(this).parents("tr.wp_crm_dynamic_table_row"), old_slug = jQuery(this_row).attr("slug"), new_slug = jQuery(this).val(), new_slug = wp_crm_create_slug(new_slug);
        if ("" != new_slug) {
            var samename = jQuery(".wp_crm_dynamic_table_row[new_row=false][slug=" + new_slug + "]").length;
            if (samename) {
                var rand = Math.floor(1e4 * Math.random());
                new_slug += rand;
            }
            jQuery(".slug", this_row).val(new_slug), jQuery(this_row).attr("slug", new_slug), 
            jQuery("input,select,textarea", this_row).each(function(element) {
                var old_name = jQuery(this).attr("name");
                if ("undefined" != typeof old_name) {
                    var new_name = old_name.replace(old_slug, new_slug);
                    if (jQuery(this).attr("id")) var old_id = jQuery(this).attr("id"), new_id = old_id.replace(old_slug, new_slug);
                    jQuery(this).attr("name", new_name), jQuery(this).attr("id", new_id);
                }
            }), jQuery("label", this_row).each(function(element) {
                if (jQuery(this).attr("for")) {
                    var old_for = jQuery(this).attr("for"), new_for = old_for.replace(old_slug, new_slug);
                    jQuery(this).attr("for", new_for);
                }
            });
        }
    }), jQuery(document).on("click", ".wp_crm_delete_row", function() {
        var parent = jQuery(this).parents("tr.wp_crm_dynamic_table_row"), row_count = jQuery(".wp_crm_delete_row:visible").length;
        jQuery("input[type=text]", parent).val(""), jQuery("textarea", parent).val(""), 
        jQuery("input[type=checkbox]", parent).attr("checked", !1), jQuery(parent).attr("new_row", "true"), 
        row_count > 1 && (jQuery(parent).hide(), jQuery(parent).remove());
    }), jQuery(".wp_crm_overview_filters .all").click(function() {
        jQuery(this).find("input").attr("checked") && jQuery(this).siblings("li").find("input").removeAttr("checked");
    }), jQuery(".wp_crm_role_list").change(function() {
        jQuery(".wp_crm_overview_filters .all").find("input").removeAttr("checked");
    }), jQuery(".wpp_crm_filter_section_title").click(function() {
        var parent = jQuery(this).parents(".wp_crm_overview_filters");
        jQuery(" .wp_crm_checkbox_filter", parent).slideToggle("fast", function() {
            "none" == jQuery(this).css("display") ? jQuery(".wpp_crm_filter_show", parent).html(wpc.strings.filter_show) : jQuery(".wpp_crm_filter_show", parent).html(wpc.strings.filter_hide);
        });
    }), jQuery(".open-help-tab").on("click", function(event) {
        event.preventDefault();
        var panel = jQuery("#" + jQuery(this).attr("aria-controls")), button = jQuery("#screen-meta-links").find(".show-settings");
        return screenMeta.open(panel, button), !1;
    });
}), jQuery(document).on("click", ".wp_crm_load_more_stream", function() {
    var params = {
        per_page: jQuery(this).attr("per_page"),
        all_messages: jQuery(this).attr("all_messages"),
        more_per_page: jQuery(this).attr("limited_messages"),
        limited_messages: jQuery(this).attr("limited_messages")
    };
    wp_crm_update_activity_stream(params);
});