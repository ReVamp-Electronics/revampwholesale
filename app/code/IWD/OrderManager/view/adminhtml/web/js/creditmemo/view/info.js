define([
        'jquery',
        'IWD_OrderManager/js/order/view/actions',
        'jquery/ui'
    ],

    function ($, actions) {
        'use strict';

        $.widget('mage.iwdOrderManagerCreditmemoInfo', $.mage.iwdOrderManagerActions, {
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
                var id = this.getCurrentCreditmemoId();
                return {'form_key':FORM_KEY, 'creditmemo_id':id};
            },

            getConfirmUpdateData:function(){
                var id = this.getCurrentCreditmemoId();
                var data = {'form_key':FORM_KEY, 'creditmemo_id':id};
                $('#order_information_form').serializeArray().map(function(x){data[x.name] = x.value;});
                return data;
            },

            getCurrentCreditmemoId:function(){
                var VRegExp = new RegExp(/creditmemo_id\/([0-9]+)/);
                var VResult = window.location.href.match(VRegExp);
                return VResult[1];
            }
        });

        return $.mage.iwdOrderManagerCreditmemoInfo;
    });