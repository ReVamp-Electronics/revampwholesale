define([
        'jquery',
        'IWD_OrderManager/js/order/view/actions',
        'Magento_Sales/order/create/scripts',
        'jquery/validate',
        'jquery/ui'
    ],

    function ($) {
        'use strict';

        $.widget('mage.iwdOrderManagerPayment', $.mage.iwdOrderManagerActions, {
            options: {
                urlUpdate: '',
                urlForm: '',
                baseUrl: '',

                cancelButtonId: '#om-edit-payment-cancel',
                updateButtonId: '#om-edit-payment-update',
                loadFromButtonId: '#edit-payment-link',

                blockId: '.order-view-billing-shipping .order-payment-method',
                formBlockId: '#payment-result-form',
                resultFormId: '.order-view-billing-shipping .order-payment-method .admin__page-section-item-content',

                scrollToTopAfterAction: true,
                disallowed: [
                    'You can not change the payment method for an order with an invoice(s).'
                ]
            },

            init: function(options){
                this.options = this._margeOptions(this.options, options);
                this._initOptions(options);
                this._initActions();
                this.initOrderAdmin();
            },

            initOrderAdmin:function(){
                if(typeof(window.order) == "undefined"){
                    var config = {};
                    var baseUrl = this.options.baseUrl;
                    var order = new AdminOrder(config);
                    order.setLoadBaseUrl(baseUrl);
                    window.order = order;
                }
                if(typeof(window.payment) == "undefined"){
                    window.payment = {
                        switchMethod: order.switchPaymentMethod.bind(order)
                    };
                }
            },

            afterLoadFromSuccessHandler: function(){
                $(this.options.formBlockId).find('#order-billing_method .admin__page-section-title').remove();
            },

            getLoadFromData:function(){
                var orderId = this.getCurrentOrderId();
                return {'form_key':FORM_KEY, order_id:orderId};
            },

            getConfirmUpdateData:function(){
                var orderId = this.getCurrentOrderId();
                var data = {'form_key':FORM_KEY, order_id:orderId};
                $('#order-billing_method').serializeArray().map(function(x){data[x.name] = x.value;});
                return data;
            }
        });

        return $.mage.iwdOrderManagerPayment;
    });