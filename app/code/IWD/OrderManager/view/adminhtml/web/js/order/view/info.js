define([
        'jquery',
        'IWD_OrderManager/js/order/view/actions',
        'jquery/ui'
    ],

    function ($, actions) {
        'use strict';

        $.widget('mage.iwdOrderManagerOrderInfo', $.mage.iwdOrderManagerActions, {
            options: {
                urlUpdate: '',
                urlForm: '',

                cancelButtonId: '#om-order-information-cancel',
                updateButtonId: '#om-order-information-update',
                loadFromButtonId: '#edit-order-information-link',

                blockId: '.order-information',
                formBlockId: '#order-information-result-form',
                resultFormId: '.order-information .admin__page-section-item-content',

                scrollToTopAfterAction: true
            },

            init: function(options){
                this.options = this._margeOptions(this.options, options);
                this._initOptions(options);
                this._initActions();
            },

            getLoadFromData:function(){
                var orderId = this.getCurrentOrderId();
                return {'form_key':FORM_KEY, order_id:orderId};
            },

            getConfirmUpdateData:function(){
                var orderId = this.getCurrentOrderId();
                var data = {'form_key':FORM_KEY, order_id:orderId};
                $('#order_information_form').serializeArray().map(function(x){data[x.name] = x.value;});
                return data;
            }
        });

        return $.mage.iwdOrderManagerOrderInfo;
    });