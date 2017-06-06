/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'jquery/ui'
], function($) {
    'use strict';

    $.widget("awrma.awRmaRequestItemMassAction", {
        actionType: 0,
        actionItemId: 0,
        options: {
        },
        _create: function() {
            this._bind();
            this.changeAction($(this.element).find('.action-type').val());
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.element).on('click', '.select-all-checkbox', $.proxy(this.onSelectAllClick, this));
            $(this.element).on('change', '.action-type', $.proxy(this.onSelectActionChange, this));
            $(this.element).on('click', '.action-apply', $.proxy(this.onApplyClick, this));
        },
        _unbind: function() {
            $(this.element).off('click', '.select-all-checkbox');
            $(this.element).off('change', '.action-type');
            $(this.element).off('click', '.action-apply');
        },
        onSelectAllClick: function (event) {
            this.actionItemId = $(event.target).data('item');
            var actions = $(this.element).find('.actions[data-item=' + this.actionItemId + ']');
            if ($(event.target).prop('checked')) {
                actions.show();
            } else {
                actions.hide();
            }
        },
        onSelectActionChange: function(event) {
            this.changeAction($(event.target).val());
        },
        onApplyClick: function () {
            var itemDetails = $('.item-details[data-item=' + this.actionItemId + ']');
            if (this.actionType == 'remove') {
                if ($(this.element).closest('form').find('.item-details').length > 1) {
                    itemDetails.closest('.item-container').remove();
                }
            } else {
                var value = $(this.element)
                    .find('.action-input-field [data-action=' + this.actionType + ']')
                    .find('input, select, textarea')
                    .filter('[type!=hidden]')
                    .val();
                itemDetails.find('[data-id=' + this.actionType + ']').val(value);
            }
        },
        changeAction: function(actionType) {
            var toHide = $(this.element).find('.action-input-field [data-action]');
            var toShow = toHide.filter('[data-action=' + actionType + ']');
            toHide.hide();
            toShow.show();
            this.resetValue(toShow);
            this.actionType = actionType;
        },
        resetValue: function(inputContainer) {
            inputContainer.find('input, select').val('');
        },
        getValue: function(inputContainer) {
            return inputContainer.find('input, select').val();
        }
    });

    return $.awrma.awRmaRequestItemMassAction;
});