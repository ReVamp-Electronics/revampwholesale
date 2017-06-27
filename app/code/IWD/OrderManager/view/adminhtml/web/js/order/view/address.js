define([
        'jquery',
        'IWD_OrderManager/js/order/view/actions',
        'jquery/ui'
    ],

function ($) {
    'use strict';

    $.widget('mage.iwdOrderManagerAddress', $.mage.iwdOrderManagerActions, {

        initAddress: function(options) {
            this._initOptions(options);
            this._initActions();
        },

        onClickAction: function(actionClass, action) {
            var self = this;

            $(document).off('click touchstart', actionClass);
            $(document).on('click touchstart', actionClass, (function(e) {
                e.preventDefault();
                eval("self." + action);
            }));
        },

        getAddressIdFromUrl:function(url)
        {
            var VRegExp = new RegExp(/address_id\/([0-9]+)/);
            var VResult = url.match(VRegExp);
            return VResult[1];
        }
    });

    return $.mage.iwdOrderManagerAddress;
});