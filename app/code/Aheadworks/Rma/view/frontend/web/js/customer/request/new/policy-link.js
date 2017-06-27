/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'mage/translate',
    'jquery/ui'
], function($, $t) {
    'use strict';

    $.widget("awrma.awRmaPolicyLink", {
        options: {
        },
        _create: function() {
            this._bind();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.element).on('click', $.proxy(this.onClick, this));
        },
        _unbind: function() {
            $(this.element).off('click');
        },
        onClick: function (event) {
            var popup = $(this.options.popup).dialog({
                dialogClass: "aw-rma-policy",
                width: $(window).width() * 0.9,
                height: $(window).height() * 0.9,
                position: {my: "center", at: "center", of: window},
                modal: true,
                buttons: [
                    {text: $t("OK"), click: function() {$(popup).dialog("close");}}
                ]
            });
            $('.ui-widget-overlay').one('click', function(event) {
                $(popup).dialog("close");
            });
            event.preventDefault();
        }
    });

    return $.awrma.awRmaPolicyLink;
});