define([
        'jquery',
        'IWD_OrderManager/js/order/view/address',
        'jquery/ui'
    ],

    function ($, address) {
        'use strict';

        $.widget('mage.iwdOrderManagerAddressShipping', $.mage.iwdOrderManagerAddress, {
            options: {
                urlUpdate: '',
                urlForm: '',

                cancelButtonId: '#address-shipping-cancel',
                updateButtonId: '#address-shipping-update',
                loadFromButtonId: '#edit-shipping-address-link',

                blockId: '.order-addresses .order-shipping-address',
                formBlockId: '#shipping-address-result-form',
                resultFormId: '.order-addresses .order-shipping-address address',

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

                return {'form_key':FORM_KEY, 'address_id':id, order_id:orderId, 'address_type':'shipping'};
            },

            getConfirmUpdateData:function(){
                var data = this.getLoadFromData();
                var formData = this.getFormData('#shipping_address_edit_form');
                return this._margeOptions(data, formData);
            },

            afterLoadFromSuccessHandler: function(){
                var actionBlock = $("#address-shipping-actions-block");
                var form = $(actionBlock).siblings('form');
                $(actionBlock).appendTo(form);
            }
        });

        return $.mage.iwdOrderManagerAddressShipping;
    });