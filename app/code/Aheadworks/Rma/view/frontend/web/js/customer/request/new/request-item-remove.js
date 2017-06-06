/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'jquery/ui'
], function($) {
    'use strict';

    $.widget("awrma.awRmaRequestItemRemove", {
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
            var itemDetails = $(event.target).closest('form').find('.item-details');
            if (itemDetails.length > 1) {
                var itemDetails = itemDetails.filter('[data-item=' + $(event.target).data('item') + ']');
                if (itemDetails.length > 1) {
                    itemDetails.filter('[data-index=' + $(event.target).data('index') + ']').remove();
                } else {
                    itemDetails.closest('.item-container').remove();
                }
            }
            event.preventDefault();
        }
    });

    return $.awrma.awRmaRequestItemRemove;
});