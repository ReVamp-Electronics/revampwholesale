define(
    ['ko'],
    function (ko) {
        'use strict';

        return {
            data: window.iwdProductInventoryStockData,

            getBackordersOption: function() {
                return this.data['backordersOption'];
            },

            getStockOption: function() {
                return this.data['stockOption'];
            },

            getIsProductNew: function() {
                return this.data['isProductNew'];
            },

            getDefaultStock: function() {
                return this.data['default_stocks'];
            },

            getStocks: function() {
                return this.data['stocks'];
            },

            isProductComposite: function() {
                return this.data['isProductComposite'];
            },

            isComplexProduct: function() {
                return this.data['isComplexProduct'];
            },

            canUseQtyDecimals: function() {
                return this.data['canUseQtyDecimals'];
            },

            isVirtual: function() {
                return this.data['isVirtual'];
            },

            isReadonly: function() {
                return this.data['isReadonly'];
            }
        };
    }
);
