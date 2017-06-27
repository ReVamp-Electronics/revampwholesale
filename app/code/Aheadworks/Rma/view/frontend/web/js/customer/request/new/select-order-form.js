/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'mage/translate',
    'mage/validation'
], function($, $t) {
    'use strict';

    $.widget("awrma.awRmaSelectItemForm", {
        options: {
            currentOrderId: '',
            currentRequestItems: ''
        },
        _create: function() {
            this._bind();
            this._initValidators();
            this._setCurrentValues();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.element).on('click', 'input[name=order_id]', $.proxy(this.onSelectOrderClick, this));
            $(this.element).on('click', 'input.order-item-select', $.proxy(this.onSelectOrderItemClick, this));
        },
        _unbind: function() {
            $(this.element).off('click', 'input[name=order_id]', $.proxy(this.onSelectOrderClick, this));
            $(this.element).off('click', 'input.order-item-select.', $.proxy(this.onSelectOrderItemClick, this));
        },
        _initValidators: function() {
            $.validator.addMethod(
                'aw-rma-order-required',
                $.proxy(this.validateOrderSelection, this),
                $t('Please select order')
            );
            $.validator.addMethod(
                'aw-rma-order-items-available',
                $.proxy(this.validateOrderItemsAvailable, this),
                $t('No items available for selected order')
            );
            $.validator.addMethod(
                'aw-rma-order-item-required',
                $.proxy(this.validateOrderItemSelection, this),
                $t('Please select order item(s)')
            );
            $.validator.addMethod(
                'aw-rma-qty-max',
                $.proxy(this.validateMaxQty, this),
                $t('Entered quantity is too high')
            );
        },
        _setCurrentValues: function() {
            this.setCurrentOrderId();
            this.setCurrentRequestItems();
        },
        setCurrentOrderId: function () {
            if (this.options.currentOrderId) {
                $(this.element).find('input[name=order_id]').filter('[value=' + this.options.currentOrderId +']').click();
            }
        },
        setCurrentRequestItems: function () {
            if (this.options.currentRequestItems) {
                for (var orderItemId in this.options.currentRequestItems) {
                    if (this.options.currentRequestItems[orderItemId]) {
                        var qty = this.options.currentRequestItems[orderItemId].qty,
                            inputQty = $(this.element).find('input[data-order-item-id=' + orderItemId + ']');

                        inputQty.val(qty);
                    }
                    $(this.element).find('input[name=item_selected]').filter('[value=' + orderItemId +']').click();
                }
            }
        },
        validateOrderSelection: function(value) {
            return !$.mage.isEmpty(value);
        },
        validateOrderItemsAvailable: function(orderId, element) {
            var orderItemSelected = $(element).closest('form')
                .find('.order-item-row[data-order-id=' + orderId + '] input[name=item_selected]');
            return orderItemSelected.length > 0;
        },
        validateOrderItemSelection: function(value) {
            return !$.mage.isEmpty(value);
        },
        validateMaxQty: function(value, element) {
            var qtyValue = parseInt(value),
                maxQtyValue = parseInt($(element).data('max-value'));

            return !isNaN(qtyValue) && !isNaN(maxQtyValue) && (qtyValue <= maxQtyValue);
        },
        onSelectOrderClick: function(event) {
            var orderId = $(event.target).val();

            this.updateOrderItemRows(orderId);
            this.updateSubmitBtnVisibility();
        },
        onSelectOrderItemClick: function(event) {
            var orderItemId = $(event.target).val(),
                isChecked = $(event.target).prop('checked');

            this.updateOrderItem(orderItemId, isChecked);
            this.updateSubmitBtnVisibility();
        },
        updateOrderItemRows: function (orderId) {
            var itemRows = $(this.element).find('.order-item-row');
            var selectedItemRows = itemRows.filter('[data-order-id=' + orderId + ']');
            itemRows
                .hide()
                .find('input').attr('disabled', 'disabled');
            selectedItemRows
                .show()
                .find('input[data-order-id=' + orderId + ']')
                .filter('input.order-item-select, input.selected').removeAttr('disabled');
            selectedItemRows.last().addClass('last');

            var orderRows = $(this.element).find('.order-row');
            orderRows
                .removeClass('selected')
                .filter('[data-order-id=' + orderId + ']').addClass('selected');
        },
        updateOrderItem: function (orderItemId, isChecked) {
            var inputQty = $(this.element).find('input[data-order-item-id=' + orderItemId + ']'),
                orderItemRow = $(this.element).find('.order-item-row[data-order-item-id=' + orderItemId + ']');

            if (isChecked) {
                inputQty
                    .addClass('selected')
                    .removeAttr('disabled');
                orderItemRow.addClass('selected');
            } else {
                inputQty
                    .removeClass('selected')
                    .attr('disabled', 'disabled');
                orderItemRow.removeClass('selected');
            }
        },
        updateSubmitBtnVisibility: function() {
            var submitBtn = $(this.element).find('button[data-role=submit-btn]'),
                selectedOrder = $(this.element).find('input[name=order_id]:checked'),
                selectedItems = $(this.element).find('input[name=item_selected]:checked:enabled');

            if (selectedOrder.length && selectedItems.length) {
                submitBtn.removeAttr('disabled');
            } else {
                submitBtn.attr('disabled', 'disabled');
            }
        }
    });

    return $.awrma.awRmaSelectItemForm;
});