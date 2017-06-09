define([
        'jquery',
        'IWD_OrderManager/js/order/view/address',
        'jquery/ui'
    ],

function ($, address) {
    'use strict';

    $.widget('mage.iwdOrderManagerAddressBilling', $.mage.iwdOrderManagerAddress, {
        options: {
            urlUpdate: '',
            urlForm: '',

            cancelButtonId: '#address-billing-cancel',
            updateButtonId: '#address-billing-update',
            loadFromButtonId: '#edit-billing-address-link',

            blockId: '.order-addresses .order-billing-address',
            formBlockId: '#billing-address-result-form',
            resultFormId: '.order-addresses .order-billing-address address',

            scrollToTopAfterAction: true
        },

        init: function(options){
            this.options = this._margeOptions(this.options, options);
            this.initAddress(this.options);
        },

        getLoadFromData:function(){
            var href = $(this.options.blockId).find('.admin__page-section-item-title .actions a').attr('href');
            var id = this.getAddressIdFromUrl(href);
            var orderId = this.getCurrentOrderId();

            return {'form_key':FORM_KEY, 'address_id':id, order_id:orderId, 'address_type':'billing'};
        },

        getConfirmUpdateData:function(){
            var data = this.getLoadFromData();
            var formData = this.getFormData('#billing_address_edit_form');
            return this._margeOptions(data, formData);
        },

        afterLoadFromSuccessHandler: function(){
            var actionBlock = $("#address-billing-actions-block");
            var form = $(actionBlock).siblings('form');
            $(actionBlock).appendTo(form);
        }
    });

    return $.mage.iwdOrderManagerAddressBilling;
});