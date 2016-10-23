jQuery(function() {
    jQuery.widget("custom.combobox", {
        _create: function() {
            this.wrapper = jQuery("<span>").addClass("custom-combobox").insertAfter(this.element), 
            this.element.hide(), this._createAutocomplete(), this._createShowAllButton();
        },
        _createAutocomplete: function() {
            var _this = this, selected = this.element.children(":selected");
            selected.val() ? selected.text() : "";
            this.input = jQuery("<input>").appendTo(this.wrapper).attr("title", "").attr("placeholder", this.element.attr("data-placeholder")).addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left").autocomplete({
                delay: 0,
                minLength: 0,
                source: jQuery.proxy(this, "_source")
            }), this._on(this.input, {
                autocompleteselect: function(event, ui) {
                    return ui.item.option.selected = !0, _this.input.val(ui.item.label), this._trigger("select", event, {
                        item: ui.item.option
                    }), !1;
                },
                autocompletechange: "_removeIfInvalid"
            });
        },
        _createShowAllButton: function() {
            var input = this.input, wasOpen = !1;
            jQuery("<a>").attr("tabIndex", -1).appendTo(this.wrapper).removeClass("ui-corner-all").addClass("ui-button ui-widget ui-button-icon-only custom-combobox-toggle ui-corner-right").html('<span class="ui-button-icon ui-icon ui-icon-triangle-1-s"></span><span class="ui-button-icon-space"> </span>').on("mousedown", function() {
                wasOpen = input.autocomplete("widget").is(":visible");
            }).on("click", function() {
                input.trigger("focus"), wasOpen || input.autocomplete("search", "");
            });
        },
        _source: function(request, response) {
            var matcher = new RegExp(jQuery.ui.autocomplete.escapeRegex(request.term), "i");
            response(this.element.children("option").map(function() {
                var text = jQuery(this).text(), val = jQuery(this).val();
                return !this.value || request.term && !matcher.test(text) ? void 0 : {
                    label: text,
                    value: val,
                    option: this
                };
            }));
        },
        _removeIfInvalid: function(event, ui) {
            if (!ui.item) {
                var value = this.input.val(), valueLowerCase = value.toLowerCase(), valid = !1;
                this.element.children("option").each(function() {
                    return jQuery(this).text().toLowerCase() === valueLowerCase ? (this.selected = valid = !0, 
                    !1) : void 0;
                }), valid || (this.input.val(""), this.element.val(""), this.input.autocomplete("instance").term = "");
            }
        },
        _destroy: function() {
            this.wrapper.remove(), this.element.show();
        }
    });
}), jQuery(document).ready(function($) {
    jQuery(".combobox").combobox();
    var cache = {};
    $("#wp_crm_text_search").autocomplete({
        minLength: 2,
        source: function(request, response) {
            var term = request.term;
            request.action = "wp_crm_user_search_network", $.getJSON(ajaxurl, request, function(data, status, xhr) {
                cache[term] = data, response(data.map(function(item) {
                    return {
                        label: item.display_name,
                        option: item
                    };
                }));
            });
        }
    });
});